<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;

class PosController extends Controller
{
    public function index()
    {
        return view('cashier.pos');
    }
}
