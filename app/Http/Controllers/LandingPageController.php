<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    public function index()
{
    try {
        return view('landingpage', [
            'basicPrice'    => Plan::where('plan_name', 'Paket Basic')->value('price_monthly') ?? 0,
            'standardPrice' => Plan::where('plan_name', 'Paket Standard')->value('price_monthly') ?? 0,
            'businessPrice' => Plan::where('plan_name', 'Paket Business')->value('price_monthly') ?? 0,
        ]);
    } catch (\Throwable $e) {
        return view('landingpage', [
            'basicPrice'    => 0,
            'standardPrice' => 0,
            'businessPrice' => 0,
        ]);
    }
}
}
