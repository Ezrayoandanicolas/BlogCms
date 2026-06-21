<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Tag</h3>
    @if($tags->count())
        <div class="flex flex-wrap gap-2">
            @foreach($tags as $tag)
                <a href="{{ url('/tag/' . $tag->slug) }}"
                   class="bg-gray-100 text-gray-600 px-3 py-1 rounded text-xs hover:bg-orange-100 hover:text-orange-600 transition">
                    {{ $tag->name }}
                </a>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500">Belum ada tag.</p>
    @endif
</div>
