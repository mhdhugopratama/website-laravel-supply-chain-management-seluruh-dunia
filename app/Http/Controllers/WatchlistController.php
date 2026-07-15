<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate(['country_id' => 'required|exists:countries,id']);

        $existing = Watchlist::where('user_id', Auth::id())
            ->where('country_id', $request->country_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['action' => 'removed']);
        }

        Watchlist::create([
            'user_id'    => Auth::id(),
            'country_id' => $request->country_id,
        ]);

        return response()->json(['action' => 'added']);
    }

    public function index()
    {
        $watchlist = Auth::user()->watchedCountries()->get();
        return view('watchlist.index', compact('watchlist'));
    }
}
