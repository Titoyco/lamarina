<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lote de Etiquetas Térmicas - {{ $totalEtiquetas }} sticker(s)</title>
    <style>
        @page {
            size: 50mm 25mm;
            margin: 0;
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

        /* Contenedor de visualización en pantalla */
        .preview-wrapper {
            padding-top: 65px;
            padding-bottom: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        /* Estructura Física de la Etiqueta (50mm x 25mm) */
        .etiqueta-sticker {
            width: 50mm;
            height: 25mm;
            min-width: 50mm;
            max-width: 50mm;
            min-height: 25mm;
            max-height: 25mm;
            padding: 1.5mm 2mm 1mm 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
            overflow: hidden;
            box-sizing: border-box;
            page-break-after: always;
            break-after: page;
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
            height: 5.2mm;
            overflow: hidden;
        }

        .etiqueta-logo {
            height: 4.8mm;
            max-width: 20mm;
            object-fit: contain;
        }

        .etiqueta-logo-text {
            font-size: 8pt;
            font-weight: 900;
            letter-spacing: 0.05em;
            color: #000;
            line-height: 1;
        }

        .etiqueta-precio {
            font-size: 11pt;
            font-weight: 900;
            color: #000;
            line-height: 1;
            text-align: right;
            white-space: nowrap;
        }

        /* Descripción del Producto */
        .etiqueta-desc {
            font-size: 6.8pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #000;
            text-align: center;
            line-height: 1.1;
            max-height: 3.6mm;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        /* Variantes de Talle, Color y Fabricante */
        .etiqueta-variantes {
            font-size: 6pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #000;
            text-align: center;
            line-height: 1;
            height: 2.5mm;
            overflow: hidden;
            white-space: nowrap;
        }

        /* Contenedor del Código de Barras SVG */
        .etiqueta-barcode {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 8.2mm;
            overflow: hidden;
        }

        .etiqueta-barcode svg {
            width: 100% !important;
            height: 100% !important;
            max-height: 8.2mm;
            display: block;
        }

        /* Pie de Etiqueta (Código con asteriscos) */
        .etiqueta-footer {
            font-size: 7.2pt;
            font-weight: 800;
            font-family: "Courier New", Courier, monospace;
            text-align: center;
            letter-spacing: 0.22em;
            color: #000;
            line-height: 1;
            height: 3mm;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Reglas estrictas de impresión */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .preview-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
                display: block !important;
            }

            .etiqueta-sticker {
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 1.5mm 2mm 1mm 2mm !important;
                width: 50mm !important;
                height: 25mm !important;
                page-break-after: always !important;
                break-after: page !important;
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
                    <span>Lote de Etiquetas Térmicas (50mm × 25mm)</span>
                    <span style="background: #166534; color: #bbf7d0; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 800; text-transform: uppercase;">
                        Total: {{ $totalEtiquetas }} sticker(s)
                    </span>
                </div>
                <div class="toolbar-desc">
                    {{ count($etiquetasAImprimir) }} artículo(s) con códigos de barra CODE128
                </div>
            </div>
        </div>

        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Todo ({{ $totalEtiquetas }} etiquetas)
            </button>

            <a href="{{ route('productos.traslado') }}" class="btn btn-back">
                Volver a Traslados
            </a>
        </div>
    </div>

    {{-- CONTENEDOR DE ETIQUETAS A IMPRIMIR --}}
    <div class="preview-wrapper">
        {{-- Banner informativo en pantalla --}}
        <div class="no-print" style="max-width: 540px; width: 94%; margin: 0 auto 14px auto;">
            <div style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; border-radius: 8px; padding: 10px 14px; font-size: 11px; line-height: 1.45; display: flex; align-items: flex-start; gap: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <span style="font-size: 18px; line-height: 1;">🏷️</span>
                <div>
                    <strong style="display: block; color: #14532d; font-size: 12px; margin-bottom: 2px;">
                        Etiquetas de Prendas Trasladadas / Rectificadas ({{ $totalEtiquetas }} unidades)
                    </strong>
                    <span>
                        Se incluyen los códigos de barras CODE128 listos para rotular los artículos físicos.
                        Haga clic en <strong>"Imprimir Todo"</strong> o use <kbd style="background: #e2e8f0; padding: 1px 4px; border-radius: 3px; font-family: monospace;">Ctrl + P</kbd>.
                    </span>
                </div>
            </div>
        </div>

        @foreach($etiquetasAImprimir as $item)
            @for($i = 0; $i < $item['cantidad']; $i++)
                <div class="etiqueta-sticker">
                    <!-- 1. Encabezado: Logo y Precio de Venta -->
                    <div class="etiqueta-header">
                        @if(file_exists(public_path('img/logo_letras.png')))
                            <img src="{{ asset('img/logo_letras.png') }}" alt="La Marina" class="etiqueta-logo">
                        @elseif(file_exists(public_path('img/logo.png')))
                            <img src="{{ asset('img/logo.png') }}" alt="La Marina" class="etiqueta-logo">
                        @else
                            <span class="etiqueta-logo-text">LA MARINA</span>
                        @endif

                        <div class="etiqueta-precio">
                            ${{ number_format($item['precioVenta'], 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- 2. Descripción del Producto -->
                    <div class="etiqueta-desc">
                        {{ $item['producto']->descripcion }}
                    </div>

                    <!-- 2b. Talle, Color y Cód. Fabricante -->
                    @if($item['talle'] || $item['color'] || $item['producto']->codigo_proveedor)
                        <div class="etiqueta-variantes">
                            @if($item['talle'])<strong>T: {{ $item['talle'] }}</strong>@endif
                            @if($item['color'])@if($item['talle']) • @endif<strong>C: {{ Str::limit($item['color'], 12) }}</strong>@endif
                            @if($item['producto']->codigo_proveedor)@if($item['talle'] || $item['color']) • @endif<span>Fab: {{ Str::limit($item['producto']->codigo_proveedor, 10) }}</span>@endif
                        </div>
                    @endif

                    <!-- 3. Código de Barras (SVG Code128 / Code39) -->
                    <div class="etiqueta-barcode">
                        @if($item['barcodeSvg'])
                            {!! $item['barcodeSvg'] !!}
                        @else
                            <div style="font-size: 8px; color: red;">ERROR BARCODE</div>
                        @endif
                    </div>

                    <!-- 4. Pie de Etiqueta (Código espaciado con asteriscos) -->
                    <div class="etiqueta-footer">
                        * {{ $item['code'] }} *
                    </div>
                </div>
            @endfor
        @endforeach
    </div>

    <script>
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
