@if(!empty($popularPosts))
    <div class="bg-slate-800 rounded-lg shadow-none p-4 sm:p-6 lg:p-8">
        <h3 class="font-semibold mb-3 text-sm text-slate-100">Populer</h3>
        <ul class="space-y-2.5">
            @foreach($popularPosts as $pp)
                <li>
                    <a href="{{ url('/blog/' . $pp->slug) }}" class="text-sm text-slate-200 hover:text-purple-600">{{ $pp->title }}</a>
                    <br><span class="text-xs text-slate-500">{{ $pp->views ?? 0 }} views</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
