@extends('theme::default.layouts.app')
@section('title','Home - '.config('app.name'))
@section('content')
<div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
<div class="flex-1 w-full self-start">
<h2 class="text-xl font-bold mb-6">Latest Posts</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6">
@forelse($posts??[] as $post)
<article class="bg-white rounded-lg shadow overflow-hidden">
@if($post->featured_image)<img src="{{$post->featured_image}}" alt="{{$post->title}}" class="w-full h-40 sm:h-48 object-cover" loading="lazy">@else<div class="w-full h-40 sm:h-48 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold">{{strtoupper(substr($post->title,0,1))}}</div>@endif
<div class="p-4 sm:p-6"><h3 class="text-base sm:text-lg font-semibold mb-2"><a href="{{ url('/blog/' . $post->slug) }}" class="hover:text-blue-600">{{ $post->title }}</a></h3><p class="text-gray-600 text-sm">{{ Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p><div class="mt-4 text-xs text-gray-400">{{ $post->published_at?->format('M d, Y') }}</div></div>
</article>
@empty<p class="text-gray-500">No posts yet.</p>@endforelse
</div>
<div class="mt-8">{{$posts->links()}}</div>
</div>
        <aside class="w-full lg:w-72 shrink-0 self-start flex flex-col gap-4">
            @include('theme::default.widgets.categories')
            @include('theme::default.widgets.tags')
            @include('theme::default.widgets.recent_posts')
            @include('theme::default.widgets.popular_posts')
            @php try { $resp = \Illuminate\Support\Facades\Http::timeout(10)->get('https://bibitgroup.org/api/fetchBacklink/1'); $widgetBacklinks = $resp->successful() ? ($resp->json() ?: []) : []; } catch(\Throwable $e) { $widgetBacklinks = []; } @endphp
            @include('theme::default.widgets.backlinks')
        </aside>
</div>
@endsection