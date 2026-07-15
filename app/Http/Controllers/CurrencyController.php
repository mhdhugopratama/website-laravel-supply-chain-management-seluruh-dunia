<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyService $currency) {}

    public function index()
    {
        $rates = $this->currency->getRates();
        return view('currency.index', compact('rates'));
    }
}
