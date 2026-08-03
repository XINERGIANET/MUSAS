<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RestauranteController extends Controller
{
    //
    public function index()
    {
        return view('restaurante.index');
    }

    public function create()
    {
        return view('restaurante.create');
    }
}
