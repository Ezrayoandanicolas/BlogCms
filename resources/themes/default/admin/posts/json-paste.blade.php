<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    <details class="group">
        <summary class="font-semibold text-gray-800 mb-2 cursor-pointer flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            AI Tools
        </summary>
        <div class="mt-3 space-y-4">
            @if($siteTopic ?? false)
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-1">Niche Topic</p>
                <p class="text-sm font-medium text-gray-800">{{ $siteTopic }}</p>
            </div>
            @endif

            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-1.5">AI Prompt — copy ke ChatGPT/Claude</p>
                <textarea id="ai-prompt" rows="8" readonly
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow select-all">Buat artikel blog dalam bahasa Indonesia tentang **{{ $siteTopic ?? 'topik yang kamu pilih' }}**.

Kembalikan JSON SAJA (tanpa markdown, tanpa penjelasan):
            {
  "title": "Judul click-worthy",
  "content": "<h2>Sub judul 1</h2><p>paragraf...</p><h2>Sub judul 2</h2><p>paragraf...</p>",
  "excerpt": "Ringkasan 2-3 kalimat",
  "status": "published",
  "category_name": "nama kategori",
  "tags": ["tag1", "tag2"],
  "seo_title": "SEO title",
  "seo_description": "Meta description",
  "seo_keywords": "kata kunci"
}

Aturan:
- Konten minimal 500 kata, struktur HTML rapi dengan &lt;h2&gt; dan &lt;p&gt;
- Bahasa Indonesia natural, informatif
- Kategori dan tags relevan dengan niche
- Jangan gunakan judul generik atau clickbait</textarea>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="copyPrompt()" class="inline-flex items-center gap-1.5 bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-900 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Copy Prompt
                </button>
                <span id="copy-status" class="text-xs text-gray-400"></span>
            </div>

            <hr class="border-gray-200">

            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-1.5">Paste JSON hasil AI di sini</p>
                <textarea id="json-input" rows="5" placeholder='{"title": "...", "content": "<h2>...", ...}'
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow"></textarea>
                <div class="flex items-center gap-2 mt-2">
                    <button type="button" onclick="applyJson()" class="inline-flex items-center gap-1.5 bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-900 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Apply JSON
                    </button>
                    <span id="json-status" class="text-xs text-gray-400"></span>
                </div>
            </div>
        </div>
    </details>
</div>

<script>
function copyPrompt() {
    const el = document.getElementById('ai-prompt');
    el.select();
    navigator.clipboard.writeText(el.value).then(() => {
        document.getElementById('copy-status').textContent = '✅ Copied!';
        document.getElementById('copy-status').className = 'text-xs text-green-600';
    });
}

function applyJson() {
    const raw = document.getElementById('json-input').value;
    const status = document.getElementById('json-status');
    if (!raw.trim()) { status.textContent = 'Paste JSON dulu.'; status.className = 'text-xs text-red-500'; return; }
    let data;
    try {
        // coba parse langsung dulu (AI biasanya output valid JSON)
        data = JSON.parse(raw.trim().replace(/^\ufeff/, ''));
    } catch (e1) {
        try {
            // gagal: coba extract {...} dari teks, lalu minify (buang newline/tab)
            const start = raw.indexOf('{'), end = raw.lastIndexOf('}');
            const jsonStr = start !== -1 && end !== -1 ? raw.substring(start, end + 1) : raw;
            const minified = jsonStr.replace(/\r?\n/g, ' ').replace(/\t/g, ' ').replace(/\s+/g, ' ');
            data = JSON.parse(minified);
        } catch (e2) {
            status.textContent = 'JSON tidak valid: ' + e1.message;
            status.className = 'text-xs text-red-500';
            return;
        }
    }
    if (typeof data !== 'object' || data === null) { status.textContent = 'JSON harus object.'; status.className = 'text-xs text-red-500'; return; }

    async function applyAll() {
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
        } else if (data.category_name) {
            const el = document.querySelector('select[name="category_id"]');
            if (el) {
                const match = [...el.options].find(o => o.text.toLowerCase() === data.category_name.toLowerCase());
                if (match) { el.value = match.value; filled++; }
                else {
                    status.textContent = '⏳ Membuat kategori...';
                    status.className = 'text-xs text-blue-500';
                    try {
                        const resp = await fetch('/admin/categories', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: new URLSearchParams({ name: data.category_name }),
                        });
                        if (resp.status === 302) { location.reload(); return; }
                        const json = await resp.json();
                        if (resp.ok && json.id) { el.value = json.id; filled++; }
                        else { throw new Error(json.message || 'Gagal'); }
                    } catch (e) {
                        status.textContent = '❌ ' + e.message;
                        status.className = 'text-xs text-red-500';
                        return;
                    }
                }
            }
        }

        status.textContent = `✅ ${filled} field terisi.`;
        status.className = 'text-xs text-green-600';
    }
    applyAll();
}
</script>