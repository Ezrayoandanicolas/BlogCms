@extends('theme::default.admin.layouts.app')

@section('title', isset($post) ? 'Edit Post' : 'New Post')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ isset($post) ? 'Edit Post' : 'New Post' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ isset($post) ? 'Update your blog post' : 'Create a new blog post' }}</p>
    </div>

    <form action="{{ isset($post) ? url('/admin/posts/' . $post->id) : url('/admin/posts') }}" method="POST" class="max-w-4xl">
        @csrf
        @if(isset($post)) @method('PUT') @endif

        @include('theme::default.admin.posts.json-paste')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Basic Info</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    @error('title')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow" placeholder="auto-generated">
                    @error('slug')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                    <select name="category_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="draft" {{ old('status', $post->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $post->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">Content</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Content *</label>
                <div class="flex items-center gap-2 mb-2">
                    <button type="button" onclick="document.getElementById('imgUpload').click()" class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Upload Image
                    </button>
                    <input type="file" id="imgUpload" accept="image/*" class="hidden">
                    <span id="img-status" class="text-xs text-gray-400"></span>
                </div>
                <textarea name="content" rows="15" required
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">{{ old('content', $post->content ?? '') }}</textarea>
                @error('content')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <script>
                document.getElementById('imgUpload')?.addEventListener('change', async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    const status = document.getElementById('img-status');
                    status.textContent = 'Uploading...';
                    status.className = 'text-xs text-blue-500';
                    const form = new FormData();
                    form.append('file', file);
                    try {
                        const resp = await fetch('{{ url("/admin/media/upload") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: form,
                        });
                        if (!resp.ok) throw new Error('Upload failed');
                        const data = await resp.json();
                        const url = data.data?.url || data.url;
                        const ta = document.querySelector('textarea[name="content"]');
                        const img = `<img src="${url}" alt="">`;
                        const start = ta.selectionStart, end = ta.selectionEnd;
                        ta.value = ta.value.substring(0, start) + img + ta.value.substring(end);
                        ta.selectionStart = ta.selectionEnd = start + img.length;
                        ta.focus();
                        status.textContent = '✅ Image inserted';
                        status.className = 'text-xs text-green-600';
                    } catch (err) {
                        status.textContent = '❌ ' + err.message;
                        status.className = 'text-xs text-red-500';
                    }
                    e.target.value = '';
                });
            </script>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Excerpt</label>
                <textarea name="excerpt" rows="2" placeholder="Brief summary of your post"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow resize-none">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Featured Image URL</label>
                <input type="url" name="featured_image" value="{{ old('featured_image', $post->featured_image ?? '') }}" placeholder="https://example.com/image.jpg"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-gray-800 mb-4">SEO Settings</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">SEO Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $post->seo_title ?? '') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">SEO Keywords</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $post->seo_keywords ?? '') }}" placeholder="keyword1, keyword2"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">SEO Description</label>
                <textarea name="seo_description" rows="2" placeholder="Meta description for search engines"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow resize-none">{{ old('seo_description', $post->seo_description ?? '') }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ isset($post) ? 'Update Post' : 'Create Post' }}
            </button>
            <a href="{{ url('/admin/posts') }}" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">Cancel</a>
        </div>
    </form>
@endsection
