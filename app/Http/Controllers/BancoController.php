<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BancoController extends Controller
{
    public function obtenerBanco($cbu)
    {
        // Validar que el CBU tenga 22 dígitos
        if (strlen($cbu) !== 22 || !preg_match('/^\d{22}$/', $cbu)) {
            return response()->json(['error' => 'CBU inválido'], 400);
        }

        // Verificar la validez del CBU
        if (!$this->validarCBU($cbu)) {
            return response()->json(['error' => 'CBU no válido'], 400);
        }

        // Obtener los primeros 3 dígitos del CBU
        $codigoBanco = substr($cbu, 0, 3);

        // Consultar el nombre del banco en la base de datos
        $banco = DB::table('codigos_bancos')->where('codigo', $codigoBanco)->first();

        if ($banco) {
            return response()->json(['nombre' => $banco->nombre]);
        } else {
            return response()->json(['error' => 'Banco no encontrado'], 404);
        }
    }
    private function validarCBU($cbu)
    {
        $CADENA = str_split($cbu);
        $CBU_VALIDO1 = false;
        $CBU_VALIDO2 = false;

        // Cálculo para el primer verificador
        $Suma1 = $CADENA[0] * 7 + $CADENA[1] * 1 + $CADENA[2] * 3 + $CADENA[3] * 9 + $CADENA[4] * 7 + $CADENA[5] * 1 + $CADENA[6] * 3;
        $verificador1 = str_split($Suma1);
        $Diferencial = 10 - $verificador1[count($verificador1) - 1];

        if ($Diferencial == $CADENA[7]) {
            $CBU_VALIDO1 = true;
        }
        if ($Diferencial == 10 && $CADENA[7] == 0) {
            $CBU_VALIDO1 = true;
        }

        // Cálculo para el segundo verificador
        $Suma2 = $CADENA[8] * 3 + $CADENA[9] * 9 + $CADENA[10] * 7 + $CADENA[11] * 1 + $CADENA[12] * 3 + $CADENA[13] * 9 + $CADENA[14] * 7 + $CADENA[15] * 1 + $CADENA[16] * 3 + $CADENA[17] * 9 + $CADENA[18] * 7 + $CADENA[19] * 1 + $CADENA[20] * 3;
        $verificador2 = str_split($Suma2);
        $Diferencial = 10 - $verificador2[count($verificador2) - 1];

        if ($Diferencial == $CADENA[21]) {
            $CBU_VALIDO2 = true;
        }
        if ($Diferencial == 10 && $CADENA[21] == 0) {
            $CBU_VALIDO2 = true;
        }

        return $CBU_VALIDO1 && $CBU_VALIDO2;
    }
}