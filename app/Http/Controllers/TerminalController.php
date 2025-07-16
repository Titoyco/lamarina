<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Terminal;

class TerminalController extends Controller
{
    //    
    public function obtenerTerminal()
    {
        $terminales = Terminal::orderBy('nombre')->get();
        return $terminales;
    }
}
