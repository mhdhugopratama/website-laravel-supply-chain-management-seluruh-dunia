@extends('layouts.app')
@section('title', 'Manage Articles — Admin')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
        <div>
            <h1><i class="bi bi-journal-richtext"></i> Article Management</h1>
            <p>{{ $articles->total() }} articles total</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.articles.create') }}" class="nb-btn nb-btn-primary"><i class="bi bi-pencil-plus"></i> New Article</a>
            <a href="{{ route('admin.index') }}" class="nb-btn nb-btn-outline"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="nb-card">
        <div class="nb-card-body p-0">
            <table class="nb-table">
                <thead>
                    <tr><th>Title</th><th>Author</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td><strong>{{ Str::limit($article->title, 50) }}</strong></td>
                        <td>{{ $article->author->name }}</td>
                        <td>
                            <span class="nb-badge {{ $article->status === 'published' ? 'nb-badge-success' : 'nb-badge-warning' }}">
                                {{ $article->status }}
                            </span>
                        </td>
                        <td>{{ $article->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.articles.edit', $article) }}" class="nb-btn nb-btn-cyan btn-sm"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.articles.delete', $article) }}" onsubmit="return confirm('Delete article?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="nb-btn nb-btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="nb-card-body pt-0">
            <div class="nb-pagination">{{ $articles->links('vendor.pagination.nb') }}</div>
        </div>
    </div>
</div>
@endsection
