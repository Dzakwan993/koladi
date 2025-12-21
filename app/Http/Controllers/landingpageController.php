<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    public function index()
    {
        // Redirect kalau sudah login
        if (Auth::check()) {
            if (Auth::user()->isSystemAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        // HANYA ambil harga
        return view('landingpage', [
            'basicPrice'    => Plan::where('plan_name', 'Paket Basic')->value('price_monthly'),
            'standardPrice' => Plan::where('plan_name', 'Paket Standard')->value('price_monthly'),
            'businessPrice' => Plan::where('plan_name', 'Paket Business')->value('price_monthly'),
        ]);
    }
}

