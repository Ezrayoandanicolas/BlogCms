@extends('theme::magazine.layouts.app')

@section('title', $category->name . ' - ' . config('app.name'))
@section('meta_description', $category->seo_description)

@section('content')
    <h1 class="text-3xl font-bold mb-2">{{ $category->name }}</h1>
    @if($category->description)
        <p class="text-gray-600 mb-8">{{ $category->description }}</p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($posts as $post)
            <article class="bg-white rounded-sm shadow p-6">
                <h2 class="text-lg font-semibold mb-2">
                    <a href="{{ url('/blog/' . $post->slug) }}" class="hover:text-red-600">{{ $post->title }}</a>
                </h2>
                <p class="text-gray-600 text-sm">{{ Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p>
                <div class="mt-4 text-xs text-gray-400">{{ $post->published_at?->format('M d, Y') }}</div>
            </article>
        @empty
            <p class="col-span-3 text-gray-500">No posts in this category.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
@endsection
