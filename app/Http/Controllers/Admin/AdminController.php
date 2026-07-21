<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Port;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $userCount    = User::count();
        $portCount    = Port::count();
        $articleCount = Article::count();
        return view('admin.index', compact('userCount', 'portCount', 'articleCount'));
    }

    public function users()
    {
        $users = User::paginate(20);
        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:user,admin']);
        $user->update(['role' => $request->role]);
        return back()->with('success', 'Role updated.');
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    public function ports()
    {
        $ports = Port::paginate(25);
        return view('admin.ports', compact('ports'));
    }

    public function storePort(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string',
            'country_code' => 'nullable|string|max:3',
            'country_name' => 'nullable|string',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'un_locode'    => 'nullable|string',
            'type'         => 'nullable|string',
        ]);
        Port::create($data);
        return back()->with('success', 'Port added.');
    }

    public function deletePort(Port $port)
    {
        $port->delete();
        return back()->with('success', 'Port deleted.');
    }

    public function articles()
    {
        $articles = Article::with('author')->paginate(15);
        return view('admin.articles', compact('articles'));
    }

    public function createArticle()
    {
        return view('admin.article-form');
    }

    public function storeArticle(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'excerpt'    => 'nullable|string',
            'source_url' => 'nullable|url',
            'body'       => 'required|string',
            'status'     => 'required|in:draft,published',
        ]);

        Article::create(array_merge($data, [
            'user_id' => auth()->id(),
            'slug'    => Str::slug($data['title']) . '-' . Str::random(5),
        ]));

        return redirect()->route('admin.articles')->with('success', 'Article saved.');
    }

    public function editArticle(Article $article)
    {
        return view('admin.article-form', compact('article'));
    }

    public function updateArticle(Request $request, Article $article)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'excerpt'    => 'nullable|string',
            'source_url' => 'nullable|url',
            'body'       => 'required|string',
            'status'     => 'required|in:draft,published',
        ]);

        $article->update($data);
        return redirect()->route('admin.articles')->with('success', 'Article updated.');
    }

    public function deleteArticle(Article $article)
    {
        $article->delete();
        return back()->with('success', 'Article deleted.');
    }
}
