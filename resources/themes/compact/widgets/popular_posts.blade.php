@if(!empty($popularPosts))
    <div class="bg-white rounded-lg shadow p-3 lg:p-8">
        <h3 class="font-semibold mb-3 text-sm text-gray-800">Populer</h3>
        <ul class="space-y-2.5">
            @foreach($popularPosts as $pp)
                <li>
                    <a href="{{ url('/blog/' . $pp->slug) }}" class="text-sm text-gray-700 hover:text-sky-600">{{ $pp->title }}</a>
                    <br><span class="text-xs text-gray-400">{{ $pp->views ?? 0 }} views</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
