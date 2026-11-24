<?php

namespace App\Services;

use App\Models\PuntoVenta;
use Exception;
use Throwable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Afip;

class ArcaFacturacionService
{
    /**
     * Instancia configurada del SDK de AFIP / ARCA según el ambiente del Punto de Venta.
     */
    public function obtenerInstanciaAfip(PuntoVenta $puntoVenta): Afip
    {
        if ($puntoVenta->tipo_emision !== 'arca_wsfe') {
            throw new Exception("El Punto de Venta #{$puntoVenta->numero} no tiene modalidad 'arca_wsfe'. Modalidad actual: '{$puntoVenta->tipo_emision}'.");
        }

        $config = $puntoVenta->configuracion ?? [];
        $esProduccion = ($config['ambiente'] ?? 'homologacion') === 'produccion';

        $cuit = !empty($config['cuit']) 
            ? (int) preg_replace('/[^0-9]/', '', (string)$config['cuit']) 
            : (int) env('ARCA_CUIT', 20000000000);

        $certFileName = $esProduccion 
            ? env('ARCA_CERT_PROD', 'arca_prod.crt') 
            : env('ARCA_CERT_HOMO', 'arca_homo.crt');

        $keyFileName = $esProduccion 
            ? env('ARCA_KEY_PROD', 'arca_prod.key') 
            : env('ARCA_KEY_HOMO', 'arca_homo.key');

        $certPath = storage_path('app/arca/' . $certFileName);
        $keyPath  = storage_path('app/arca/' . $keyFileName);
        $tokensFolder = storage_path('app/arca/tokens/');

        // Garantizar que la carpeta de almacenamiento de tokens exista
        if (!File::isDirectory($tokensFolder)) {
            File::makeDirectory($tokensFolder, 0755, true);
        }

        // Validación de existencia de archivos de certificados
        if (!File::exists($certPath)) {
            throw new Exception("No se encontró el certificado de ARCA en: {$certPath}. Debe colocar su archivo .crt en storage/app/arca/ y definir ARCA_CERT_HOMO/PROD en .env.");
        }

        if (!File::exists($keyPath)) {
            throw new Exception("No se encontró la clave privada de ARCA en: {$keyPath}. Debe colocar su archivo .key en storage/app/arca/ y definir ARCA_KEY_HOMO/PROD en .env.");
        }

        return new Afip([
            'CUIT'       => $cuit,
            'production' => $esProduccion,
            'cert'       => $certPath,
            'key'        => $keyPath,
            'res_folder' => $tokensFolder,
            'ta_folder'  => $tokensFolder,
        ]);
    }

    /**
     * Emite un comprobante digital en ARCA (WSFEv1) y obtiene CAE y Vencimiento.
     *
     * @param PuntoVenta $puntoVenta
     * @param array $datos [
     *      'cbte_tipo' => 1|6|11|'FACTURA_A'|'FACTURA_B'|'FACTURA_C',
     *      'concepto'  => 1|2|3 (1: Productos, 2: Servicios),
     *      'doc_tipo'  => 80|96|99 (80: CUIT, 96: DNI, 99: Consumidor Final),
     *      'doc_nro'   => numeric|string,
     *      'total'     => float (monto total bruto / cobrado),
     *      'imp_neto'  => float (opcional),
     *      'imp_iva'   => float (opcional),
     * ]
     * @return array
     */
    public function emitirComprobante(PuntoVenta $puntoVenta, array $datos): array
    {
        $esProduccion = ($puntoVenta->configuracion['ambiente'] ?? 'homologacion') === 'produccion';

        try {
            $afip = $this->obtenerInstanciaAfip($puntoVenta);

            $cbteTipo = $this->resolverTipoComprobante($datos['cbte_tipo'] ?? 6);
            $concepto = (int) ($datos['concepto'] ?? 1); // 1 = Productos

            // 1. Consultar el último comprobante emitido en ARCA para este Punto de Venta y Tipo
            $ultimoCbte = (int) $afip->ElectronicBilling->GetLastVoucher((int)$puntoVenta->numero, $cbteTipo);
            $nuevoNro = $ultimoCbte + 1;

            // 2. Resolver Documento de Cliente
            $docTipo = (int) ($datos['doc_tipo'] ?? 99);
            $docNro  = $docTipo === 99 ? 0 : (float) preg_replace('/[^0-9]/', '', (string) ($datos['doc_nro'] ?? '0'));

            // 3. Resolver Importes y Alícuotas de IVA
            $impTotal = round((float) ($datos['total'] ?? $datos['imp_total'] ?? 0), 2);
            if ($impTotal <= 0) {
                throw new Exception("El importe total del comprobante debe ser mayor a 0 (recibido: {$impTotal}).");
            }

            // Factura C (Monotributo / Exento) no discrimina IVA
            if (in_array($cbteTipo, [11, 12, 13], true)) {
                $impNeto = $impTotal;
                $impIVA  = 0.00;
                $ivaPayload = null;
            } else {
                // Facturas A y B
                if (isset($datos['imp_neto']) && isset($datos['imp_iva'])) {
                    $impNeto = round((float) $datos['imp_neto'], 2);
                    $impIVA  = round((float) $datos['imp_iva'], 2);
                } else {
                    $impNeto = round($impTotal / 1.21, 2);
                    $impIVA  = round($impTotal - $impNeto, 2);
                }

                $ivaPayload = [
                    [
                        'Id'      => 5, // IVA 21%
                        'BaseImp' => $impNeto,
                        'Importe' => $impIVA,
                    ]
                ];
            }

            // 4. Armar el payload oficial FECAESolicitar
            $payload = [
                'CantReg'    => 1,
                'PtoVta'     => (int) $puntoVenta->numero,
                'CbteTipo'   => $cbteTipo,
                'Concepto'   => $concepto,
                'DocTipo'    => $docTipo,
                'DocNro'     => $docNro,
                'CbteDesde'  => $nuevoNro,
                'CbteHasta'  => $nuevoNro,
                'CbteFch'    => (int) date('Ymd'),
                'ImpTotal'   => $impTotal,
                'ImpTotConc' => 0,
                'ImpNeto'    => $impNeto,
                'ImpOpEx'    => 0,
                'ImpIVA'     => $impIVA,
                'ImpTrib'    => 0,
                'MonId'      => 'PES',
                'MonCotiz'   => 1,
            ];

            if ($ivaPayload !== null) {
                $payload['Iva'] = $ivaPayload;
            }

            // 5. Enviar a ARCA para autorización y obtención de CAE
            $res = $afip->ElectronicBilling->CreateVoucher($payload);

            if (empty($res['CAE'])) {
                throw new Exception("ARCA no devolvió un CAE válido para el comprobante.");
            }

            return [
                'exito'              => true,
                'numero_comprobante' => $nuevoNro,
                'punto_venta'        => (int) $puntoVenta->numero,
                'cae'                => (string) $res['CAE'],
                'cae_vencimiento'    => (string) $res['CAEFchVto'],
                'ambiente'           => $esProduccion ? 'produccion' : 'homologacion',
                'cbte_tipo'          => $cbteTipo,
                'total'              => $impTotal,
                'neto'               => $impNeto,
                'iva'                => $impIVA,
                'fecha'              => date('Y-m-d'),
            ];

        } catch (Throwable $e) {
            Log::error("Error en Facturación Digital ARCA (PV #{$puntoVenta->numero}): " . $e->getMessage(), [
                'pv'    => $puntoVenta->id,
                'datos' => $datos,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'exito'    => false,
                'error'    => $e->getMessage(),
                'ambiente' => $esProduccion ? 'produccion' : 'homologacion',
            ];
        }
    }

    /**
     * Consulta el último número de comprobante autorizado en ARCA para un Punto de Venta.
     */
    public function consultarUltimoComprobante(PuntoVenta $puntoVenta, int|string $cbteTipo = 6): array
    {
        $esProduccion = ($puntoVenta->configuracion['ambiente'] ?? 'homologacion') === 'produccion';

        try {
            $afip = $this->obtenerInstanciaAfip($puntoVenta);
            $tipoNum = $this->resolverTipoComprobante($cbteTipo);

            $ultimo = (int) $afip->ElectronicBilling->GetLastVoucher((int)$puntoVenta->numero, $tipoNum);

            return [
                'exito'              => true,
                'punto_venta'        => (int) $puntoVenta->numero,
                'cbte_tipo'          => $tipoNum,
                'ultimo_comprobante' => $ultimo,
                'siguiente_numero'   => $ultimo + 1,
                'ambiente'           => $esProduccion ? 'produccion' : 'homologacion',
            ];
        } catch (Throwable $e) {
            return [
                'exito'    => false,
                'error'    => $e->getMessage(),
                'ambiente' => $esProduccion ? 'produccion' : 'homologacion',
            ];
        }
    }

    /**
     * Consulta el estado de los servidores de ARCA (AppServer, DbServer, AuthServer).
     */
    public function consultarEstadoServidores(PuntoVenta $puntoVenta): array
    {
        $esProduccion = ($puntoVenta->configuracion['ambiente'] ?? 'homologacion') === 'produccion';

        try {
            $afip = $this->obtenerInstanciaAfip($puntoVenta);
            $serverStatus = $afip->ElectronicBilling->GetServerStatus();

            return [
                'exito'       => true,
                'app_server'  => $serverStatus->AppServer ?? 'OK',
                'db_server'   => $serverStatus->DbServer ?? 'OK',
                'auth_server' => $serverStatus->AuthServer ?? 'OK',
                'ambiente'    => $esProduccion ? 'produccion' : 'homologacion',
            ];
        } catch (Throwable $e) {
            return [
                'exito'    => false,
                'error'    => $e->getMessage(),
                'ambiente' => $esProduccion ? 'produccion' : 'homologacion',
            ];
        }
    }

    /**
     * Emite un comprobante de prueba en modo Homologación a Consumidor Final ($121 total: $100 Neto + $21 IVA).
     */
    public function emitirComprobantePrueba(PuntoVenta $puntoVenta, int|string $cbteTipo = 6, float $total = 121.00): array
    {
        return $this->emitirComprobante($puntoVenta, [
            'cbte_tipo' => $cbteTipo,
            'concepto'  => 1, // Productos
            'doc_tipo'  => 99, // Consumidor Final
            'doc_nro'   => 0,
            'total'     => $total,
        ]);
    }

    /**
     * Mapea identificadores de comprobantes (código numérico o string) a códigos oficiales de AFIP / ARCA.
     */
    public function resolverTipoComprobante(int|string $tipo): int
    {
        if (is_numeric($tipo)) {
            return (int) $tipo;
        }

        $upper = strtoupper(trim((string) $tipo));

        return match ($upper) {
            'FACTURA_A', 'FACTURA A', 'A' => 1,
            'NOTA_DEBITO_A', 'DEBITO_A'   => 2,
            'NOTA_CREDITO_A', 'CREDITO_A' => 3,
            'FACTURA_B', 'FACTURA B', 'B' => 6,
            'NOTA_DEBITO_B', 'DEBITO_B'   => 7,
            'NOTA_CREDITO_B', 'CREDITO_B' => 8,
            'FACTURA_C', 'FACTURA C', 'C' => 11,
            'NOTA_DEBITO_C', 'DEBITO_C'   => 12,
            'NOTA_CREDITO_C', 'CREDITO_C' => 13,
            default                       => 6,
        };
    }
}
