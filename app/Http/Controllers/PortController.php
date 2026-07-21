<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\Country;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::with('country');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('country')) {
            $query->where('country_code', $request->country);
        }

        $ports = $query->paginate(20)->withQueryString();
        $countries = Country::orderBy('name')->get();
        
        return view('ports.index', compact('ports', 'countries'));
    }
}
