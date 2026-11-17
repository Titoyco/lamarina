<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Exception;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'codigo_barra',
        'codigo_proveedor',
        'talle',
        'color',
        'descripcion',
        'categoria_id',
        'subcategoria_id',
        'proveedor_id',
        'precio_costo',
        'precio_venta',
        'stock_minimo',
        'unidad_medida',
        'imagen',
        'observaciones',
        'activo',
        'controla_stock',
        'total_ventas_unidades',
        'total_ventas_operaciones',
        'fecha_alta',
        'fecha_ultima_venta',
        'fecha_ultima_compra',
        'fecha_modificacion',
    ];

    /**
     * Lista normalizada de talles estándar sugeridos (1 a 70 y letras XS a 8XL).
     */
    public static function listaTallesSugeridos(): array
    {
        $talles = [];
        // Numéricos 1 al 70
        for ($i = 1; $i <= 70; $i++) {
            $talles[] = (string)$i;
        }
        // Bebé / Primera infancia
        $bebe = ['0', '00', '1M', '3M', '6M', '9M', '12M', '18M', '24M', '36M'];
        // Letras
        $letras = ['XS', 'S', 'M', 'L', 'XL', '2XL', 'XXL', '3XL', 'XXXL', '4XL', '5XL', '6XL', '7XL', '8XL', 'UNICO'];

        return array_merge($talles, $bebe, $letras);
    }

    /**
     * Convierte cualquier talle (1-70 o XS-8XL) en una representación estricta de 2 caracteres alfanuméricos.
     */
    public static function normalizarTalle2Char(?string $talle): string
    {
        if ($talle === null || trim($talle) === '') {
            return '00';
        }
        $t = strtoupper(trim($talle));

        // Talles numéricos 1-9 -> '01'..'09', 10-99 -> '10'..'99'
        if (is_numeric($t)) {
            $num = (int)$t;
            return str_pad((string)$num, 2, '0', STR_PAD_LEFT);
        }

        // Mapeo estándar de letras a 2 caracteres
        $map = [
            'XS' => 'XS',
            'S'  => '0S',
            'M'  => '0M',
            'L'  => '0L',
            'XL' => 'XL',
            'XXL' => '2X',
            '2XL' => '2X',
            'XXXL' => '3X',
            '3XL' => '3X',
            '4XL' => '4X',
            'XXXXL' => '4X',
            '5XL' => '5X',
            '6XL' => '6X',
            '7XL' => '7X',
            '8XL' => '8X',
            'U'   => '0U',
            'UNICO' => 'UN',
            'ESP' => 'ES',
        ];

        if (isset($map[$t])) {
            return $map[$t];
        }

        // Si tiene longitud 2, devolverla
        if (strlen($t) === 2) {
            return $t;
        }

        // Si tiene 1 caracter, rellenar con 0 a la izquierda
        if (strlen($t) === 1) {
            return '0' . $t;
        }

        // Si es más largo, tomar los 2 primeros caracteres alfanuméricos
        $clean = preg_replace('/[^A-Z0-9]/', '', $t);
        return str_pad(substr($clean, 0, 2), 2, '0', STR_PAD_RIGHT);
    }

    /**
     * Convierte una descripción de color a un código estricto de 2 caracteres alfanuméricos.
     */
    public static function normalizarColor2Char(?string $color): string
    {
        if ($color === null || trim($color) === '') {
            return '00';
        }
        $c = strtoupper(trim($color));

        $coloresMap = [
            'NEGRO' => 'NE',
            'BLANCO' => 'BL',
            'AZUL' => 'AZ',
            'ROJO' => 'RO',
            'VERDE' => 'VE',
            'AMARILLO' => 'AM',
            'GRIS' => 'GR',
            'ROSA' => 'RS',
            'ROSADO' => 'RS',
            'CELESTE' => 'CE',
            'BEIGE' => 'BE',
            'MARRON' => 'MA',
            'CAFE' => 'CF',
            'BORDO' => 'BO',
            'VIOLETA' => 'VI',
            'LILA' => 'LI',
            'NARANJA' => 'NA',
            'FUCSIA' => 'FU',
            'TURQUESA' => 'TU',
            'ESTAMPADO' => 'ES',
            'SURTIDO' => 'SU',
            'VARIOS' => 'VA',
            'MULTICOLOR' => 'MC',
            'NATURAL' => 'NT',
            'CRUDO' => 'CR',
            'CORAL' => 'CO',
            'MOSTAZA' => 'MO',
            'PLATA' => 'PL',
            'ORO' => 'OR',
            'DORADO' => 'DO',
            'TERRACOTA' => 'TC',
        ];

        if (isset($coloresMap[$c])) {
            return $coloresMap[$c];
        }

        $clean = preg_replace('/[^A-Z0-9]/', '', $c);
        if (strlen($clean) >= 2) {
            return substr($clean, 0, 2);
        }
        if (strlen($clean) === 1) {
            return '0' . $clean;
        }
        return '00';
    }

    /**
     * Genera el código estructurado (que funciona como código interno y código de barras CODE128):
     * {código empresa proveedora (3)}{código talle (2)}{código color (2)}{código artículo según proveedor}
     */
    public static function armarCodigoEstructurado(?Proveedor $proveedor, ?string $talle, ?string $color, ?string $codigoArticuloProveedor): string
    {
        $codProv = '000';
        if ($proveedor && $proveedor->legajo) {
            $codProv = str_pad($proveedor->legajo, 3, '0', STR_PAD_LEFT);
        }

        $talle2 = static::normalizarTalle2Char($talle);
        $color2 = static::normalizarColor2Char($color);

        $cleanArt = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', (string)$codigoArticuloProveedor)));
        if (empty($cleanArt)) {
            $cleanArt = '00001';
        }

        return $codProv . $talle2 . $color2 . $cleanArt;
    }

    /**
     * Genera el código de barras sugerido según la estructura:
     * {cod.prov(3)}{talle(2)}{color(2)}{codigo(5...)} en CODE128
     */
    public static function generarCodigoBarraSugerido(?string $talle = null, ?string $color = null, ?Proveedor $proveedor = null, ?string $codigoBase = null): string
    {
        // 1. Código Proveedor / Legajo (3 dígitos)
        $codProv = '000';
        if ($proveedor && $proveedor->legajo) {
            $codProv = str_pad($proveedor->legajo, 3, '0', STR_PAD_LEFT);
        }

        // 2. Talle (2 caracteres)
        $talle2 = static::normalizarTalle2Char($talle);

        // 3. Color (2 caracteres)
        $color2 = static::normalizarColor2Char($color);

        // 4. Código base / correlativo (mínimo 5 caracteres)
        $base = '00001';
        if ($codigoBase) {
            $cleanBase = preg_replace('/[^A-Z0-9]/', '', strtoupper($codigoBase));
            if (!empty($cleanBase)) {
                $base = is_numeric($cleanBase) ? str_pad($cleanBase, 5, '0', STR_PAD_LEFT) : $cleanBase;
            }
        } else {
            $ultimoId = (int)(static::max('id') ?? 0) + 1;
            $base = str_pad((string)$ultimoId, 5, '0', STR_PAD_LEFT);
        }

        return $codProv . $talle2 . $color2 . $base;
    }

    /**
     * Desglosa un código de barra estructurado o histórico en sus 4 componentes:
     * - Proveedor (3 caracteres)
     * - Talle (2 caracteres)
     * - Color (2 caracteres)
     * - Código de fábrica / artículo (5 o más caracteres)
     */
    public static function desglosarCodigoBarra(?string $codigo): array
    {
        $cod = preg_replace('/[^A-Za-z0-9]/', '', strtoupper(trim((string)$codigo)));
        if (strlen($cod) === 11) {
            $cod = '0' . $cod;
        }

        if (strlen($cod) >= 12) {
            $provLegajo = substr($cod, 0, 3);
            $segTalle = substr($cod, 3, 2);
            $segColor = substr($cod, 5, 2);
            $segFabrica = substr($cod, 7);

            return [
                'proveedor_legajo' => $provLegajo,
                'talle_sugerido' => $segTalle,
                'color_sugerido' => $segColor,
                'codigo_proveedor' => $segFabrica,
            ];
        }

        if (strlen($cod) >= 8) {
            return [
                'proveedor_legajo' => substr($cod, 0, 3),
                'talle_sugerido' => '00',
                'color_sugerido' => '00',
                'codigo_proveedor' => substr($cod, 3),
            ];
        }

        return [
            'proveedor_legajo' => '000',
            'talle_sugerido' => '00',
            'color_sugerido' => '00',
            'codigo_proveedor' => $cod ?: '00001',
        ];
    }

    /**
     * Descripción completa enriquecida con Talle y Color.
     * Ej: "PANTALON CORDEROY CLASICO TULY (Talle: 42 - Color: NEGRO)"
     */
    public function getDescripcionCompletaAttribute(): string
    {
        $detalles = [];
        if (!empty($this->talle)) {
            $detalles[] = "Talle: {$this->talle}";
        }
        if (!empty($this->color)) {
            $detalles[] = "Color: {$this->color}";
        }

        if (!empty($detalles)) {
            return $this->descripcion . ' (' . implode(' - ', $detalles) . ')';
        }

        return $this->descripcion;
    }

    /**
     * Nombre del proveedor con el código entre paréntesis.
     * Ej: "ELY (cod. rem01234)" o "TULY JEANS (cod. 7040)"
     */
    public function getProveedorNombreConCodigoAttribute(): ?string
    {
        if (!$this->proveedor) {
            return null;
        }

        $nombre = $this->proveedor->nombre;
        $cod = $this->codigo_proveedor ?: ($this->proveedor->legajo_formateado ?? $this->proveedor->legajo);

        if (!empty($cod)) {
            return "{$nombre} (cod. {$cod})";
        }

        return $nombre;
    }

    /**
     * Variantes resumidas en formato corto: "T: 42 • C: NEGRO • Fab: 7040"
     */
    public function getVariantesTextoAttribute(): string
    {
        $partes = [];
        if (!empty($this->talle)) {
            $partes[] = "T: {$this->talle}";
        }
        if (!empty($this->color)) {
            $partes[] = "C: {$this->color}";
        }
        if (!empty($this->codigo_proveedor)) {
            $partes[] = "Fab: {$this->codigo_proveedor}";
        }
        return implode(' • ', $partes);
    }


    protected $casts = [
        'precio_costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'activo' => 'boolean',
        'controla_stock' => 'boolean',
        'total_ventas_unidades' => 'decimal:2',
        'total_ventas_operaciones' => 'integer',
        'fecha_alta' => 'datetime',
        'fecha_ultima_venta' => 'datetime',
        'fecha_ultima_compra' => 'datetime',
        'fecha_modificacion' => 'datetime',
    ];

    public function scopeInventariables($query)
    {
        return $query->where('controla_stock', true);
    }

    public function scopeGenericos($query)
    {
        return $query->where('controla_stock', false);
    }

    /**
     * Scope para productos sin rotación (sin ventas desde hace $meses meses)
     */
    public function scopeSinRotacion($query, int $meses = 24)
    {
        return $query->where('controla_stock', true)
            ->where(function ($q) use ($meses) {
                $q->where('fecha_ultima_venta', '<', now()->subMonths($meses))
                  ->orWhereNull('fecha_ultima_venta');
            });
    }

    public function diasSinVenta(): ?int
    {
        if (!$this->fecha_ultima_venta) {
            return null;
        }
        return (int)now()->diffInDays($this->fecha_ultima_venta);
    }

    public function getRotacionStatus(): array
    {
        if (!$this->controla_stock) {
            return ['label' => 'Sin Control', 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'];
        }

        if (!$this->fecha_ultima_venta) {
            return ['label' => 'Sin ventas', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800'];
        }

        $dias = $this->diasSinVenta();
        if ($dias >= 730) { // +2 años
            $anos = round($dias / 365, 1);
            return [
                'label' => "+{$anos}a sin venta", 
                'class' => 'bg-red-100 text-red-800 dark:bg-red-950/70 dark:text-red-300 border border-red-300 dark:border-red-800 font-bold'
            ];
        } elseif ($dias >= 365) { // +1 año
            return [
                'label' => '+1a sin venta', 
                'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800'
            ];
        } elseif ($dias >= 180) { // +6 meses
            return [
                'label' => '+6m sin venta', 
                'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950/60 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800'
            ];
        }

        return [
            'label' => 'Venta reciente', 
            'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
        ];
    }

    public function esInventariable(): bool
    {
        return (bool)$this->controla_stock;
    }

    public function categoria()
    {
        return $this->belongsTo(ProductoCategoria::class, 'categoria_id');
    }

    public function subcategoria()
    {
        return $this->belongsTo(ProductoSubcategoria::class, 'subcategoria_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function stocks()
    {
        return $this->hasMany(ProductoStock::class, 'producto_id');
    }

    public function movimientos()
    {
        return $this->hasMany(ProductoMovimiento::class, 'producto_id');
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'productos_stock')
                    ->withPivot('stock', 'stock_minimo', 'punto_compra', 'precio_venta', 'ubicacion')
                    ->withTimestamps();
    }

    /**
     * Obtiene el stock disponible en una sucursal específica.
     */
    public function getStockSucursal(int $sucursalId): float
    {
        $stockRow = $this->stocks()->where('sucursal_id', $sucursalId)->first();
        return $stockRow ? (float)$stockRow->stock : 0.0;
    }

    /**
     * Obtiene la suma de stock en todas las sucursales.
     */
    public function getStockTotal(): float
    {
        return (float)$this->stocks()->sum('stock');
    }

    /**
     * Registra un movimiento de stock atómico (alta, baja, corrección, venta, etc.)
     */
    public function ajustarStock(
        int $sucursalId,
        string $tipo,
        float $cantidad,
        ?string $motivo = null,
        ?int $usuarioId = null,
        ?string $comprobante = null,
        ?string $observaciones = null,
        ?Model $referencia = null
    ): ProductoMovimiento {
        return DB::transaction(function () use (
            $sucursalId, $tipo, $cantidad, $motivo, $usuarioId, $comprobante, $observaciones, $referencia
        ) {
            $stockRegistro = ProductoStock::firstOrCreate(
                ['producto_id' => $this->id, 'sucursal_id' => $sucursalId],
                [
                    'stock' => 0,
                    'stock_minimo' => $this->stock_minimo ?? 0,
                    'punto_compra' => 0,
                    'precio_venta' => $this->precio_venta,
                ]
            );

            $stockAnterior = (float)$stockRegistro->stock;
            $stockNuevo = $stockAnterior + $cantidad;

            // Actualizar stock de la sucursal
            $stockRegistro->stock = $stockNuevo;
            $stockRegistro->save();

            // Crear el registro de auditoría de movimiento
            $movimiento = new ProductoMovimiento([
                'producto_id' => $this->id,
                'sucursal_id' => $sucursalId,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'precio_costo' => $this->precio_costo,
                'precio_venta' => $stockRegistro->precio_venta ?? $this->precio_venta,
                'usuario_id' => $usuarioId,
                'comprobante' => $comprobante,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
            ]);

            if ($referencia) {
                $movimiento->referencia()->associate($referencia);
            }

            $movimiento->save();

            return $movimiento;
        });
    }

    /**
     * Traslada stock de una sucursal origen a una sucursal destino de manera atómica.
     */
    public function trasladar(
        int $sucursalOrigenId,
        int $sucursalDestinoId,
        float $cantidad,
        ?int $usuarioId = null,
        ?string $comprobante = null,
        ?string $motivo = null
    ): array {
        if (!$this->controla_stock) {
            throw new Exception("El artículo '{$this->descripcion}' es genérico / sin control de stock y no admite traslados.");
        }

        if ($sucursalOrigenId === $sucursalDestinoId) {
            throw new Exception("La sucursal de origen y destino no pueden ser la misma.");
        }

        if ($cantidad <= 0) {
            throw new Exception("La cantidad a trasladar debe ser mayor a cero.");
        }

        return DB::transaction(function () use (
            $sucursalOrigenId, $sucursalDestinoId, $cantidad, $usuarioId, $comprobante, $motivo
        ) {
            // 1. Obtener o crear stock en origen
            $stockOrigen = ProductoStock::firstOrCreate(
                ['producto_id' => $this->id, 'sucursal_id' => $sucursalOrigenId],
                ['stock' => 0, 'stock_minimo' => $this->stock_minimo ?? 0]
            );

            if ((float)$stockOrigen->stock < $cantidad) {
                throw new Exception("Stock insuficiente en la sucursal de origen (Disponible: {$stockOrigen->stock}).");
            }

            $stockOrigenAnterior = (float)$stockOrigen->stock;
            $stockOrigenNuevo = $stockOrigenAnterior - $cantidad;
            $stockOrigen->stock = $stockOrigenNuevo;
            $stockOrigen->save();

            // Movimiento de salida en origen
            $movSalida = ProductoMovimiento::create([
                'producto_id' => $this->id,
                'sucursal_id' => $sucursalOrigenId,
                'tipo' => 'traslado_salida',
                'cantidad' => -$cantidad,
                'stock_anterior' => $stockOrigenAnterior,
                'stock_nuevo' => $stockOrigenNuevo,
                'precio_costo' => $this->precio_costo,
                'precio_venta' => $this->precio_venta,
                'usuario_id' => $usuarioId,
                'sucursal_origen_id' => $sucursalOrigenId,
                'sucursal_destino_id' => $sucursalDestinoId,
                'comprobante' => $comprobante ?? 'TRASLADO-ENVIO',
                'motivo' => $motivo ?? 'Traslado inter-sucursal',
            ]);

            // 2. Obtener o crear stock en destino
            $stockDestino = ProductoStock::firstOrCreate(
                ['producto_id' => $this->id, 'sucursal_id' => $sucursalDestinoId],
                ['stock' => 0, 'stock_minimo' => $this->stock_minimo ?? 0]
            );

            $stockDestinoAnterior = (float)$stockDestino->stock;
            $stockDestinoNuevo = $stockDestinoAnterior + $cantidad;
            $stockDestino->stock = $stockDestinoNuevo;
            $stockDestino->save();

            // Movimiento de entrada en destino
            $movEntrada = ProductoMovimiento::create([
                'producto_id' => $this->id,
                'sucursal_id' => $sucursalDestinoId,
                'tipo' => 'traslado_entrada',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockDestinoAnterior,
                'stock_nuevo' => $stockDestinoNuevo,
                'precio_costo' => $this->precio_costo,
                'precio_venta' => $this->precio_venta,
                'usuario_id' => $usuarioId,
                'sucursal_origen_id' => $sucursalOrigenId,
                'sucursal_destino_id' => $sucursalDestinoId,
                'comprobante' => $comprobante ?? 'TRASLADO-RECEPCION',
                'motivo' => $motivo ?? 'Recepción de traslado inter-sucursal',
            ]);

            return [
                'salida' => $movSalida,
                'entrada' => $movEntrada,
            ];
        });
    }
}
