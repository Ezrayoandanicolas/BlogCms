<div class="flex items-center gap-2 mb-2">
    <button type="button" onclick="document.getElementById('imgUpload').click()" class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Upload Image
    </button>
    <input type="file" id="imgUpload" accept="image/*" class="hidden">
    <span id="img-status" class="text-xs text-gray-400"></span>
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
        const resp = await fetch('/admin/media/upload', {
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