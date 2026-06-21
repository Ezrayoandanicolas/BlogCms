<div class="bg-slate-800 rounded-lg shadow-none p-6">
    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Terbaru</h3>
    @if($recentPosts->count())
        <ul class="space-y-3">
            @foreach($recentPosts as $rp)
                <li>
                    <a href="{{ url('/blog/' . $rp->slug) }}"
                       class="text-sm text-purple-600 hover:text-purple-800 hover:underline block leading-tight">
                        {{ $rp->title }}
                    </a>
                    <span class="text-xs text-slate-500">{{ $rp->published_at?->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-slate-400">Belum ada artikel.</p>
    @endif
</div>
