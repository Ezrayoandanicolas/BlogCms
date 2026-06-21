@extends('theme::blue.layouts.app')
@section('title','Home - '.config('app.name'))
@section('content')
<div class="flex flex-col lg:flex-row-reverse gap-6 lg:gap-8 items-start">
<div class="flex-1 w-full self-start">

    @php $paginator = $posts; $carouselPosts = collect($paginator->items())->take(4); @endphp
    <section class="mb-8 overflow-hidden">
        <div class="flex gap-4 overflow-x-auto pb-4 snap-x snap-mandatory" id="carousel">
            @foreach($carouselPosts as $cp)
                <a href="{{ url('/blog/' . $cp->slug) }}" class="snap-start shrink-0 w-64 sm:w-72 group">
                    <div class="rounded-xl overflow-hidden shadow-sm border border-gray-50">
                        @if($cp->featured_image)<img src="{{ $cp->featured_image }}" alt="{{ $cp->title }}" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                        @else<div class="w-full h-36 bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-2xl font-bold">{{ strtoupper(substr($cp->title, 0, 1)) }}</div>@endif
                        <div class="p-3 bg-white"><h3 class="text-sm font-semibold leading-snug text-gray-900">{{ $cp->title }}</h3><p class="text-xs text-gray-400 mt-1">{{ $cp->published_at?->format('M d, Y') }}</p></div>
                    </div>
                </a>
            @endforeach
        </div>
    </section><h2 class="text-xl font-bold mb-6">Latest Posts</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
@forelse($paginator??$posts as $post)
<article class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all border border-gray-50">
@if($post->featured_image)<img src="{{$post->featured_image}}" alt="{{$post->title}}" class="w-full h-40 sm:h-48 object-cover" loading="lazy">@else<div class="w-full h-40 sm:h-48 bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold">{{strtoupper(substr($post->title,0,1))}}</div>@endif
<div class="p-4 sm:p-6"><h3 class="text-base sm:text-lg font-semibold mb-2"><a href="{{ url('/blog/' . $post->slug) }}" class="hover:text-cyan-600">{{ $post->title }}</a></h3><p class="text-gray-600 text-sm">{{ Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p><div class="mt-4 text-xs text-gray-400">{{ $post->published_at?->format('M d, Y') }}</div></div>
</article>
@empty<p class="text-gray-500">No posts yet.</p>@endforelse
</div>
<div class="mt-8">{{$posts->links()}}</div>
</div>
        <aside class="w-full lg:w-72 shrink-0 self-start flex flex-col gap-4">
            @include('theme::blue.widgets.categories')
            @include('theme::blue.widgets.tags')
            @include('theme::blue.widgets.recent_posts')
            @include('theme::blue.widgets.popular_posts')
            @php try { $resp = \Illuminate\Support\Facades\Http::timeout(10)->get('https://bibitgroup.org/api/fetchBacklink/1'); $widgetBacklinks = $resp->successful() ? ($resp->json() ?: []) : []; } catch(\Throwable $e) { $widgetBacklinks = []; } @endphp
            @include('theme::blue.widgets.backlinks')
        </aside>
</div>
@endsection