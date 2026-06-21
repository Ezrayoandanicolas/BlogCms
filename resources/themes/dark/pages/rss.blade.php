<?php echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ url('/') }}</link>
        <description>Latest posts from {{ config('app.name') }}</description>
        <language>id</language>
        <atom:link href="{{ url('/feed') }}" rel="self" type="application/rss+xml"/>
        @foreach($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ url('/blog/' . $post->slug) }}</link>
                <guid isPermaLink="true">{{ url('/blog/' . $post->slug) }}</guid>
                <description>{{ $post->excerpt }}</description>
                <pubDate>{{ $post->published_at?->format('r') }}</pubDate>
                <category>{{ $post->category?->name }}</category>
            </item>
        @endforeach
    </channel>
</rss>
