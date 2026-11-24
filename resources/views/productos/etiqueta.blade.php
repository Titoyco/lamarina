<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas - {{ $producto->codigo }} ({{ $cantidad }} unid.)</title>
    <style>
        @page {
            size: 50mm 23.5mm;
            margin: 0mm !important;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #000;
        }

        /* Barra de control en pantalla (Oculta al imprimir) */
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            font-family: inherit;
        }

        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toolbar-title {
            font-size: 13px;
            font-weight: 700;
            color: #f8fafc;
        }

        .toolbar-desc {
            font-size: 11px;
            color: #94a3b8;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-back {
            background: #334155;
            color: #e2e8f0;
        }
        .btn-back:hover {
            background: #475569;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #1e293b;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #334155;
            font-size: 11px;
            color: #cbd5e1;
        }

        .qty-input {
            width: 48px;
            background: #0f172a;
            border: 1px solid #475569;
            color: #fff;
            font-weight: 700;
            font-family: monospace;
            padding: 3px 6px;
            border-radius: 4px;
            text-align: center;
        }

        /* Contenedor de visualización en pantalla */
        .preview-wrapper {
            padding-top: 65px;
            padding-bottom: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        /* Estructura Física de la Etiqueta (50mm x 25mm) calibrada para Zebra GC420t */
        .etiqueta-sticker {
            width: 50mm;
            height: 22.8mm;
            min-width: 50mm;
            max-width: 50mm;
            min-height: 22.8mm;
            max-height: 22.8mm;
            padding: 0.5mm 1.2mm 0.2mm 1.2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
            overflow: hidden;
            box-sizing: border-box;
            position: relative;
        }

        /* Borde punteado tenue solo en pantalla para simular el sticker adhesivo */
        @media screen {
            .etiqueta-sticker {
                border: 1px dashed #cbd5e1;
                border-radius: 3px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
        }

        /* Encabezado: Logo y Precio */
        .etiqueta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            height: 4.0mm;
            max-height: 4.0mm;
            overflow: hidden;
        }

        .etiqueta-logo {
            height: 3.8mm;
            max-width: 20mm;
            object-fit: contain;
        }

        .etiqueta-logo-text {
            font-size: 7.5pt;
            font-weight: 900;
            letter-spacing: 0.05em;
            color: #000;
            line-height: 1;
        }

        .etiqueta-precio {
            font-size: 10pt;
            font-weight: 900;
            color: #000;
            line-height: 1;
            text-align: right;
            white-space: nowrap;
        }

        /* Descripción del Producto */
        .etiqueta-desc {
            font-size: 6pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #000;
            text-align: center;
            line-height: 1;
            height: 2.6mm;
            max-height: 2.6mm;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            margin: 0.1mm 0;
        }

        /* Variantes de Talle, Color y Fabricante */
        .etiqueta-variantes {
            font-size: 5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #000;
            text-align: center;
            line-height: 1;
            height: 1.8mm;
            max-height: 1.8mm;
            overflow: hidden;
            white-space: nowrap;
        }

        /* Contenedor del Código de Barras SVG */
        .etiqueta-barcode {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 6.2mm;
            max-height: 6.2mm;
            overflow: hidden;
            margin: 0.1mm 0;
        }

        .etiqueta-barcode svg {
            width: 100% !important;
            height: 100% !important;
            max-height: 6.2mm;
            display: block;
        }

        /* Pie de Etiqueta (Código con asteriscos) */
        .etiqueta-footer {
            font-size: 6pt;
            font-weight: 800;
            font-family: "Courier New", Courier, monospace;
            text-align: center;
            letter-spacing: 0.15em;
            color: #000;
            line-height: 1;
            height: 2.0mm;
            max-height: 2.0mm;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Reglas estrictas de impresión para Zebra GC420t (EPL / ZPL) */
        @media print {
            @page {
                size: 50mm 23.5mm;
                margin: 0mm !important;
            }

            html, body {
                width: 50mm !important;
                height: 22.8mm !important;
                max-height: 22.8mm !important;
                margin: 0mm !important;
                padding: 0mm !important;
                background: #ffffff !important;
                overflow: hidden !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .preview-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
                display: block !important;
                width: 50mm !important;
            }

            .etiqueta-sticker {
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0.5mm 1.2mm 0.2mm 1.2mm !important;
                width: 50mm !important;
                max-width: 50mm !important;
                height: 22.8mm !important;
                max-height: 22.8mm !important;
                min-height: 22.8mm !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Corta página SOLO entre etiquetas, NUNCA en la última para no expulsar etiqueta vacía */
            .etiqueta-sticker:not(:last-child) {
                page-break-after: always !important;
                break-after: page !important;
            }

            .etiqueta-sticker:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

    {{-- BARRA DE CONTROL SUPERIOR (SOLO PANTALLA) --}}
    <div class="toolbar no-print">
        <div class="toolbar-info">
            <div>
                <div class="toolbar-title" style="display: flex; align-items: center; gap: 8px;">
                    <span>Etiqueta: {{ $producto->codigo }} (50mm × 25mm)</span>
                    @if($esAlta)
                        <span style="background: #166534; color: #bbf7d0; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 800; text-transform: uppercase;">
                            ✓ Ingreso de Stock ({{ $cantidad }} unid.)
                        </span>
                    @else
                        <span style="background: #854d0e; color: #fef08a; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 800; text-transform: uppercase;">
                            ⚠ Reimpresión (No modifica stock)
                        </span>
                    @endif
                </div>
                <div class="toolbar-desc">
                    {{ Str::limit($producto->descripcion, 45) }} 
                    @if($producto->proveedor)
                        • Prov: [{{ $producto->proveedor->legajo_formateado ?? 'S/L' }}] {{ $producto->proveedor->nombre }}
                    @endif
                    @if(!$esAlta)
                        <span style="color: #fcd34d; font-weight: 700;">• Motivo: {{ $motivo }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="toolbar-actions">
            {{-- Modificar cantidad de etiquetas --}}
            <form method="GET" action="{{ route('productos.etiqueta', $producto->id) }}" style="display: flex; align-items: center; gap: 6px;">
                @if($sucursalId)
                    <input type="hidden" name="sucursal_id" value="{{ $sucursalId }}">
                @endif
                <input type="hidden" name="es_alta" value="{{ $esAlta ? '1' : '0' }}">
                <input type="hidden" name="motivo" value="{{ $motivo }}">
                <div class="qty-control">
                    <span>Copias:</span>
                    <input type="number" name="cantidad" min="1" max="500" value="{{ $cantidad }}" class="qty-input">
                    <button type="submit" class="btn" style="background: #3b82f6; color: white; padding: 3px 8px; font-size: 11px;">
                        Actualizar
                    </button>
                </div>
            </form>

            <button onclick="window.print()" class="btn btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir {{ $cantidad }} {{ $cantidad == 1 ? 'Etiqueta' : 'Etiquetas' }}
            </button>

            <button onclick="window.close(); if(history.length > 1) history.back();" class="btn btn-back">
                Cerrar
            </button>
        </div>
    </div>

    {{-- CONTENEDOR DE ETIQUETAS A IMPRIMIR --}}
    <div class="preview-wrapper">
        {{-- Banner de advertencia en pantalla (no sale en la impresión térmica) --}}
        <div class="no-print" style="max-width: 520px; width: 94%; margin: 0 auto 14px auto;">
            @if($esAlta)
                <div style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; border-radius: 8px; padding: 10px 14px; font-size: 11px; line-height: 1.45; display: flex; align-items: flex-start; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <span style="font-size: 18px; line-height: 1;">📦</span>
                    <div>
                        <strong style="display: block; color: #14532d; font-size: 12px; margin-bottom: 2px;">Impresión por Ingreso de Mercadería ({{ $cantidad }} {{ $cantidad == 1 ? 'etiqueta' : 'etiquetas' }})</strong>
                        <span>Se están emitiendo las etiquetas correspondientes al stock ingresado. <strong style="color: #991b1b;">¡Atención al control de inventario!</strong> No imprima etiquetas de más para no distorsionar las existencias en góndola ni el stock del sistema.</span>
                    </div>
                </div>
            @else
                <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 8px; padding: 10px 14px; font-size: 11px; line-height: 1.45; display: flex; align-items: flex-start; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <span style="font-size: 18px; line-height: 1;">⚠️</span>
                    <div>
                        <strong style="display: block; color: #78350f; font-size: 12px; margin-bottom: 2px;">Modo Reimpresión de Etiquetas (No modifica el stock)</strong>
                        <span>Esta acción es <u>exclusivamente para reposición</u> por etiquetas dañadas, despegadas o fallas de impresión. <strong style="color: #b45309;">No genera movimientos de stock en el sistema</strong> ni debe utilizarse para ingresar mercadería nueva.</span>
                    </div>
                </div>
            @endif

            <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 8px; padding: 8px 12px; font-size: 11px; line-height: 1.4; display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                <span style="font-size: 16px;">🖨️</span>
                <div>
                    <strong>Zebra GC420t (50×25mm):</strong> En la ventana de impresión (Ctrl+P), en <em>Más opciones / Ajustes</em> configure: <strong>Márgenes: "Ninguno"</strong> y <strong>desmarque "Encabezados y pies de página"</strong> para evitar texto de URL y páginas adicionales.
                </div>
            </div>
        </div>

        @php
            $logoBase64 = null;
            if (file_exists(public_path('img/logo_letras.png'))) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('img/logo_letras.png')));
            } elseif (file_exists(public_path('img/logo.png'))) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('img/logo.png')));
            }
        @endphp

        @for($i = 0; $i < $cantidad; $i++)
            <div class="etiqueta-sticker">
                <!-- 1. Encabezado: Logo y Precio de Venta -->
                <div class="etiqueta-header">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="La Marina" class="etiqueta-logo">
                    @else
                        <span class="etiqueta-logo-text">LA MARINA</span>
                    @endif

                    <div class="etiqueta-precio">
                        ${{ number_format($precioVenta, 2, ',', '.') }}
                    </div>
                </div>

                <!-- 2. Descripción del Producto -->
                <div class="etiqueta-desc">
                    {{ $producto->descripcion }}
                </div>

                <!-- 2b. Talle, Color y Cód. Fabricante -->
                @if($producto->talle || $producto->color || $producto->codigo_proveedor)
                    <div class="etiqueta-variantes">
                        @if($producto->talle)<strong>T: {{ $producto->talle }}</strong>@endif
                        @if($producto->color)@if($producto->talle) • @endif<strong>C: {{ Str::limit($producto->color, 12) }}</strong>@endif
                        @if($producto->codigo_proveedor)@if($producto->talle || $producto->color) • @endif<span>Fab: {{ Str::limit($producto->codigo_proveedor, 10) }}</span>@endif
                    </div>
                @endif

                <!-- 3. Código de Barras (SVG Code128 / Code39) -->
                <div class="etiqueta-barcode">
                    @if($barcodeSvg)
                        {!! $barcodeSvg !!}
                    @else
                        <div style="font-size: 8px; color: red;">ERROR BARCODE</div>
                    @endif
                </div>

                <!-- 4. Pie de Etiqueta (Código espaciado con asteriscos) -->
                <div class="etiqueta-footer">
                    * {{ $code }} *
                </div>
            </div>
        @endfor
    </div>

    <script>
        // Disparo automático de impresión si se especifica autoprint=1
        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('autoprint') === '1') {
                setTimeout(function () {
                    window.print();
                }, 400);
            }
        });
    </script>
</body>
</html>
