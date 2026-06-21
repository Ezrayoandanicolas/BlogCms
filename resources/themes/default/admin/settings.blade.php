@extends('theme::default.admin.layouts.app')

@section('title', 'Settings')

@section('content')
    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-1">Settings</h1>
    <p class="text-sm text-gray-500 mb-6">Site configuration per domain</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-1">Current Site Settings</h3>
            @php $editingDomain = $domainThemes->firstWhere('id', config('app.domain_id')); @endphp
            @if($editingDomain)
                <p class="text-xs text-gray-400 mb-4">Editing: <span class="font-medium text-gray-600">{{ $editingDomain->domain }}</span></p>
            @endif
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Site Name</label>
                    <input type="text" name="site_name" value="{{ $siteSettings['site_name'] }}" required
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                </div>
                <div class="mb-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Site Description</label>
                    <textarea name="site_description" rows="2"
                              class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow resize-none">{{ $siteSettings['site_description'] }}</textarea>
                </div>
                <div class="mb-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Niche / Topik Website</label>
                    <input type="text" name="site_topic" value="{{ $siteSettings['site_topic'] }}"
                           placeholder="e.g. Teknologi Blockchain, Digital Marketing"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">Save Settings</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">New Domain Mapping</h3>
            <form action="{{ url('/admin/domain-themes') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Domain</label>
                        <input type="text" name="domain" placeholder="example.com" required
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Theme</label>
                        <select name="theme_slug" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            @foreach($themes as $theme)
                                <option value="{{ $theme->slug }}">{{ $theme->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">Add Domain</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[500px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Domain</th>
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Theme</th>
                        <th class="text-center px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Active</th>
                        <th class="text-right px-5 py-3.5 font-semibold text-gray-600 text-xs uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($domainThemes as $dt)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $dt->domain }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $dt->theme_slug }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dt->active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dt->active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $dt->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex gap-1 items-center justify-end">
                                    <form action="{{ url('/admin/domain-themes/' . $dt->id) }}" method="POST" class="inline-flex gap-1.5 items-center flex-wrap justify-end">
                                        @csrf @method('PUT')
                                        <input type="text" name="domain" value="{{ $dt->domain }}" class="border border-gray-200 rounded-lg px-2 py-1 text-xs w-28 focus:ring-2 focus:ring-blue-500 outline-none">
                                        <select name="theme_slug" class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                                            @foreach($themes as $theme)
                                                <option value="{{ $theme->slug }}" {{ $dt->theme_slug == $theme->slug ? 'selected' : '' }}>{{ $theme->slug }}</option>
                                            @endforeach
                                        </select>
                                        <label class="flex items-center gap-1 text-xs text-gray-500">
                                            <input type="checkbox" name="active" value="1" {{ $dt->active ? 'checked' : '' }} class="rounded">
                                            Active
                                        </label>
                                        <button type="submit" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Save</button>
                                    </form>
                                    <button type="button" onclick="openSettingsModal({{ $dt->id }}, '{{ $dt->domain }}')"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                                            title="Edit Site Settings">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <form action="{{ url('/admin/domain-themes/' . $dt->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this domain mapping?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($domainThemes->isEmpty())
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                <p class="text-sm font-medium">No domain mappings yet</p>
                                <p class="text-xs mt-1">Add your first domain to get started with multi-tenant.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div id="settingsModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeSettingsModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Site Settings - <span id="modalDomainName" class="text-blue-600"></span></h3>
                    <button type="button" onclick="closeSettingsModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="modalSettingsForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Site Name</label>
                        <input type="text" name="site_name" id="modalSiteName" required
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow">
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Site Description</label>
                        <textarea name="site_description" id="modalSiteDesc" rows="2"
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow resize-none"></textarea>
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Niche / Topik</label>
                        <input type="text" name="site_topic" id="modalSiteTopic"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-shadow"
                               placeholder="e.g. Teknologi Blockchain">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">Save</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const siteSettings = @json($siteSettings);
        const domainThemes = @json($domainThemes);

        function openSettingsModal(domainId, domainName) {
            document.getElementById('modalDomainName').textContent = domainName;
            document.getElementById('modalSettingsForm').action = '/admin/settings/domain/' + domainId;
            document.getElementById('settingsModal').classList.remove('hidden');

            const dt = domainThemes.find(d => d.id === domainId);
            if (dt) {
                    fetch('/admin/settings/domain/' + domainId + '/data')
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('modalSiteName').value = data.site_name || '{{ config("app.name") }}';
                            document.getElementById('modalSiteDesc').value = data.site_description || '';
                            document.getElementById('modalSiteTopic').value = data.site_topic || '';
                        })
                        .catch(() => {
                            document.getElementById('modalSiteName').value = '{{ config("app.name") }}';
                            document.getElementById('modalSiteDesc').value = '';
                            document.getElementById('modalSiteTopic').value = '';
                        });
            }
        }

        function closeSettingsModal() {
            document.getElementById('settingsModal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSettingsModal();
        });
    </script>
@endsection
