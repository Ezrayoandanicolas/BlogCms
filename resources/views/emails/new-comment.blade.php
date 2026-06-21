# Komentar Baru

**{{ $comment->name }}** ({{ $comment->email }}) meninggalkan komentar pada artikel **{{ $comment->post->title }}**.

> {{ $comment->content }}

@if($comment->status === 'pending')
**Status: Menunggu review**
@else
**Status: {{ $comment->status }}**
@endif

[Lihat di Admin]({{ url('/admin/comments') }})
