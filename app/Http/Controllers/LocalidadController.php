<?php

namespace App\Http\Controllers;

use App\Models\Localidad;
use Illuminate\Http\Request;

class LocalidadController extends Controller
{
    public function getLocalidades($provinciaId)
    {
        $localidades = Localidad::where('id_provincia', $provinciaId)
        ->orderBy('nombre', 'asc')
        ->get();
        return response()->json($localidades);
    }
}