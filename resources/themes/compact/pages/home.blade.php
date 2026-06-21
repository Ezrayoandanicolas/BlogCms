@extends('theme::compact.layouts.app')
@section('title','Home - '.config('app.name'))
@section('content')
<div class="flex flex-col lg:flex-row gap-4 items-start">
        <aside class="w-full lg:w-72 shrink-0 self-start flex flex-col gap-4">
            @include('theme::compact.widgets.categories')
            @include('theme::compact.widgets.tags')
            @include('theme::compact.widgets.recent_posts')
            @include('theme::compact.widgets.popular_posts')
            @php try { $resp = \Illuminate\Support\Facades\Http::timeout(10)->get('https://bibitgroup.org/api/fetchBacklink/1'); $widgetBacklinks = $resp->successful() ? ($resp->json() ?: []) : []; } catch(\Throwable $e) { $widgetBacklinks = []; } @endphp
            @include('theme::compact.widgets.backlinks')
        </aside><div class="flex-1 w-full min-w-0 self-start"><h2 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Latest Posts</h2>
<div class="flex flex-col gap-2">
@forelse($posts??[] as $post)
<article class="flex gap-3 bg-white rounded p-3 shadow-sm hover:shadow transition-shadow">
@if($post->featured_image)<img src="{{$post->featured_image}}" alt="{{$post->title}}" class="w-20 h-20 object-cover rounded shrink-0" loading="lazy">@else<div class="w-20 h-20 bg-gradient-to-br from-sky-400 to-blue-500 rounded shrink-0 flex items-center justify-center text-white text-xl font-bold">{{strtoupper(substr($post->title,0,1))}}</div>@endif
<div class="flex-1 min-w-0"><h3 class="text-sm font-semibold leading-snug mb-0.5"><a href="{{ url('/blog/' . $post->slug) }}" class="hover:text-sky-600">{{ $post->title }}</a></h3><p class="text-xs text-gray-500 leading-relaxed">{{ Str::limit(strip_tags($post->excerpt ?: $post->content), 80) }}</p><div class="mt-1 text-[10px] text-gray-400">{{ $post->published_at?->format('M d, Y') }}</div></div>
</article>
@empty<p class="text-gray-500">No posts yet.</p>@endforelse
</div>
<div class="mt-6">{{$posts->links()}}</div>
</div>
        <aside class="w-full lg:w-72 shrink-0 self-start flex flex-col gap-4">
            @include('theme::compact.widgets.categories')
            @include('theme::compact.widgets.tags')
            @include('theme::compact.widgets.recent_posts')
            @include('theme::compact.widgets.popular_posts')
            @php try { $resp = \Illuminate\Support\Facades\Http::timeout(10)->get('https://bibitgroup.org/api/fetchBacklink/1'); $widgetBacklinks = $resp->successful() ? ($resp->json() ?: []) : []; } catch(\Throwable $e) { $widgetBacklinks = []; } @endphp
            @include('theme::compact.widgets.backlinks')
        </aside>
</div>
@endsection