<?php

namespace App\Http\Controllers;

use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(private NewsService $news) {}

    public function index(Request $request)
    {
        $query = $request->input('q'); // the view will get an empty string if not provided
        $apiQuery = $query ?: 'logistics OR supply chain OR trade OR economy';
        
        $forceRefresh = $request->has('refresh');
        $data  = $this->news->fetchNews($apiQuery, null, $forceRefresh);
        return view('news.index', compact('data', 'query'));
    }
}
