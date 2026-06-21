@extends('theme::default.layouts.app')

@section('title', 'Pencarian: ' . $query . ' - ' . config('app.name'))

@php
    $hl = function($text) use ($query) {
        if (!$query) return e($text);
        $words = explode(' ', preg_replace('/\s+/', ' ', trim($query)));
        $pattern = '/(' . implode('|', array_map('preg_quote', $words)) . ')/iu';
        return preg_replace($pattern, '<mark class="bg-yellow-200 text-gray-900 px-0.5 rounded">$1</mark>', e($text));
    };
@endphp

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">
            @if($query)
                Hasil pencarian untuk: "{{ $query }}"
            @else
                Pencarian
            @endif
        </h1>

        <form action="{{ url('/search') }}" method="GET" class="mb-8">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $query }}"
                       placeholder="Cari artikel..."
                       class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Cari</button>
            </div>
        </form>

        @if($query)
            @if($posts->count())
                <div class="space-y-4">
                    @foreach($posts as $post)
                        <article class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold">
                                <a href="{{ url('/blog/' . $post->slug) }}" class="hover:text-blue-600">{!! $hl($post->title) !!}</a>
                            </h2>
                            <p class="text-gray-600 text-sm mt-1">{!! $hl(Str::limit(strip_tags($post->excerpt ?: $post->content), 120)) !!}</p>
                            <div class="mt-2 text-xs text-gray-400">{{ $post->published_at?->format('M d, Y') }}</div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-6">{{ $posts->links() }}</div>
            @else
                <p class="text-gray-500">Tidak ditemukan artikel untuk "{{ $query }}".</p>
            @endif
        @endif
    </div>
@endsection
