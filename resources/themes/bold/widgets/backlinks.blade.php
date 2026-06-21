<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Support By</h3>
    @if(!empty($widgetBacklinks))
        <ul class="space-y-3">
            @foreach($widgetBacklinks as $bl)
                @php
                    $blUrl = is_array($bl) ? ($bl['backlink_url'] ?? $bl['url'] ?? '') : '';
                    $blText = is_array($bl) ? ($bl['anchor_text'] ?? $blDomain) : '';
                @endphp
                <li>
                    <a href="{{ $blUrl }}"
                       target="_blank"
                       rel="nofollow"
                       class="text-sm text-orange-600 hover:text-orange-800 hover:underline block truncate">
                        {{ $blText }}
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-sm text-gray-500">No backlinks yet.</p>
    @endif
</div>
