<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SistemaController extends Controller
{
    //
    public function index()
    {
        return view('sistema.index');
    }
}
