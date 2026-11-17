<?php

namespace App\Services;

use App\Models\PuntoVenta;
use App\Models\Venta;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use SoapClient;

class ArcaWsfeService
{
    // URLs de Web Services de ARCA / AFIP
    protected const WSAA_HOMO = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';
    protected const WSAA_PROD = 'https://wsaa.afip.gov.ar/ws/services/LoginCms';

    protected const WSFE_HOMO = 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL';
    protected const WSFE_PROD = 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?WSDL';

    /**
     * Autentica ante el WSAA para obtener el Token y Sign (con caché de 11.5 horas).
     */
    public function obtenerTokenSign(PuntoVenta $pv): array
    {
        $config = $pv->configuracion ?? [];
        $ambiente = $config['ambiente'] ?? 'homologacion';
        $cuit = preg_replace('/[^0-9]/', '', (string) ($config['cuit'] ?? '30712345678'));

        $certPath = $this->resolverRutaArchivo($config['certificado_path'] ?? null);
        $keyPath  = $this->resolverRutaArchivo($config['clave_privada_path'] ?? null);

        // Si faltan certificados y estamos en homologación/desarrollo, permitimos modo simulado
        if (!$certPath || !$keyPath || !file_exists($certPath) || !file_exists($keyPath)) {
            if ($ambiente === 'homologacion') {
                return [
                    'token'     => 'MOCK_TOKEN_HOMOLOGACION_' . time(),
                    'sign'      => 'MOCK_SIGN_HOMOLOGACION_' . time(),
                    'cuit'      => $cuit,
                    'simulado'  => true,
                ];
            }
            throw new Exception("No se encontraron los archivos de Certificado o Clave Privada de ARCA configurados para el Punto de Venta #{$pv->numero}.");
        }

        // Revisar caché en disco
        $cacheDir = storage_path('app/arca/tokens');
        if (!File::exists($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        $cacheFile = "{$cacheDir}/token_{$cuit}_{$ambiente}.json";
        if (File::exists($cacheFile)) {
            $cached = json_decode(File::get($cacheFile), true);
            if ($cached && isset($cached['expiration']) && strtotime($cached['expiration']) > (time() + 600)) {
                return [
                    'token'    => $cached['token'],
                    'sign'     => $cached['sign'],
                    'cuit'     => $cuit,
                    'simulado' => false,
                ];
            }
        }

        // Generar Ticket de Requerimiento de Acceso (TRA)
        $uniqueId = time();
        $genTime = date('c', time() - 300);
        $expTime = date('c', time() + 36000); // 10 horas

        $traXml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<loginTicketRequest version=\"1.0\">\n"
            . "  <header>\n"
            . "    <uniqueId>{$uniqueId}</uniqueId>\n"
            . "    <generationTime>{$genTime}</generationTime>\n"
            . "    <expirationTime>{$expTime}</expirationTime>\n"
            . "  </header>\n"
            . "  <service>wsfe</service>\n"
            . "</loginTicketRequest>";

        $tempTra = tempnam(sys_get_temp_dir(), 'TRA_');
        $tempCms = tempnam(sys_get_temp_dir(), 'CMS_');
        File::put($tempTra, $traXml);

        // Firmar con OpenSSL PKCS#7
        $signResult = openssl_pkcs7_sign(
            $tempTra,
            $tempCms,
            "file://{$certPath}",
            ["file://{$keyPath}", ''],
            [],
            !PKCS7_DETACHED
        );

        if (!$signResult) {
            @unlink($tempTra);
            @unlink($tempCms);
            $err = openssl_error_string();
            throw new Exception("Error al firmar digitalmente el TRA con la clave privada de ARCA: {$err}");
        }

        $cmsContent = File::get($tempCms);
        @unlink($tempTra);
        @unlink($tempCms);

        // Extraer contenido base64 del archivo DER/CMS generado
        $parts = explode("\n\n", $cmsContent, 2);
        $cmsBase64 = isset($parts[1]) ? trim($parts[1]) : trim($cmsContent);

        // Llamar a WSAA por SOAP
        $wsaaUrl = ($ambiente === 'produccion') ? self::WSAA_PROD : self::WSAA_HOMO;
        $client = new SoapClient(null, [
            'location'           => $wsaaUrl,
            'uri'                => 'http://wsaa.view.sua.dvadac.desein.afip.gov',
            'soap_version'       => SOAP_1_2,
            'trace'              => 1,
            'exceptions'         => 1,
            'connection_timeout' => 15,
        ]);

        try {
            $responseXml = $client->loginCms(['in0' => $cmsBase64]);
            $xml = simplexml_load_string($responseXml->loginCmsReturn);
            $token = (string) $xml->credentials->token;
            $sign = (string) $xml->credentials->sign;
            $expiration = (string) $xml->header->expirationTime;

            // Guardar en caché
            File::put($cacheFile, json_encode([
                'token'      => $token,
                'sign'       => $sign,
                'expiration' => $expiration,
            ]));

            return [
                'token'    => $token,
                'sign'     => $sign,
                'cuit'     => $cuit,
                'simulado' => false,
            ];
        } catch (Exception $e) {
            Log::error("Error WSAA ARCA: " . $e->getMessage());
            throw new Exception("Fallo de autenticación con ARCA (WSAA): " . $e->getMessage());
        }
    }

    /**
     * Emite una factura digital (Factura B o A) ante ARCA WSFE.
     */
    public function emitirFactura(PuntoVenta $pv, Venta $venta, int $tipoCbte = 6): array
    {
        $auth = $this->obtenerTokenSign($pv);

        $esTdf = $venta->sucursal ? $venta->sucursal->isTdf() : false;

        // Modo Simulación si no hay certificados reales cargados
        if (!empty($auth['simulado'])) {
            $cae = '7' . str_pad((string) mt_rand(100000000000, 999999999999), 13, '0', STR_PAD_LEFT);
            $vto = date('Y-m-d', strtotime('+10 days'));
            $nroCbte = ($venta->numero_factura > 0) ? $venta->numero_factura : mt_rand(100, 9999);

            $docNro = 0;
            $docTipo = 99;
            if (!empty($venta->cliente_documento) && is_numeric($venta->cliente_documento)) {
                $docNro = (int) $venta->cliente_documento;
                $docTipo = (strlen((string) $docNro) === 11) ? 80 : 96;
            }

            $qrData = $this->generarQrAfip([
                'cuit'      => $auth['cuit'],
                'ptoVta'    => $pv->numero,
                'tipoCmp'   => $tipoCbte,
                'nroCmp'    => $nroCbte,
                'importe'   => (float) $venta->total,
                'fecha'     => date('Y-m-d'),
                'tipoDoc'   => $docTipo,
                'nroDoc'    => $docNro,
                'cae'       => $cae,
            ]);

            return [
                'cae'             => $cae,
                'cae_vencimiento' => $vto,
                'numero_factura'  => $nroCbte,
                'tipo_cbte'       => $tipoCbte,
                'afip_qr'         => $qrData,
                'simulado'        => true,
                'es_tdf'          => $esTdf,
                'observaciones'   => $esTdf 
                    ? 'Comprobante simulado - Ushuaia (Exento de IVA - Ley 19.640).' 
                    : 'Comprobante simulado - Chubut (IVA 21% Gravado).',
            ];
        }

        // Llamar al WSFE
        $config = $pv->configuracion ?? [];
        $ambiente = $config['ambiente'] ?? 'homologacion';
        $wsdl = ($ambiente === 'produccion') ? self::WSFE_PROD : self::WSFE_HOMO;

        $client = new SoapClient($wsdl, [
            'soap_version'       => SOAP_1_2,
            'trace'              => 1,
            'exceptions'         => 1,
            'connection_timeout' => 20,
        ]);

        $authHeader = [
            'Token' => $auth['token'],
            'Sign'  => $auth['sign'],
            'Cuit'  => $auth['cuit'],
        ];

        // 1. Obtener el último comprobante autorizado
        $ultimoResp = $client->FECompUltimoAutorizado([
            'Auth'     => $authHeader,
            'PtoVta'   => (int) $pv->numero,
            'CbteTipo' => $tipoCbte,
        ]);

        $nroCbte = 1;
        if (isset($ultimoResp->FECompUltimoAutorizadoResult->CbteNro)) {
            $nroCbte = (int) $ultimoResp->FECompUltimoAutorizadoResult->CbteNro + 1;
        }

        // 2. Preparar importes según régimen fiscal
        // Ushuaia (Tierra del Fuego) = Ley 19.640 (Operaciones Exentas de IVA)
        // Chubut (Trelew / Interior) = Régimen General (Gravado IVA 21%)
        $total = (float) $venta->total;
        if ($esTdf) {
            $neto = 0.00;
            $iva = 0.00;
            $opEx = $total;
        } else {
            $neto = round($total / 1.21, 2);
            $iva = round($total - $neto, 2);
            $opEx = 0.00;
        }

        // Identificación del receptor
        $docTipo = 99; // Consumidor Final
        $docNro = 0;
        if (!empty($venta->cliente_documento) && is_numeric($venta->cliente_documento)) {
            $docNro = (int) $venta->cliente_documento;
            $docTipo = (strlen((string) $docNro) === 11) ? 80 : 96; // 80 = CUIT, 96 = DNI
        }

        // Si es Factura A, ARCA exige obligatoriamente DocTipo 80 (CUIT)
        if ($tipoCbte === 1 && $docTipo !== 80) {
            throw new Exception("Para emitir Factura A en ARCA es obligatorio un CUIT de 11 dígitos válido (DocTipo 80).");
        }

        $concepto = (int) ($config['concepto'] ?? 1);
        $fechaYmd = date('Ymd');

        $regFe = [
            'Concepto'   => $concepto,
            'DocTipo'    => $docTipo,
            'DocNro'     => $docNro,
            'CbteDesde'  => $nroCbte,
            'CbteHasta'  => $nroCbte,
            'CbteFch'    => $fechaYmd,
            'ImpTotal'   => $total,
            'ImpTotConc' => 0.00,
            'ImpNeto'    => $neto,
            'ImpOpEx'    => $opEx,
            'ImpTrib'    => 0.00,
            'ImpIVA'     => $iva,
            'MonId'      => 'PES',
            'MonCotiz'   => 1,
        ];

        // En operaciones gravadas con IVA > 0 se informa el desglose de alícuotas
        // En Ushuaia (Ley 19.640 - Exento) AFIP prohíbe enviar AlicIva
        if (!$esTdf && $iva > 0) {
            $regFe['Iva'] = [
                'AlicIva' => [
                    [
                        'Id'      => 5, // 21%
                        'BaseImp' => $neto,
                        'Importe' => $iva,
                    ]
                ]
            ];
        }

        $feReq = [
            'Auth' => $authHeader,
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg'  => 1,
                    'PtoVta'   => (int) $pv->numero,
                    'CbteTipo' => $tipoCbte,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => $regFe,
                ],
            ],
        ];

        $solicitud = $client->FECAESolicitar($feReq);
        $res = $solicitud->FECAESolicitarResult;

        if (isset($res->FeDetResp->FECAEDetResponse)) {
            $det = $res->FeDetResp->FECAEDetResponse;
            if ($det->Resultado === 'A') {
                $cae = (string) $det->CAE;
                $vto = date('Y-m-d', strtotime((string) $det->CAEFchVto));

                $qrData = $this->generarQrAfip([
                    'cuit'      => $auth['cuit'],
                    'ptoVta'    => $pv->numero,
                    'tipoCmp'   => $tipoCbte,
                    'nroCmp'    => $nroCbte,
                    'importe'   => $total,
                    'fecha'     => date('Y-m-d'),
                    'tipoDoc'   => $docTipo,
                    'nroDoc'    => $docNro,
                    'cae'       => $cae,
                ]);

                return [
                    'cae'             => $cae,
                    'cae_vencimiento' => $vto,
                    'numero_factura'  => $nroCbte,
                    'afip_qr'         => $qrData,
                    'simulado'        => false,
                ];
            } else {
                $msg = 'ARCA rechazó la factura.';
                if (isset($det->Observaciones->Obs)) {
                    $obs = is_array($det->Observaciones->Obs) ? $det->Observaciones->Obs : [$det->Observaciones->Obs];
                    $obsMsgs = array_map(fn($o) => "({$o->Code}) {$o->Msg}", $obs);
                    $msg .= ' ' . implode(', ', $obsMsgs);
                }
                throw new Exception($msg);
            }
        }

        if (isset($res->Errors->Err)) {
            $errors = is_array($res->Errors->Err) ? $res->Errors->Err : [$res->Errors->Err];
            $errMsgs = array_map(fn($e) => "({$e->Code}) {$e->Msg}", $errors);
            throw new Exception("Error devuelto por ARCA: " . implode(', ', $errMsgs));
        }

        throw new Exception("Respuesta inesperada al solicitar CAE a ARCA.");
    }

    /**
     * Emite una Nota de Crédito Electrónica vinculada a un comprobante original.
     */
    public function emitirNotaCredito(PuntoVenta $pv, Venta $ventaOriginal, string $motivo = 'Anulación de Venta'): array
    {
        $tipoCbteOriginal = ($ventaOriginal->tipo_comprobante === 'FACTURA_A') ? 1 : 6;
        $tipoNotaCredito = ($tipoCbteOriginal === 1) ? 3 : 8; // 8 = Nota de Crédito B, 3 = Nota de Crédito A

        $esTdf = $ventaOriginal->sucursal ? $ventaOriginal->sucursal->isTdf() : false;
        $auth = $this->obtenerTokenSign($pv);

        // Modo Simulación
        if (!empty($auth['simulado'])) {
            $cae = '7' . str_pad((string) mt_rand(100000000000, 999999999999), 13, '0', STR_PAD_LEFT);
            $vto = date('Y-m-d', strtotime('+10 days'));
            $nroCbte = mt_rand(100, 9999);

            $docNro = 0;
            $docTipo = 99;
            if (!empty($ventaOriginal->cliente_documento) && is_numeric($ventaOriginal->cliente_documento)) {
                $docNro = (int) $ventaOriginal->cliente_documento;
                $docTipo = (strlen((string) $docNro) === 11) ? 80 : 96;
            }

            $qrData = $this->generarQrAfip([
                'cuit'      => $auth['cuit'],
                'ptoVta'    => $pv->numero,
                'tipoCmp'   => $tipoNotaCredito,
                'nroCmp'    => $nroCbte,
                'importe'   => (float) $ventaOriginal->total,
                'fecha'     => date('Y-m-d'),
                'tipoDoc'   => $docTipo,
                'nroDoc'    => $docNro,
                'cae'       => $cae,
            ]);

            return [
                'cae'             => $cae,
                'cae_vencimiento' => $vto,
                'numero_factura'  => $nroCbte,
                'tipo_comprobante'=> ($tipoNotaCredito === 8) ? 'NOTA_CREDITO_B' : 'NOTA_CREDITO_A',
                'afip_qr'         => $qrData,
                'simulado'        => true,
                'es_tdf'          => $esTdf,
            ];
        }

        $config = $pv->configuracion ?? [];
        $ambiente = $config['ambiente'] ?? 'homologacion';
        $wsdl = ($ambiente === 'produccion') ? self::WSFE_PROD : self::WSFE_HOMO;

        $client = new SoapClient($wsdl, [
            'soap_version'       => SOAP_1_2,
            'trace'              => 1,
            'exceptions'         => 1,
            'connection_timeout' => 20,
        ]);

        $authHeader = [
            'Token' => $auth['token'],
            'Sign'  => $auth['sign'],
            'Cuit'  => $auth['cuit'],
        ];

        // Obtener último número de NC
        $ultimoResp = $client->FECompUltimoAutorizado([
            'Auth'     => $authHeader,
            'PtoVta'   => (int) $pv->numero,
            'CbteTipo' => $tipoNotaCredito,
        ]);

        $nroCbte = 1;
        if (isset($ultimoResp->FECompUltimoAutorizadoResult->CbteNro)) {
            $nroCbte = (int) $ultimoResp->FECompUltimoAutorizadoResult->CbteNro + 1;
        }

        $total = (float) $ventaOriginal->total;
        if ($esTdf) {
            $neto = 0.00;
            $iva = 0.00;
            $opEx = $total;
        } else {
            $neto = round($total / 1.21, 2);
            $iva = round($total - $neto, 2);
            $opEx = 0.00;
        }

        $docTipo = 99;
        $docNro = 0;
        if (!empty($ventaOriginal->cliente_documento) && is_numeric($ventaOriginal->cliente_documento)) {
            $docNro = (int) $ventaOriginal->cliente_documento;
            $docTipo = (strlen((string) $docNro) === 11) ? 80 : 96;
        }

        $regFe = [
            'Concepto'   => 1,
            'DocTipo'    => $docTipo,
            'DocNro'     => $docNro,
            'CbteDesde'  => $nroCbte,
            'CbteHasta'  => $nroCbte,
            'CbteFch'    => date('Ymd'),
            'ImpTotal'   => $total,
            'ImpTotConc' => 0.00,
            'ImpNeto'    => $neto,
            'ImpOpEx'    => $opEx,
            'ImpTrib'    => 0.00,
            'ImpIVA'     => $iva,
            'MonId'      => 'PES',
            'MonCotiz'   => 1,
            'CbtesAsoc'  => [
                'CbteAsoc' => [
                    [
                        'Tipo'   => $tipoCbteOriginal,
                        'PtoVta' => (int) $pv->numero,
                        'Nro'    => (int) $ventaOriginal->numero_factura,
                    ]
                ]
            ],
        ];

        if (!$esTdf && $iva > 0) {
            $regFe['Iva'] = [
                'AlicIva' => [
                    [
                        'Id'      => 5,
                        'BaseImp' => $neto,
                        'Importe' => $iva,
                    ]
                ]
            ];
        }

        $feReq = [
            'Auth' => $authHeader,
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg'  => 1,
                    'PtoVta'   => (int) $pv->numero,
                    'CbteTipo' => $tipoNotaCredito,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => $regFe,
                ],
            ],
        ];

        $solicitud = $client->FECAESolicitar($feReq);
        $res = $solicitud->FECAESolicitarResult;

        if (isset($res->FeDetResp->FECAEDetResponse)) {
            $det = $res->FeDetResp->FECAEDetResponse;
            if ($det->Resultado === 'A') {
                $cae = (string) $det->CAE;
                $vto = date('Y-m-d', strtotime((string) $det->CAEFchVto));

                $qrData = $this->generarQrAfip([
                    'cuit'      => $auth['cuit'],
                    'ptoVta'    => $pv->numero,
                    'tipoCmp'   => $tipoNotaCredito,
                    'nroCmp'    => $nroCbte,
                    'importe'   => $total,
                    'fecha'     => date('Y-m-d'),
                    'tipoDoc'   => $docTipo,
                    'nroDoc'    => $docNro,
                    'cae'       => $cae,
                ]);

                return [
                    'cae'              => $cae,
                    'cae_vencimiento'  => $vto,
                    'numero_factura'   => $nroCbte,
                    'tipo_comprobante' => ($tipoNotaCredito === 8) ? 'NOTA_CREDITO_B' : 'NOTA_CREDITO_A',
                    'afip_qr'          => $qrData,
                    'simulado'         => false,
                ];
            } else {
                $msg = 'ARCA rechazó la emisión de la Nota de Crédito.';
                if (isset($det->Observaciones->Obs)) {
                    $obs = is_array($det->Observaciones->Obs) ? $det->Observaciones->Obs : [$det->Observaciones->Obs];
                    $obsMsgs = array_map(fn($o) => "({$o->Code}) {$o->Msg}", $obs);
                    $msg .= ' ' . implode(', ', $obsMsgs);
                }
                throw new Exception($msg);
            }
        }

        throw new Exception("Error al emitir Nota de Crédito en ARCA.");
    }

    /**
     * Prueba la conexión autenticando contra el WSAA y consultando el estado de WSFE.
     */
    public function probarConexion(PuntoVenta $pv): array
    {
        $auth = $this->obtenerTokenSign($pv);

        if (!empty($auth['simulado'])) {
            return [
                'ok'      => true,
                'mensaje' => 'Conexión verificada en modo simulación / homologación preventiva (archivos CRT y KEY pendientes de carga).',
                'cuit'    => $auth['cuit'],
            ];
        }

        $config = $pv->configuracion ?? [];
        $ambiente = $config['ambiente'] ?? 'homologacion';
        $wsdl = ($ambiente === 'produccion') ? self::WSFE_PROD : self::WSFE_HOMO;

        $client = new SoapClient($wsdl, [
            'soap_version'       => SOAP_1_2,
            'trace'              => 1,
            'exceptions'         => 1,
            'connection_timeout' => 15,
        ]);

        $dummy = $client->FEDummy();
        $app = $dummy->FEDummyResult->AppServer ?? 'OK';
        $db = $dummy->FEDummyResult->DbServer ?? 'OK';
        $authStatus = $dummy->FEDummyResult->AuthServer ?? 'OK';

        return [
            'ok'      => ($app === 'OK' && $db === 'OK' && $authStatus === 'OK'),
            'mensaje' => "Conexión exitosa con Servidores ARCA ({$ambiente}). AppServer: {$app}, DbServer: {$db}, AuthServer: {$authStatus}.",
            'cuit'    => $auth['cuit'],
        ];
    }

    /**
     * Genera la URL oficial con el parámetro Base64 del Código QR según RG 5003 de ARCA.
     */
    public function generarQrAfip(array $datos): string
    {
        $json = json_encode([
            'ver'        => 1,
            'fecha'      => $datos['fecha'] ?? date('Y-m-d'),
            'cuit'       => (int) ($datos['cuit'] ?? 0),
            'ptoVta'     => (int) ($datos['ptoVta'] ?? 1),
            'tipoCmp'    => (int) ($datos['tipoCmp'] ?? 6),
            'nroCmp'     => (int) ($datos['nroCmp'] ?? 1),
            'importe'    => round((float) ($datos['importe'] ?? 0), 2),
            'moneda'     => 'PES',
            'ctz'        => 1,
            'tipoDocRec' => (int) ($datos['tipoDoc'] ?? 99),
            'nroDocRec'  => (int) ($datos['nroDoc'] ?? 0),
            'tipoCodAut' => 'E',
            'codAut'     => (int) ($datos['cae'] ?? 0),
        ], JSON_UNESCAPED_SLASHES);

        return 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode($json);
    }

    protected function resolverRutaArchivo(?string $path): ?string
    {
        if (!$path) return null;
        if (File::exists($path)) return $path;
        $storagePath = storage_path('app/' . ltrim($path, '/\\'));
        if (File::exists($storagePath)) return $storagePath;
        return null;
    }
}

