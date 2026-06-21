@extends('theme::clean.layouts.app')
@section('title','Home - '.config('app.name'))
@section('content')
<div class="max-w-4xl mx-auto">
<h2 class="text-2xl font-light tracking-wider text-gray-600 mb-8 text-center">Latest Posts</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-16">
@forelse($posts??[] as $post)
<article class="group border-b border-gray-50 pb-8 mb-2">
@if($post->featured_image)<img src="{{$post->featured_image}}" alt="{{$post->title}}" class="w-full h-40 sm:h-48 object-cover" loading="lazy">@else<div class="w-full h-40 sm:h-48 bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold">{{strtoupper(substr($post->title,0,1))}}</div>@endif
<div class="p-4 sm:p-6"><h3 class="text-base sm:text-lg font-semibold mb-2"><a href="{{ url('/blog/' . $post->slug) }}" class="hover:text-teal-600">{{ $post->title }}</a></h3><p class="text-gray-600 text-sm">{{ Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p><div class="mt-4 text-xs text-gray-400">{{ $post->published_at?->format('M d, Y') }}</div></div>
</article>
@empty<p class="text-gray-500">No posts yet.</p>@endforelse
</div>
<div class="mt-8">{{$posts->links()}}</div>
</div>
@endsection