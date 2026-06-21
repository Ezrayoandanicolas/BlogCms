@extends('theme::magazine.layouts.app')
@section('title','Home - '.config('app.name'))
@section('content')
<div class="flex flex-col lg:flex-row-reverse gap-6 lg:gap-8 items-start">
<div class="flex-1 w-full self-start">

    @php $paginator = $posts; $allItems = $paginator->items(); $featuredPosts = array_slice($allItems, 0, 3); $allItems = array_slice($allItems, 3); @endphp
    <section class="mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            @foreach($featuredPosts as $i => $fp)
                <a href="{{ url('/blog/' . $fp->slug) }}" class="relative group overflow-hidden rounded-lg shadow-lg">
                    @if($fp->featured_image)<img src="{{ $fp->featured_image }}" alt="{{ $fp->title }}" class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else<div class="w-full h-56 bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white text-4xl font-bold">{{ strtoupper(substr($fp->title, 0, 1)) }}</div>@endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <span class="text-white/80 text-xs uppercase tracking-wider">{{ $fp->category?->name ?? 'Featured' }}</span>
                        <h3 class="text-white font-bold text-lg leading-snug mt-1">{{ $fp->title }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </section><h2 class="font-serif text-3xl font-bold text-gray-900 mb-6">Latest Posts</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
@forelse($allItems as $post)
<article class="bg-white rounded-sm shadow-sm hover:shadow-md transition-shadow border-b-2 border-red-500">
@if($post->featured_image)<img src="{{$post->featured_image}}" alt="{{$post->title}}" class="w-full h-40 sm:h-48 object-cover" loading="lazy">@else<div class="w-full h-40 sm:h-48 bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold">{{strtoupper(substr($post->title,0,1))}}</div>@endif
<div class="p-4 sm:p-6"><h3 class="text-base sm:text-lg font-semibold mb-2"><a href="{{ url('/blog/' . $post->slug) }}" class="hover:text-red-600">{{ $post->title }}</a></h3><p class="text-gray-600 text-sm">{{ Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p><div class="mt-4 text-xs text-gray-400">{{ $post->published_at?->format('M d, Y') }}</div></div>
</article>
@empty<p class="text-gray-500">No posts yet.</p>@endforelse
</div>
<div class="mt-8">{{$paginator->links()}}</div>
</div>
        <aside class="w-full lg:w-72 shrink-0 self-start flex flex-col gap-4">
            @include('theme::magazine.widgets.categories')
            @include('theme::magazine.widgets.tags')
            @include('theme::magazine.widgets.recent_posts')
            @include('theme::magazine.widgets.popular_posts')
            @php try { $resp = \Illuminate\Support\Facades\Http::timeout(10)->get('https://bibitgroup.org/api/fetchBacklink/1'); $widgetBacklinks = $resp->successful() ? ($resp->json() ?: []) : []; } catch(\Throwable $e) { $widgetBacklinks = []; } @endphp
            @include('theme::magazine.widgets.backlinks')
        </aside>
</div>
@endsection