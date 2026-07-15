<?php

namespace App\Http\Controllers;

use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(private NewsService $news) {}

    public function index(Request $request)
    {
        $query = $request->input('q', 'logistics shipping trade economy');
        $data  = $this->news->fetchNews($query);
        return view('news.index', compact('data', 'query'));
    }
}
