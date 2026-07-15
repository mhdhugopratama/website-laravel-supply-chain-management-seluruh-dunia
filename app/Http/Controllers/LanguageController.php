<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, ['en', 'id'])) {
            abort(400);
        }
        session(['locale' => $locale]);
        return redirect()->back();
    }
}
