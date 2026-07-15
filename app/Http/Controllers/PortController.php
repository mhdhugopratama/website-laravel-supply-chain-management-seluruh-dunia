<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('country')) {
            $query->where('country_name', 'like', "%{$request->country}%");
        }

        $ports = $query->paginate(20)->withQueryString();
        return view('ports.index', compact('ports'));
    }
}
