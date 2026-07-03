<div id="content-dropzone"
     class="border-2 border-dashed border-gray-300 rounded-xl p-6 mb-2 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/30 transition-all">
    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    <p class="text-sm text-gray-500 font-medium">Drag & drop image here</p>
    <p class="text-xs text-gray-400 mt-0.5">or <span class="text-blue-600 underline">browse</span></p>
    <input type="file" id="imgUpload" accept="image/*" class="hidden">
    <div id="content-upload-progress" class="hidden mt-3"></div>
</div>

:root { --dz-primary: #3b82f6; --dz-bg: #eff6ff; }
#content-dropzone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 8px;
    text-align: center;
    cursor: pointer;
    transition: all .25s;
    position: relative;
}
#content-dropzone:hover { border-color: var(--dz-primary); background: var(--dz-bg); }
#content-dropzone.dragover {
    border-color: var(--dz-primary);
    background: var(--dz-bg);
    transform: scale(1.02);
    box-shadow: 0 0 0 4px rgba(59,130,246,0.15), 0 4px 12px rgba(59,130,246,0.1);
}
#content-dropzone.dragover svg { color: var(--dz-primary); transform: scale(1.1); }
#content-dropzone.dragover p { color: #1e40af; }
#content-dropzone svg { transition: transform .25s; }
#content-dropzone.uploading { pointer-events: none; opacity: .6; }
<script>
    const dropzone = document.getElementById('content-dropzone');
    const input = document.getElementById('imgUpload');
    let dragCounter = 0;

    dropzone.addEventListener('dragenter', ev => { ev.preventDefault(); dragCounter++; if (dragCounter === 1) dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragover', ev => { ev.preventDefault(); ev.dataTransfer.dropEffect = 'copy'; });
    dropzone.addEventListener('dragleave', ev => { ev.preventDefault(); dragCounter--; if (dragCounter === 0) dropzone.classList.remove('dragover'); });
    dropzone.addEventListener('drop', ev => { ev.preventDefault(); dragCounter = 0; dropzone.classList.remove('dragover'); if (ev.dataTransfer.files[0]) uploadImage(ev.dataTransfer.files[0]); });

    dropzone.addEventListener('click', () => input.click());
    input.addEventListener('change', () => { if (input.files[0]) uploadImage(input.files[0]); input.value = ''; });

    async function uploadImage(file) {
        if (!file.type.startsWith('image/')) return;
        const progress = document.getElementById('content-upload-progress');
        progress.classList.remove('hidden');
        progress.innerHTML = `<div class="text-sm text-gray-600">${file.name}</div><div class="mt-1 progress-bar" style="height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden"><div style="height:100%;background:#3b82f6;width:0%;transition:width .3s"></div></div>`;
        dropzone.classList.add('uploading');
        const bar = progress.querySelector('.progress-bar div');
        const form = new FormData();
        form.append('file', file);
        try {
            const resp = await fetch('/admin/media/upload', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: form,
            });
            bar.style.width = '100%';
            if (!resp.ok) { progress.innerHTML += '<div class="text-xs text-red-500 mt-1">Failed</div>'; return; }
            const data = await resp.json();
            const url = data.data?.url || data.url;
            const ta = document.querySelector('textarea[name="content"]');
            const img = `<img src="${url}" alt="">`;
            const start = ta.selectionStart, end = ta.selectionEnd;
            ta.value = ta.value.substring(0, start) + img + ta.value.substring(end);
            ta.selectionStart = ta.selectionEnd = start + img.length;
            ta.focus();
            progress.innerHTML = '<div class="text-xs text-green-600">✅ Image inserted</div>';
        } catch (err) {
            progress.innerHTML = `<div class="text-xs text-red-500">❌ ${err.message}</div>`;
        }
        dropzone.classList.remove('uploading');
    }
</script>