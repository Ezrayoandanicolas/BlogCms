<div id="dropzone"
     class="border-2 border-dashed border-gray-300 rounded-2xl p-10 mb-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/30 transition-all">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    <p class="text-gray-600 font-medium">Drag & drop images here</p>
    <p class="text-sm text-gray-400 mt-1">or click to browse</p>
    <input type="file" id="fileInput" accept="image/*" multiple class="hidden">
    <div id="upload-progress" class="hidden mt-4 space-y-2"></div>
</div>

<style>
    #dropzone.dragover { border-color: #3b82f6; background: #eff6ff; }
    #dropzone.uploading { pointer-events: none; opacity: 0.6; }
    .progress-bar { height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden; }
    .progress-bar div { height: 100%; background: #3b82f6; width: 0%; transition: width .3s; }
</style>
<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');

    ['dragenter','dragover'].forEach(e => { dropzone.addEventListener(e, ev => { ev.preventDefault(); ev.dataTransfer.dropEffect = 'copy'; dropzone.classList.add('dragover'); }); });
    ['dragleave','drop'].forEach(e => { dropzone.addEventListener(e, ev => { ev.preventDefault(); dropzone.classList.remove('dragover'); }); });

    dropzone.addEventListener('drop', ev => uploadFiles(ev.dataTransfer.files));
    dropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => { if (fileInput.files.length) uploadFiles(fileInput.files); });

    async function uploadFiles(files) {
        const progress = document.getElementById('upload-progress');
        progress.classList.remove('hidden');
        progress.innerHTML = '';
        dropzone.classList.add('uploading');

        for (const file of files) {
            if (!file.type.startsWith('image/')) continue;
            const row = document.createElement('div');
            row.className = 'text-sm text-gray-600';
            row.innerHTML = `<div class="flex justify-between mb-1"><span>${file.name}</span><span class="text-xs">0%</span></div><div class="progress-bar"><div></div></div>`;
            progress.appendChild(row);
            const pct = row.querySelector('span:last-child');
            const bar = row.querySelector('.progress-bar div');

            const form = new FormData();
            form.append('file', file);
            try {
                const resp = await fetch('{{ url("/admin/media/upload") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: form,
                });
                bar.style.width = '100%';
                pct.textContent = '100%';
                if (!resp.ok) { row.style.color = '#ef4444'; pct.textContent = 'Failed'; }
            } catch (err) {
                row.style.color = '#ef4444';
                pct.textContent = 'Error';
            }
        }

        dropzone.classList.remove('uploading');
        setTimeout(() => location.reload(), 1000);
    }
</script>