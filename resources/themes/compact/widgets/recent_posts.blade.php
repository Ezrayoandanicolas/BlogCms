<div class="bg-white rounded-lg shadow p-3">
    <h3 class="text-sm font-semibold mb-2 pb-2 border-b">Terbaru</h3>
    @if($recentPosts->count())
        <ul class="space-y-3">
            @foreach($recentPosts as $rp)
                <li>
                    <a href="{{ url('/blog/' . $rp->slug) }}"
                       class="text-sm text-sky-600 hover:text-sky-800 hover:underline block leading-tight">
                        {{ $rp->title }}
                    </a>
                    <span class="text-xs text-gray-400">{{ $rp->published_at?->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-gray-500">Belum ada artikel.</p>
    @endif
</div>
