<div class="bg-slate-800 rounded-lg shadow-none p-6">
    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Kategori</h3>
    @if($categories->count())
        <ul class="space-y-2">
            @foreach($categories as $cat)
                <li>
                    <a href="{{ url('/category/' . $cat->slug) }}"
                       class="text-sm text-purple-600 hover:text-purple-800 hover:underline flex items-center justify-between">
                        <span>{{ $cat->name }}</span>
                        <span class="text-xs text-slate-500">{{ $cat->posts_count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-slate-400">Belum ada kategori.</p>
    @endif
</div>
