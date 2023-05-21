<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(int $id)
    {
        return view('payment.show');
    }
}
