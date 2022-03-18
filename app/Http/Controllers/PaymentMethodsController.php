<?php


namespace App\Http\Controllers;


use App\Models\PaymentMethod;

class PaymentMethodsController extends Controller
{
    public function index()
    {
        return response()->json(['payment_methods' => PaymentMethod::all()]);
    }
}
