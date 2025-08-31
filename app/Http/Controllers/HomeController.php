<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
         return view('home');
    }



    public function testAuth()
{
    dd([
        'auth' => auth(),
        'user' => auth()->user()->toArray(), // Convert user to array for better readability
        'id' => auth()->id(),

    ]);
}

}


