<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\EscposImage; // <--- Necesario para el logo

class TicketController extends Controller
{
    public function imprimirEtiqueta()
    {
        $nombreImpresora = "ticket_pos"; 
        
        // Simulación de datos (reemplaza esto con tus variables reales)
        $producto = (object)[
            'descripcion' => 'CABLE USB TIPO C 1M',
            'codigo' => 'A-123',
            'barcode_raw' => '7791234' // El número puro, sin HTML
        ];
        $precio_venta = 15500.00;

        try {
            $connector = new WindowsPrintConnector($nombreImpresora);
            $profile = CapabilityProfile::load("simple");
            $printer = new Printer($connector, $profile);

            $printer->initialize();
            $printer->setEmphasis(true); // Siempre negrita para contraste
            // Carga la imagen desde la carpeta public de Laravel
            // La imagen DEBE ser .png monocromático (blanco y negro puro) y pequeña (ej. 100px ancho)
            if (file_exists(public_path('images/logo_ticket.png'))) {
                $logo = EscposImage::load(public_path('images/logo_ticket.png'), false);
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->bitImage($logo); 
            }
            // 1. ENCABEZADO: PRECIO (Estilo CSS: text-right font-bold text-lg)
            // Nota: Poner logo e imagen lado a lado es complejo y lento en estas impresoras.
            // Recomendación: Priorizar el precio grande.
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->setTextSize(2, 2); // Doble tamaño (Simula text-lg font-bold)
            if ($precio_venta !== null) {
                $printer->text("$" . number_format($precio_venta, 0, ',', '.') . "\n");
            } else {
                $printer->text("No disp.\n");
            }
            $printer->setTextSize(1, 1); // Volver a normal

            // 2. DESCRIPCIÓN (Estilo CSS: text-[8px])
            // Usamos fuente B si la impresora la soporta (es más pequeña), si no, normal.
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setFont(Printer::FONT_B); // Fuente pequeña
            // Recortamos texto si es muy largo para que no ocupe 2 líneas
            $descripcion = substr($producto->descripcion, 0, 30); 
            $printer->text($descripcion . "\n");
            $printer->setFont(Printer::FONT_A); // Volver a fuente normal

            // 3. CÓDIGO DE BARRAS (Estilo CSS: barcode-container)
            // Ajustamos altura para que entre en los 25mm totales
            $printer->setBarcodeHeight(60); // Altura en puntos (aprox 6-7mm)
            $printer->setBarcodeWidth(2);   // Ancho de barras
            // IMPORTANTE: Quitamos el texto automático del barcode para ahorrar espacio vertical
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW); 
            
            try {
                // Intentamos imprimir CODE128 (mejor densidad) o CODE39
                $printer->barcode("123456789", Printer::BARCODE_CODE39);
            } catch (\Exception $e) {
                // Fallback por si el código tiene caracteres no válidos
                $printer->text("ERR: BARCODE\n");
            }

            // 4. PIE DE PÁGINA (Estilo CSS: letter-spacing: 0.5em)
            // Manualmente agregamos espacios para simular el letter-spacing
            $codigoEspaciado = implode(" ", str_split($producto->codigo));
            $printer->text("\n"); // Un pequeño salto tras el código de barras
            $printer->setEmphasis(true);
            $printer->text("* " . $codigoEspaciado . " *\n");

            // 5. Finalizar
            $printer->feed(1); // Ajustar según donde corte tu impresora (gap detection)
            // $printer->cut(); // Solo si tiene guillotina. Si es de etiquetas, suele despegar sola.
            
            $printer->close();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}