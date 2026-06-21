<div class="bg-white rounded-sm shadow p-6">
    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Kategori</h3>
    @if($categories->count())
        <ul class="space-y-2">
            @foreach($categories as $cat)
                <li>
                    <a href="{{ url('/category/' . $cat->slug) }}"
                       class="text-sm text-red-600 hover:text-red-800 hover:underline flex items-center justify-between">
                        <span>{{ $cat->name }}</span>
                        <span class="text-xs text-gray-400">{{ $cat->posts_count }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-gray-500">Belum ada kategori.</p>
    @endif
</div>
