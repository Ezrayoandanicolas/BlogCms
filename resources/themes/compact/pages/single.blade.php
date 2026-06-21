@extends('theme::compact.layouts.app')

@section('title', $post->title . ' - ' . config('app.name'))
@section('meta_description', $post->seo_description)
@section('meta_keywords', $post->seo_keywords)

@push('seo')
    @if($post->seo_title)
        <meta property="og:title" content="{{ $post->seo_title }}">
    @endif
    @if($post->seo_description)
        <meta property="og:description" content="{{ $post->seo_description }}">
    @endif
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url('/blog/' . $post->slug) }}">
    @if($post->featured_image)
        <meta property="og:image" content="{{ url($post->featured_image) }}">
        <meta name="twitter:image" content="{{ url($post->featured_image) }}">
    @else
        <meta property="og:image" content="{{ url('/api/v1/og-image?title=' . urlencode($post->title)) }}">
        <meta name="twitter:image" content="{{ url('/api/v1/og-image?title=' . urlencode($post->title)) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "{{ $post->title }}",
        "description": "{{ $post->seo_description ?? strip_tags($post->excerpt) }}",
        "image": "{{ $post->featured_image ? url($post->featured_image) : '' }}",
        "datePublished": "{{ $post->published_at?->toIso8601String() }}",
        "dateModified": "{{ $post->updated_at->toIso8601String() }}",
        "author": {
            "@type": "Person",
            "name": "{{ $post->user?->name ?? 'Admin' }}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "{{ config('app.name') }}"
        }
    }
    </script>
@endpush

@section('breadcrumb')
    @if($post->category)
        / <a href="{{ url('/category/' . $post->category->slug) }}" class="hover:text-sky-600">{{ $post->category->name }}</a>
    @endif
    / <span class="text-gray-700">{{ $post->title }}</span>
@endsection

@section('content')
    @php
        $backlinkApi = app(\App\Services\BacklinkApiService::class);
        $backlinks = $backlinkApi->claimAndGet($post->slug, request()->getHost(), 2);
        $shareUrl = urlencode(url('/blog/' . $post->slug));
        $shareTitle = urlencode($post->title);
    @endphp

    <div class="flex flex-col lg:flex-row gap-3 lg:gap-8 items-start">
        <div class="flex-1 w-full self-start">
            <article class="bg-white rounded-lg shadow p-3 lg:p-8">
                <h1 class="text-lg md:text-3xl font-bold mb-2">{{ $post->title }}</h1>
                <div class="text-sm text-gray-500 mb-3 flex flex-wrap items-center gap-2">
                    <span>By {{ $post->user?->name }}</span>
                    <span>&middot;</span>
                    <span>{{ $post->published_at?->format('M d, Y') }}</span>
                    <span>&middot;</span>
                    <span>{{ $minutes }} min read</span>
                    @if($post->views)
                        <span>&middot;</span>
                        <span>{{ number_format($post->views) }} views</span>
                    @endif
                    @if($post->category)
                        <span>&middot;</span>
                        <a href="{{ url('/category/' . $post->category->slug) }}" class="text-sky-600">{{ $post->category->name }}</a>
                    @endif
                </div>

                @if($post->featured_image)
                    <a href="{{ $post->featured_image }}" class="lightbox-trigger" onclick="event.preventDefault(); openLightbox(this.href)">
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full rounded-lg mb-3 cursor-pointer hover:opacity-95 transition-opacity" loading="lazy">
                    </a>
                @endif

                @php
                    $raw = $post->content;
                    $hasHtml = $raw !== strip_tags($raw);
                    if (!$hasHtml) {
                        $raw = '<p>' . implode('</p><p>', explode("\n\n", trim($raw))) . '</p>';
                    }
                    $parts = explode('</p>', $raw);
                @endphp

                @if(!empty($backlinks) && count($parts) >= 2)
                    @php
                        $url0 = $backlinks[0]['url'] ?? '';
                        $domain0 = $backlinkApi->fetchDomain($url0);
                        $title0 = $backlinkApi->fetchTitle($url0) ?: 'Artikel terkait';
                        $first = array_shift($parts);

                        $url1 = $backlinks[1]['url'] ?? '';
                        $domain1 = $backlinkApi->fetchDomain($url1);
                        $title1 = $backlinkApi->fetchTitle($url1) ?: 'Artikel terkait';
                        $last = array_pop($parts);
                    @endphp

                    {!! $first !!}</p>

                    <div style="margin:16px 0;padding:12px 16px;border-left:4px solid #2563eb;background:#f8fafc;font-size:14px">
                        <a href="{{ $url0 }}" target="_blank" rel="nofollow noopener" class="text-sky-600 hover:underline">
                            {{ $domain0 }} : {{ $title0 }}
                        </a>
                    </div>

                    @foreach($parts as $part)
                        {!! $part !!}</p>
                    @endforeach

                    <div style="margin:16px 0;padding:12px 16px;border-left:4px solid #2563eb;background:#f8fafc;font-size:14px">
                        <a href="{{ $url1 }}" target="_blank" rel="nofollow noopener" class="text-sky-600 hover:underline">
                            {{ $domain1 }} : {{ $title1 }}
                        </a>
                    </div>

                    {!! $last !!}
                @else
                    @foreach($parts as $part)
                        {!! $part !!}</p>
                    @endforeach
                @endif

                @if($post->tags->count())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                            <a href="{{ url('/tag/' . $tag->slug) }}" class="bg-gray-100 px-3 py-1 rounded text-sm hover:bg-gray-200">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 flex items-center gap-3 pt-4 border-t">
                    <span class="text-sm text-gray-500">Bagikan:</span>
                    <a href="https://www.facebook.com/sharer.php?u={{ $shareUrl }}"
                       target="_blank" rel="noopener" class="w-9 h-9 flex items-center justify-center rounded-full bg-sky-600 text-white hover:bg-sky-700 transition" title="Facebook">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879v-6.99h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.99C18.343 21.128 22 16.991 22 12z"/></svg>
                    </a>
                    <a href="https://x.com/intent/tweet?text={{ $shareTitle }}&url={{ $shareUrl }}"
                       target="_blank" rel="noopener" class="w-9 h-9 flex items-center justify-center rounded-full bg-black text-white hover:bg-gray-800 transition" title="X">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}"
                       target="_blank" rel="noopener" class="w-9 h-9 flex items-center justify-center rounded-full bg-green-500 text-white hover:bg-green-600 transition" title="WhatsApp">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </article>

            <nav class="mt-2 flex justify-between">
                <div>
                    @if($prevPost)
                        <a href="{{ url('/blog/' . $prevPost->slug) }}"
                           class="text-sky-600 hover:underline text-sm">
                            &laquo; {{ $prevPost->title }}
                        </a>
                    @endif
                </div>
                <div>
                    @if($nextPost)
                        <a href="{{ url('/blog/' . $nextPost->slug) }}"
                           class="text-sky-600 hover:underline text-sm">
                            {{ $nextPost->title }} &raquo;
                        </a>
                    @endif
                </div>
            </nav>

            @if($relatedPosts->count())
                <section class="mt-2">
                    <h2 class="text-base font-semibold mb-2">Artikel Terkait</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($relatedPosts as $rel)
                            <a href="{{ url('/blog/' . $rel->slug) }}"
                               class="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                                <h3 class="font-semibold text-sm hover:text-sky-600">{{ $rel->title }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $rel->published_at?->diffForHumans() }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-2 bg-white rounded-lg shadow p-3 lg:p-8">
                <h2 class="text-base font-semibold mb-3">Komentar ({{ $comments->count() }})</h2>

                @if(session('success'))
                    <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-2 text-sm">{{ session('success') }}</div>
                @endif

                <form action="{{ route('comments.store', $post->slug) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
                        <input type="text" name="name" placeholder="Nama *" required
                               class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                               value="{{ old('name') }}">
                        <input type="email" name="email" placeholder="Email *" required
                               class="border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"
                               value="{{ old('email') }}">
                    </div>
                    <textarea name="content" rows="4" placeholder="Tulis komentar..." required
                              class="w-full border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">{{ old('content') }}</textarea>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <button type="submit" class="mt-3 bg-sky-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-sky-700">
                        Kirim Komentar
                    </button>
                </form>

                @if($comments->count())
                    <div class="space-y-4">
                        @foreach($comments as $comment)
                            <div class="border-b pb-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-sm">{{ $comment->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    <button onclick="toggleReply({{ $comment->id }})" class="text-xs text-sky-600 hover:underline ml-auto">Balas</button>
                                </div>
                                <p class="text-sm text-gray-700">{{ $comment->content }}</p>

                                @if($comment->replies->count())
                                    <div class="ml-6 mt-3 space-y-3 border-l-2 border-gray-100 pl-4">
                                        @foreach($comment->replies as $reply)
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-semibold text-xs">{{ $reply->name }}</span>
                                                    <span class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-sm text-gray-700">{{ $reply->content }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div id="reply-form-{{ $comment->id }}" class="hidden mt-2 ml-6">
                                    <form action="{{ route('comments.store', $post->slug) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <input type="text" name="name" placeholder="Nama *" required
                                                   class="border rounded px-3 py-1.5 text-xs focus:ring-2 focus:ring-sky-500">
                                            <input type="email" name="email" placeholder="Email *" required
                                                   class="border rounded px-3 py-1.5 text-xs focus:ring-2 focus:ring-sky-500">
                                        </div>
                                        <textarea name="content" rows="2" placeholder="Tulis balasan..." required
                                                  class="w-full border rounded px-3 py-1.5 text-xs focus:ring-2 focus:ring-sky-500 mb-2"></textarea>
                                        <button type="submit" class="bg-sky-600 text-white px-4 py-1 rounded text-xs hover:bg-sky-700">Kirim Balasan</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <script>
                        function toggleReply(id) {
                            var form = document.getElementById('reply-form-' + id);
                            form.classList.toggle('hidden');
                        }
                    </script>
                @else
                    <p class="text-sm text-gray-500">Belum ada komentar. Jadilah yang pertama!</p>
                @endif
            </section>
        </div>

        <aside class="w-full lg:w-80 shrink-0 self-start flex flex-col gap-3">
            <div class="bg-white rounded-lg shadow p-3" id="toc-wrapper">
                <h3 class="text-sm font-semibold mb-2 pb-2 border-b">Daftar Isi</h3>
                <ul class="text-sm space-y-2" id="toc-list"></ul>
            </div>

            @include('theme::compact.widgets.categories')
            @include('theme::compact.widgets.tags')
            @include('theme::compact.widgets.recent_posts')

            @if(!empty($popularPosts) && $popularPosts->count())
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold mb-2 pb-2 border-b">Populer</h3>
                    <ul class="space-y-3">
                        @foreach($popularPosts as $pop)
                            <li>
                                <a href="{{ url('/blog/' . $pop->slug) }}"
                                   class="text-sm text-sky-600 hover:text-sky-800 hover:underline block">
                                    {{ $pop->title }}
                                </a>
                                <span class="text-xs text-gray-400">{{ $pop->published_at?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                try {
                    $resp = \Illuminate\Support\Facades\Http::timeout(10)
                        ->get('https://bibitgroup.org/api/fetchBacklink/1');
                    $widgetBacklinks = $resp->successful() ? ($resp->json() ?: []) : [];
                } catch (\Throwable $e) {
                    $widgetBacklinks = [];
                }
            @endphp
            @include('theme::compact.widgets.backlinks')
        </aside>
    </div>

<div id="lightbox-overlay" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center" onclick="this.classList.add('hidden')">
    <img id="lightbox-image" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg">
</div>

<script>
function openLightbox(src) {
    document.getElementById('lightbox-image').src = src;
    document.getElementById('lightbox-overlay').classList.remove('hidden');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('lightbox-overlay').classList.add('hidden');
});

(function() {
    var toc = document.getElementById('toc-list');
    if (toc) {
        var headings = document.querySelectorAll('article h2, article h3');
        headings.forEach(function(h, i) {
            var id = 'heading-' + i;
            h.setAttribute('id', id);
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.href = '#' + id;
            a.textContent = h.textContent;
            a.className = 'text-sky-600 hover:underline block ' + (h.tagName === 'H3' ? 'pl-3 text-xs' : '');
            li.appendChild(a);
            toc.appendChild(li);
        });
        if (headings.length === 0) document.getElementById('toc-wrapper')?.classList.add('hidden');
    }
})();
</script>
@endsection
