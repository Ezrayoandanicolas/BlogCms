@extends('theme::bold.admin.layouts.app')

@section('title', 'Media')

@section('content')
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Media Library</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage uploaded images</p>
        </div>
    </div>

    @include('theme::default.admin.media-uploader')

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" id="mediaGrid">
        @forelse($media as $m)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group relative">
                <img src="{{ $m['url'] }}" alt="{{ $m['filename'] }}" class="w-full h-32 object-cover" loading="lazy">
                <div class="p-2">
                    <p class="text-xs text-gray-600 truncate">{{ $m['filename'] }}</p>
                    <p class="text-[10px] text-gray-400">{{ number_format($m['size'] / 1024, 1) }} KB</p>
                </div>
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <button onclick="copyUrl('{{ $m['url'] }}')" class="bg-white text-gray-800 px-2 py-1 rounded text-xs font-medium hover:bg-gray-100">Copy URL</button>
                    <form action="{{ url('/admin/media/' . $m['id']) }}" method="POST" onsubmit="return confirm('Delete this file?')">
                        @csrf @method('DELETE')
                        <button class="bg-red-500 text-white px-2 py-1 rounded text-xs font-medium hover:bg-red-600">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="font-medium">No media yet</p>
                <p class="text-sm mt-1">Upload images to use in your posts.</p>
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
<script>
    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => { alert('URL copied!'); });
    }
</script>
@endpush
