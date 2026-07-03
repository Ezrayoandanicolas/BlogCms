<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <details class="group">
        <summary class="font-semibold text-gray-800 mb-2 cursor-pointer flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            Paste JSON
        </summary>
        <div class="mt-3 space-y-3">
            <textarea id="json-input" rows="6" placeholder='{"title": "...", "content": "<h2>...", "excerpt": "...", "status": "published", "category_id": 1}'
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow"></textarea>
            <div class="flex items-center gap-2">
                <button type="button" onclick="applyJson()" class="inline-flex items-center gap-1.5 bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-900 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Apply JSON
                </button>
                <span id="json-status" class="text-xs text-gray-400"></span>
            </div>
        </div>
    </details>
</div>

<script>
function applyJson() {
    const raw = document.getElementById('json-input').value.trim();
    const status = document.getElementById('json-status');
    if (!raw) { status.textContent = 'Paste JSON dulu.'; status.className = 'text-xs text-red-500'; return; }
    let data;
    try { data = JSON.parse(raw); } catch (e) { status.textContent = 'JSON tidak valid: ' + e.message; status.className = 'text-xs text-red-500'; return; }
    if (typeof data !== 'object' || data === null) { status.textContent = 'JSON harus object.'; status.className = 'text-xs text-red-500'; return; }

    const fields = ['title','slug','content','excerpt','featured_image','seo_title','seo_description','seo_keywords'];
    let filled = 0;
    for (const key of fields) {
        if (data[key] !== undefined) {
            const el = document.querySelector(`[name="${key}"]`);
            if (el) { el.value = data[key]; filled++; }
        }
    }

    if (data.status !== undefined) {
        const el = document.querySelector('select[name="status"]');
        if (el && [...el.options].some(o => o.value === data.status)) { el.value = data.status; filled++; }
    }
    if (data.category_id !== undefined) {
        const el = document.querySelector('select[name="category_id"]');
        if (el && [...el.options].some(o => String(o.value) === String(data.category_id))) { el.value = data.category_id; filled++; }
    }

    status.textContent = `✅ ${filled} field terisi.`;
    status.className = 'text-xs text-green-600';
}
</script>