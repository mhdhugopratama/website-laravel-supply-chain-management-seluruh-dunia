@extends('layouts.app')
@section('title', isset($article) ? 'Edit Article' : 'New Article')

@section('content')
<div class="nb-page-header">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
        <div>
            <h1>{{ isset($article) ? 'Edit Article' : 'Write New Article' }}</h1>
        </div>
        <a href="{{ route('admin.articles') }}" class="nb-btn nb-btn-outline">Back</a>
    </div>
</div>

<div class="container-fluid px-4">
    <div class="nb-card">
        <div class="nb-card-body">
            @if($errors->any())
                <div class="nb-alert nb-alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ isset($article) ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
                @csrf
                @isset($article) @method('PUT') @endisset

                <div class="mb-3">
                    <label style="font-weight:700;font-size:0.85rem">Article Title *</label>
                    <input type="text" name="title" class="nb-input mt-1" value="{{ old('title', $article->title ?? '') }}" required>
                </div>
                <div class="mb-3">
                    <label style="font-weight:700;font-size:0.85rem">Excerpt (Short Summary)</label>
                    <textarea name="excerpt" class="nb-input mt-1" rows="2" style="resize:vertical">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label style="font-weight:700;font-size:0.85rem">Source URL (Link to original article)</label>
                    <input type="url" name="source_url" class="nb-input mt-1" value="{{ old('source_url', $article->source_url ?? '') }}" placeholder="https://example.com/article">
                </div>
                <div class="mb-3">
                    <label style="font-weight:700;font-size:0.85rem">Body Content *</label>
                    <textarea name="body" class="nb-input mt-1" rows="12" style="resize:vertical;font-family:monospace" required>{{ old('body', $article->body ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label style="font-weight:700;font-size:0.85rem">Status</label>
                    <select name="status" class="nb-select mt-1">
                        <option value="draft" {{ (old('status', $article->status ?? '') === 'draft') ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ (old('status', $article->status ?? '') === 'published') ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="nb-btn nb-btn-primary">
                        <i class="bi bi-floppy"></i> {{ isset($article) ? 'Update Article' : 'Save Article' }}
                    </button>
                    <a href="{{ route('admin.articles') }}" class="nb-btn nb-btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
