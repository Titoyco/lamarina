<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Sucursal;
use App\Models\Cliente;
use COM;
use Exception;

class ImportVentasUshuaiaCommand extends Command
{
    protected $signature = 'app:import-ventas-ushuaia 
                            {--path= : Ruta al archivo db.mdb (por defecto base_path/db.mdb)}
                            {--chunk=2500 : Tamaño del lote de inserción}
                            {--clean : Eliminar ventas y detalles previos de Ushuaia antes de importar}';

    protected $description = 'Importa todas las ventas y sus detalles de la sucursal Ushuaia desde creditos/db.mdb hacia MySQL (vinculadas a clientes, productos y créditos)';

    protected ?COM $conn = null;

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $startTime = microtime(true);
        $dbPath = $this->option('path') ?: base_path('db.mdb');
        $chunkSize = 1000;

        if (!file_exists($dbPath)) {
            $this->error("No se encontró el archivo Access en: {$dbPath}");
            return 1;
        }

        $this->info("==================================================================");
        $this->info("  IMPORTACIÓN DE VENTAS Y DETALLES - SUCURSAL USHUAIA             ");
        $this->info("  Origen : {$dbPath}");
        $this->info("  Destino: MySQL (lamarina_v4 - ventas & venta_items)             ");
        $this->info("==================================================================");

        try {
            $this->connectMdb($dbPath);
        } catch (Exception $e) {
            $this->error("Error al conectar con ADODB: " . $e->getMessage());
            return 1;
        }

        // Desactivar query log para evitar consumo innecesario de RAM
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        try {
            // 1. Identificar sucursal Ushuaia
            $sucUshuaia = Sucursal::where('codigo_sucursal', 'US')->orWhere('nombre', 'like', '%Ushuaia%')->first();
            $idSucursal = $sucUshuaia?->id ?? 1;
            $this->info("Sucursal destino: ID {$idSucursal} ({$sucUshuaia?->nombre})");

            if ($this->option('clean')) {
                $this->warn("Limpiando venta_items y ventas previas de sucursal Ushuaia (ID: {$idSucursal})...");
                DB::table('venta_items')
                    ->whereIn('id_venta', function($q) use ($idSucursal) {
                        $q->select('id')->from('ventas')->where('id_sucursal', $idSucursal);
                    })->delete();
                DB::table('ventas')->where('id_sucursal', $idSucursal)->delete();
                $this->info("✓ Registros de ventas e items previos de Ushuaia eliminados correctamente.");
            }

            // 2. Pre-cargar mapas en memoria
            $this->info("\n--- 1/4 PREPARANDO MAPAS DE VINCULACIÓN ---");
            
            // Mapas de Clientes
            $personaToClienteMap = [];
            $dniToClienteMap = [];
            $clientesDb = DB::table('clientes')->get(['id', 'nombre', 'dni', 'cuit', 'obs']);
            foreach ($clientesDb as $c) {
                $doc = $c->dni ?: $c->cuit;
                if ($c->dni) {
                    $dniToClienteMap[(string)$c->dni] = [
                        'id' => $c->id,
                        'nombre' => $c->nombre,
                        'documento' => $doc,
                    ];
                }
                if ($c->obs && preg_match('/MDB IDPERSONA:\s*(\d+)/', $c->obs, $m)) {
                    $personaToClienteMap[(int)$m[1]] = [
                        'id' => $c->id,
                        'nombre' => $c->nombre,
                        'documento' => $doc,
                    ];
                }
            }
            $this->info("✓ Clientes vinculados en memoria: " . count($personaToClienteMap));

            // Cargar PERSONAS de MDB para resolver clientes faltantes
            $this->info("Cargando catálogo PERSONAS de Access MDB...");
            $personasMdb = [];
            $rsP = $this->conn->Execute("SELECT IDPERSONA, RSOCIAL, Cuit, Calle, Telefonos, Email, Saldo, Limite FROM [PERSONAS]");
            while (!$rsP->EOF) {
                $idP = (int)$rsP->Fields("IDPERSONA")->Value;
                $personasMdb[$idP] = [
                    'nombre'    => $this->cleanUtf8($rsP->Fields("RSOCIAL")->Value),
                    'cuit'      => $this->cleanUtf8($rsP->Fields("Cuit")->Value),
                    'calle'     => $this->cleanUtf8($rsP->Fields("Calle")->Value),
                    'tel'       => $this->cleanUtf8($rsP->Fields("Telefonos")->Value),
                    'email'     => $this->cleanUtf8($rsP->Fields("Email")->Value),
                    'saldo'     => (float)($rsP->Fields("Saldo")->Value ?? 0),
                    'limite'    => (float)($rsP->Fields("Limite")->Value ?? 0),
                ];
                $rsP->MoveNext();
            }
            $rsP->Close();
            $this->info("✓ Personas cargadas desde MDB: " . count($personasMdb));

            // Mapas de Productos
            $productosMap = DB::table('productos')->pluck('id', 'codigo')->toArray();
            $this->info("✓ Productos en catálogo MySQL: " . count($productosMap));

            // Mapas de Créditos Ushuaia
            $creditosUshuaia = DB::table('creditos')
                ->where('id_sucursal', $idSucursal)
                ->get(['id', 'credito', 'cantidad_cuotas', 'valor_cuota_ref'])
                ->keyBy('credito');
            $this->info("✓ Créditos personales Ushuaia en MySQL: " . count($creditosUshuaia));

            // Mapa de Ventas ya importadas (id_operacion => id)
            $existingVentas = DB::table('ventas')
                ->whereNotNull('id_operacion')
                ->pluck('id', 'id_operacion')
                ->toArray();
            $this->info("✓ Ventas previamente importadas: " . count($existingVentas));

            // 3. Importación de VENTAS (MOVIMIENTO)
            $this->info("\n--- 2/4 IMPORTANDO CABECERAS DE VENTAS (MOVIMIENTO) ---");
            $rsMov = $this->conn->Execute("
                SELECT id_operacion, Comprobante, sucursal, ncomprobante, fecha, hora, 
                       idcliente, bruto, descuento, impuesto, totalneto, formadepago, estado, IDUSUARIO 
                FROM [MOVIMIENTO] 
                WHERE Operacion = 'VENTA' 
                ORDER BY id_operacion ASC
            ");

            $batchVentas = [];
            $nuevasVentasCount = 0;
            $maxNumeroFactura = 0;

            while (!$rsMov->EOF) {
                $idOp = (int)$rsMov->Fields("id_operacion")->Value;

                // Si ya existe, solo registrar en mapa si no estaba
                if (isset($existingVentas[$idOp])) {
                    $rsMov->MoveNext();
                    continue;
                }

                // Punto de venta y número comprobante
                $rawPv = (string)$rsMov->Fields("sucursal")->Value;
                $pv = (int)preg_replace('/[^0-9]/', '', $rawPv) ?: 1;
                $rawNcomp = (string)$rsMov->Fields("ncomprobante")->Value;
                $ncomp = (int)preg_replace('/[^0-9]/', '', $rawNcomp) ?: 1;
                $comprobante = sprintf('%04d-%08d', $pv, $ncomp);

                if ($ncomp > $maxNumeroFactura) {
                    $maxNumeroFactura = $ncomp;
                }

                // Fecha y hora
                $rawFecha = (string)$rsMov->Fields("fecha")->Value;
                $rawHora = (string)$rsMov->Fields("hora")->Value;
                $fechaHora = $this->parseDateTime($rawFecha, $rawHora);

                // Cliente
                $idCli = (int)$rsMov->Fields("idcliente")->Value;
                $idCliente = null;
                $clienteNombre = 'CONSUMIDOR FINAL';
                $clienteDoc = null;

                if ($idCli > 1) {
                    if (isset($personaToClienteMap[$idCli])) {
                        $idCliente = $personaToClienteMap[$idCli]['id'];
                        $clienteNombre = $personaToClienteMap[$idCli]['nombre'];
                        $clienteDoc = $personaToClienteMap[$idCli]['documento'];
                    } elseif (isset($personasMdb[$idCli])) {
                        $pInfo = $personasMdb[$idCli];
                        $dniDigits = preg_replace('/[^0-9]/', '', $pInfo['cuit'] ?? '');
                        if (strlen($dniDigits) === 11 && in_array(substr($dniDigits, 0, 2), ['20', '23', '24', '27', '30', '33', '34'])) {
                            $dniCandidate = substr($dniDigits, 2, 8);
                        } else {
                            $dniCandidate = $dniDigits ?: (string)$idCli;
                        }

                        $dniInt = is_numeric($dniCandidate) ? (int)$dniCandidate : 0;
                        $dniKey = $dniInt > 0 ? (string)$dniInt : $dniCandidate;

                        if (isset($dniToClienteMap[$dniKey])) {
                            $idCliente = $dniToClienteMap[$dniKey]['id'];
                            $clienteNombre = $dniToClienteMap[$dniKey]['nombre'];
                            $clienteDoc = $dniToClienteMap[$dniKey]['documento'];
                            $personaToClienteMap[$idCli] = $dniToClienteMap[$dniKey];
                        } else {
                            // Buscar en base de datos si existe por DNI
                            $cliDb = ($dniInt > 0) ? DB::table('clientes')->where('dni', $dniInt)->first(['id', 'nombre', 'dni', 'cuit']) : null;
                            if ($cliDb) {
                                $clientData = [
                                    'id'        => $cliDb->id,
                                    'nombre'    => $cliDb->nombre,
                                    'documento' => $cliDb->dni ?: $cliDb->cuit,
                                ];
                                $personaToClienteMap[$idCli] = $clientData;
                                $dniToClienteMap[$dniKey] = $clientData;

                                $idCliente = $cliDb->id;
                                $clienteNombre = $cliDb->nombre;
                                $clienteDoc = $clientData['documento'];
                            } else {
                                // Crear cliente en MySQL para mantener integridad completa
                                $nomLimpio = $pInfo['nombre'] !== '' ? $pInfo['nombre'] : "CLIENTE USHUAIA #{$idCli}";
                                try {
                                    $nuevoCliId = DB::table('clientes')->insertGetId([
                                        'id_sucursal'  => $idSucursal,
                                        'id_localidad' => 310, // Ushuaia
                                        'nombre'       => $nomLimpio,
                                        'dni'          => $dniInt,
                                        'cuit'         => $pInfo['cuit'] ?: null,
                                        'direccion'    => $pInfo['calle'] ?: 'Sin dirección',
                                        'tel1'         => $pInfo['tel'] ?: '0',
                                        'email'        => $pInfo['email'] ?: null,
                                        'saldo'        => $pInfo['saldo'],
                                        'limite'       => $pInfo['limite'] > 0 ? $pInfo['limite'] : 500000.00,
                                        'suspendido'   => 0,
                                        'obs'          => "MDB IDPERSONA: {$idCli}",
                                        'created_at'   => $fechaHora,
                                        'updated_at'   => $fechaHora,
                                    ]);

                                    $clientData = [
                                        'id'        => $nuevoCliId,
                                        'nombre'    => $nomLimpio,
                                        'documento' => $dniCandidate ?: $pInfo['cuit'],
                                    ];
                                    $personaToClienteMap[$idCli] = $clientData;
                                    $dniToClienteMap[$dniKey] = $clientData;

                                    $idCliente = $nuevoCliId;
                                    $clienteNombre = $nomLimpio;
                                    $clienteDoc = $clientData['documento'];
                                } catch (\Throwable $e) {
                                    // Fallback si por alguna razón falla el insert
                                    $idCliente = null;
                                    $clienteNombre = $nomLimpio;
                                    $clienteDoc = $pInfo['cuit'] ?: null;
                                }
                            }
                        }
                    }
                }

                // Crédito Personal
                $idCredito = null;
                $cuotasCredito = null;
                $valorCuotaCredito = null;
                $medioPago = 'efectivo';

                if (isset($creditosUshuaia[(string)$idOp])) {
                    $cr = $creditosUshuaia[(string)$idOp];
                    $idCredito = $cr->id;
                    $cuotasCredito = $cr->cantidad_cuotas;
                    $valorCuotaCredito = $cr->valor_cuota_ref;
                    $medioPago = 'credito_personal';
                } else {
                    $rawPago = strtoupper(trim((string)$rsMov->Fields("formadepago")->Value));
                    if (str_contains($rawPago, 'TARJETA')) {
                        $medioPago = 'tarjeta_credito';
                    } elseif (str_contains($rawPago, 'MERCADO') || str_contains($rawPago, 'QR') || str_contains($rawPago, 'GO CUOTAS')) {
                        $medioPago = 'mercado_pago';
                    } elseif (str_contains($rawPago, 'TRANSFER')) {
                        $medioPago = 'transferencia';
                    } elseif (str_contains($rawPago, 'CTA')) {
                        $medioPago = 'cuenta_corriente';
                    } elseif (str_contains($rawPago, 'CREDITO') || str_contains($rawPago, 'CREDIMUNDO')) {
                        $medioPago = 'credito_personal';
                    } else {
                        $medioPago = 'efectivo';
                    }
                }

                // Importes
                $bruto = (float)($rsMov->Fields("bruto")->Value ?? 0);
                $desc = (float)($rsMov->Fields("descuento")->Value ?? 0);
                $total = (float)($rsMov->Fields("totalneto")->Value ?? 0);
                $recargo = max(0.0, round($total - ($bruto - $desc), 2));

                // Tipo Comprobante
                $rawComp = strtoupper((string)$rsMov->Fields("Comprobante")->Value);
                if (str_contains($rawComp, 'FACTURA A')) $tipoComp = 'FACTURA_A';
                elseif (str_contains($rawComp, 'FACTURA E')) $tipoComp = 'FACTURA_E';
                elseif (str_contains($rawComp, 'CREDITO')) $tipoComp = 'NOTA_CREDITO';
                elseif (str_contains($rawComp, 'RECIBO')) $tipoComp = 'RECIBO';
                elseif (str_contains($rawComp, 'REMITO')) $tipoComp = 'REMITO';
                elseif (str_contains($rawComp, 'PRESUPUESTO')) $tipoComp = 'PRESUPUESTO';
                elseif (str_contains($rawComp, 'TICKET')) $tipoComp = 'TICKET_B';
                else $tipoComp = 'FACTURA_B';

                // Estado
                $rawEstado = strtoupper((string)$rsMov->Fields("estado")->Value);
                $estado = (str_contains($rawEstado, 'ANULAD') || str_contains($rawEstado, 'CANCEL')) ? 'anulada' : 'completada';

                $batchVentas[] = [
                    'id_operacion'        => $idOp,
                    'id_sucursal'         => $idSucursal,
                    'id_usuario'          => 1,
                    'id_cliente'          => $idCliente,
                    'cliente_nombre'      => mb_substr($clienteNombre, 0, 190),
                    'cliente_documento'   => $clienteDoc ? mb_substr((string)$clienteDoc, 0, 50) : null,
                    'punto_venta'         => $pv,
                    'numero_factura'      => $ncomp,
                    'comprobante'         => $comprobante,
                    'tipo_comprobante'    => $tipoComp,
                    'fecha'               => $fechaHora,
                    'medio_pago'          => $medioPago,
                    'subtotal'            => $bruto,
                    'recargo'             => $recargo,
                    'descuento'           => $desc,
                    'total'               => $total,
                    'id_credito'          => $idCredito,
                    'cuotas_credito'      => $cuotasCredito,
                    'valor_cuota_credito' => $valorCuotaCredito,
                    'estado'              => $estado,
                    'observaciones'       => "MDB ID_OPERACION: {$idOp}",
                    'created_at'          => $fechaHora,
                    'updated_at'          => $fechaHora,
                ];

                $nuevasVentasCount++;

                if (count($batchVentas) >= $chunkSize) {
                    DB::table('ventas')->insert($batchVentas);
                    $batchVentas = [];
                    $this->output->write(".");
                }

                $rsMov->MoveNext();
            }
            $rsMov->Close();

            if (!empty($batchVentas)) {
                DB::table('ventas')->insert($batchVentas);
                $batchVentas = [];
            }

            $this->info("\n✓ Cabeceras de ventas insertadas: {$nuevasVentasCount}");

            // Liberar memoria de estructuras no requeridas en el paso de items
            unset($personasMdb, $personaToClienteMap, $dniToClienteMap, $creditosUshuaia, $batchVentas);
            gc_collect_cycles();

            // 4. Recargar mapa de ventas para inserción de items (id_operacion => id)
            $this->info("\n--- 3/4 ASOCIANDO VENTAS E INSERTANDO DETALLES (MOVIMIENTO_DETALLE) ---");
            $ventasMap = DB::table('ventas')
                ->whereNotNull('id_operacion')
                ->pluck('id', 'id_operacion')
                ->toArray();
            $this->info("✓ Total ventas registradas para vincular detalles: " . count($ventasMap));

            // Consultar cuántos items ya existen para evitar duplicar si se reanuda
            $itemsExistentes = DB::table('venta_items')->count();
            $this->info("Items actualmente en venta_items: {$itemsExistentes}");

            // Mapear qué ventas ya tienen renglones para garantizar idempotencia estricta
            $ventasConItems = [];
            if ($itemsExistentes > 0) {
                $ventasConItems = DB::table('venta_items')
                    ->distinct()
                    ->pluck('id_venta')
                    ->flip()
                    ->toArray();
                $this->info("Ventas con items ya cargados: " . count($ventasConItems));
            }

            // Procesamos MOVIMIENTO_DETALLE
            $rsItems = $this->conn->Execute("
                SELECT md.id_operacion, md.idproducto, md.descripcion, md.cantidad, md.precio_unitario, md.descuento, md.totalproducto 
                FROM [MOVIMIENTO_DETALLE] md 
                INNER JOIN [MOVIMIENTO] m ON md.id_operacion = m.id_operacion 
                WHERE m.Operacion = 'VENTA' 
                ORDER BY md.id_operacion ASC
            ");

            $batchItems = [];
            $itemsInsertadosCount = 0;
            $itemsOmitidosCount = 0;
            $itemsYaExistentesCount = 0;
            $now = now()->toDateTimeString();

            while (!$rsItems->EOF) {
                $idOp = (int)$rsItems->Fields("id_operacion")->Value;
                $ventaId = $ventasMap[$idOp] ?? null;

                if (!$ventaId) {
                    $itemsOmitidosCount++;
                    $rsItems->MoveNext();
                    continue;
                }

                // Si esta venta ya tiene items cargados, omitir para no duplicar
                if (isset($ventasConItems[$ventaId])) {
                    $itemsYaExistentesCount++;
                    $rsItems->MoveNext();
                    continue;
                }

                $code = trim((string)$rsItems->Fields("idproducto")->Value);
                $idProd = $productosMap[$code] ?? null;
                $rawDesc = $this->cleanUtf8($rsItems->Fields("descripcion")->Value);
                $desc = $rawDesc !== '' ? $rawDesc : ($idProd ? 'Artículo de venta' : "Producto #{$code}");

                $cant = max(0.01, (float)($rsItems->Fields("cantidad")->Value ?? 1));
                $precio = (float)($rsItems->Fields("precio_unitario")->Value ?? 0);
                $descMonto = (float)($rsItems->Fields("descuento")->Value ?? 0);
                $subtotal = (float)($rsItems->Fields("totalproducto")->Value ?? 0);
                $bruto = round($cant * $precio, 2);

                // Calcular porcentaje de descuento si existe monto de descuento
                $descPorcentaje = 0.0;
                if ($descMonto > 0 && $bruto > 0) {
                    $descPorcentaje = min(100.0, round(($descMonto / $bruto) * 100, 2));
                }

                // Si totalproducto viene en 0 pero NO hubo descuento y precio > 0, calcular
                if ($subtotal <= 0.0 && $descMonto == 0.0 && $precio > 0) {
                    $subtotal = $bruto;
                } elseif ($descMonto > 0) {
                    // Mantener el subtotal neto exacto (bruto - descMonto, ej: promo 2x1 -> subtotal = 0.00)
                    $subtotal = max(0.0, round($bruto - $descMonto, 2));
                }

                $batchItems[] = [
                    'id_venta'             => $ventaId,
                    'id_producto'          => $idProd,
                    'codigo_producto'      => $code !== '' ? mb_substr($code, 0, 50) : null,
                    'descripcion'          => mb_substr($desc, 0, 255),
                    'cantidad'             => $cant,
                    'precio_unitario'      => $precio,
                    'descuento_porcentaje' => $descPorcentaje,
                    'descuento'            => $descMonto,
                    'subtotal'             => $subtotal,
                    'descuenta_stock'      => 0,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];

                $itemsInsertadosCount++;

                if (count($batchItems) >= 1500) {
                    DB::table('venta_items')->insert($batchItems);
                    $batchItems = [];
                    $this->output->write(":");
                }

                $rsItems->MoveNext();
            }
            $rsItems->Close();

            if (!empty($batchItems)) {
                DB::table('venta_items')->insert($batchItems);
                $batchItems = [];
            }

            $this->info("\n✓ Detalles de venta procesados: {$itemsInsertadosCount} (Omitidos sin cabecera: {$itemsOmitidosCount})");

            // 5. Actualizar sucursal ultimo_comprobante
            if ($maxNumeroFactura > 0) {
                DB::table('sucursales')
                    ->where('id', $idSucursal)
                    ->where('ultimo_comprobante', '<', $maxNumeroFactura)
                    ->update(['ultimo_comprobante' => $maxNumeroFactura]);
                $this->info("✓ Último número de comprobante actualizado en sucursal Ushuaia: #{$maxNumeroFactura}");
            }

            $this->info("\n--- 4/4 RESUMEN CONSOLIDADO ---");
            $totalVentasFinal = DB::table('ventas')->where('id_sucursal', $idSucursal)->count();
            $totalItemsFinal = DB::table('venta_items')->count();
            $ventasCredito = DB::table('ventas')->where('id_sucursal', $idSucursal)->whereNotNull('id_credito')->count();
            $ventasConCliente = DB::table('ventas')->where('id_sucursal', $idSucursal)->whereNotNull('id_cliente')->count();

            $this->info("- Total Ventas en Ushuaia : {$totalVentasFinal}");
            $this->info("- Total Renglones/Detalles: {$totalItemsFinal}");
            $this->info("- Ventas vinculadas a Créditos: {$ventasCredito}");
            $this->info("- Ventas con Cliente Ficha   : {$ventasConCliente}");

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            $this->disconnectMdb();
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->info("\n==================================================================");
        $this->info("✓ IMPORTACIÓN COMPLETADA EXITOSAMENTE EN {$elapsed} SEGUNDOS.");
        $this->info("==================================================================");

        return 0;
    }

    protected function connectMdb(string $dbPath): void
    {
        $connStr = "Provider=Microsoft.ACE.OLEDB.12.0;Data Source={$dbPath};Persist Security Info=False;";
        $this->conn = new COM("ADODB.Connection");
        $this->conn->Open($connStr);
    }

    protected function disconnectMdb(): void
    {
        if ($this->conn) {
            try {
                $this->conn->Close();
            } catch (\Throwable $e) {}
            $this->conn = null;
        }
    }

    protected function parseDateTime(string $rawFecha, string $rawHora): string
    {
        $hora = '12:00:00';
        if (preg_match('/(\d{1,2}):(\d{1,2}):(\d{1,2})/', $rawHora, $hm)) {
            $hora = sprintf('%02d:%02d:%02d', (int)$hm[1], (int)$hm[2], (int)$hm[3]);
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $rawFecha, $m)) {
            return sprintf('%04d-%02d-%02d %s', (int)$m[3], (int)$m[2], (int)$m[1], $hora);
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $rawFecha, $m)) {
            return sprintf('%04d-%02d-%02d %s', (int)$m[1], (int)$m[2], (int)$m[3], $hora);
        }

        try {
            $d = new \DateTime($rawFecha);
            return $d->format('Y-m-d') . ' ' . $hora;
        } catch (\Throwable $e) {
            return '2026-10-21 ' . $hora;
        }
    }

    protected function cleanUtf8(mixed $val): string
    {
        if ($val === null) return '';
        $val = (string)$val;
        if (!mb_check_encoding($val, 'UTF-8')) {
            $val = mb_convert_encoding($val, 'UTF-8', 'Windows-1252');
        }
        $val = str_replace(["\xB4", "´", "`"], "'", $val);
        $val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $val);
        return trim(preg_replace('/\s+/u', ' ', $val));
    }
}
