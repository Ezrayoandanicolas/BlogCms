<div class="bg-slate-800 rounded-lg shadow-none p-6">
    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Tag</h3>
    @if($tags->count())
        <div class="flex flex-wrap gap-2">
            @foreach($tags as $tag)
                <a href="{{ url('/tag/' . $tag->slug) }}"
                   class="bg-slate-700 text-slate-300 px-3 py-1 rounded text-xs hover:bg-purple-100 hover:text-purple-600 transition">
                    {{ $tag->name }}
                </a>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-400">Belum ada tag.</p>
    @endif
</div>
