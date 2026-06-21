@extends('theme::clean.admin.layouts.app')

@section('title', 'API Docs')

@section('content')
<div x-data="apiDocs()" class="max-w-5xl">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-1">API Documentation</h1>
    <p class="text-sm text-gray-500 mb-6">Test and explore the API endpoints</p>

    @if($newToken ?? session('newToken'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl mb-6 text-sm">
            <strong>API Token</strong> — Copy this now, you won't see it again.<br>
            <code class="break-all text-xs bg-amber-100 px-2 py-1 rounded mt-2 block select-all font-mono">{{ $newToken ?? session('newToken') }}</code>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-3">API Token</h3>
            <p class="text-xs text-gray-500 mb-3">Used by your AI generator script to authenticate API requests.</p>
            <form action="{{ url('/admin/api-docs/regenerate') }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Regenerate token? Old token will stop working.')"
                        class="bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-600 transition-colors shadow-sm">
                    Regenerate Token
                </button>
            </form>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-3">API Tester</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Base URL</label>
                    <input type="text" x-model="baseUrl" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Bearer Token</label>
                    <input type="text" x-model="bearerToken" placeholder="paste token here"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none font-mono">
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4 flex gap-2 flex-wrap border-b border-gray-200 pb-2">
        <template x-for="g in groups" :key="g.key">
            <button @click="activeGroup = g.key"
                    :class="activeGroup === g.key ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors"
                    x-text="g.label"></button>
        </template>
    </div>

    <template x-for="g in groups" :key="g.key">
        <div x-show="activeGroup === g.key" class="space-y-4">
            <template x-for="ep in g.endpoints" :key="ep.method + ep.path">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-3 flex items-center gap-3 cursor-pointer hover:bg-gray-50"
                         @click="ep.open = !ep.open">
                        <span :class="methodClass(ep.method)"
                              class="px-2 py-0.5 rounded text-xs font-bold uppercase shrink-0"
                              x-text="ep.method"></span>
                        <code class="text-sm font-mono text-gray-800 truncate" x-text="ep.path"></code>
                        <span class="text-xs text-gray-400 ml-auto shrink-0" x-text="ep.desc"></span>
                        <svg class="w-4 h-4 text-gray-300 shrink-0 transition-transform" :class="ep.open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div x-show="ep.open" class="border-t border-gray-100">
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Request</h4>
                                    <div class="bg-white rounded-xl p-3 overflow-x-auto">
                                        <pre class="text-xs font-mono text-gray-700 whitespace-pre-wrap" x-text="ep.requestExample || '—'"></pre>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Response</h4>
                                    <div class="bg-white rounded-xl p-3 overflow-x-auto">
                                        <pre class="text-xs font-mono text-gray-700 whitespace-pre-wrap" x-text="ep.responseExample || '—'"></pre>
                                    </div>
                                </div>
                            </div>
                            <template x-if="ep.params">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Parameters</h4>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead><tr class="border-b border-gray-200"><th class="text-left py-1.5 px-2 text-gray-500 font-medium">Name</th><th class="text-left py-1.5 px-2 text-gray-500 font-medium">Type</th><th class="text-left py-1.5 px-2 text-gray-500 font-medium">Required</th><th class="text-left py-1.5 px-2 text-gray-500 font-medium">Description</th></tr></thead>
                                            <tbody>
                                                <template x-for="p in ep.params" :key="p.name">
                                                    <tr class="border-b border-gray-50"><td class="py-1.5 px-2 font-mono" x-text="p.name"></td><td class="py-1.5 px-2 text-gray-600" x-text="p.type"></td><td class="py-1.5 px-2" x-text="p.required ? 'Yes' : 'No'"></td><td class="py-1.5 px-2 text-gray-600" x-text="p.desc"></td></tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Test</h4>
                                <div class="space-y-2">
                                    <template x-if="ep.testParams">
                                        <template x-for="tp in ep.testParams" :key="tp.name">
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-0.5" x-text="tp.name + (tp.required ? ' *' : '')"></label>
                                                <template x-if="tp.type === 'file'">
                                                    <input type="file" :id="'ip-' + ep.method + '-' + ep.slug + '-' + tp.name"
                                                           class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-teal-500 outline-none">
                                                </template>
                                                <template x-if="tp.type === 'textarea'">
                                                    <textarea :id="'ip-' + ep.method + '-' + ep.slug + '-' + tp.name" rows="3"
                                                              class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-mono focus:ring-2 focus:ring-teal-500 outline-none"
                                                              :placeholder="tp.placeholder || ''"></textarea>
                                                </template>
                                                <template x-if="tp.type !== 'file' && tp.type !== 'textarea'">
                                                    <input :type="tp.type || 'text'" :id="'ip-' + ep.method + '-' + ep.slug + '-' + tp.name"
                                                           class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-teal-500 outline-none font-mono"
                                                           :placeholder="tp.placeholder || ''">
                                                </template>
                                            </div>
                                        </template>
                                    </template>
                                    <div class="flex items-center gap-3 mt-3">
                                        <button @click="testEndpoint(ep)"
                                                class="bg-teal-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium hover:bg-teal-700 transition-colors">
                                            Send Request
                                        </button>
                                        <span x-show="ep.loading" class="text-xs text-gray-400">Sending...</span>
                                    </div>
                                    <div x-show="ep.response !== null" class="mt-2">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-semibold" :class="ep.statusCode >= 200 && ep.statusCode < 300 ? 'text-emerald-600' : 'text-red-600'" x-text="ep.statusCode ? 'HTTP ' + ep.statusCode : ''"></span>
                                            <span class="text-xs text-gray-400" x-text="ep.responseTime ? ep.responseTime + 'ms' : ''"></span>
                                        </div>
                                        <div class="bg-gray-900 rounded-xl p-3 overflow-x-auto max-h-64 overflow-y-auto">
                                            <pre class="text-xs font-mono text-green-400 whitespace-pre-wrap" x-text="ep.response || 'No response'"></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>

<script>
function apiDocs() {
    const base = '{{ url('/') }}';
    const baseUrl = base.match(/^https?:\/\/[^\/]+/)[0];

    function slug(s) { return s.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }

    const groups = [
        {
            key: 'auth', label: 'Auth',
            endpoints: [
                { method: 'POST', path: '/api/v1/auth/login', desc: 'Login & get token', slug: 'auth-login',
                  params: [
                    { name: 'email', type: 'string', required: true, desc: 'Email address' },
                    { name: 'password', type: 'string', required: true, desc: 'Password' },
                  ],
                  testParams: [
                    { name: 'email', type: 'email', required: true, placeholder: 'admin@blogcms.test' },
                    { name: 'password', type: 'password', required: true, placeholder: 'password' },
                  ],
                  requestExample: 'POST /api/v1/auth/login\n{\n  "email": "admin@blogcms.test",\n  "password": "password"\n}',
                  responseExample: '{\n  "message": "Login successful",\n  "data": {\n    "user": { ... },\n    "token": "1|abc123..."\n  }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'POST', path: '/api/v1/auth/register', desc: 'Register new user', slug: 'auth-register',
                  params: [
                    { name: 'name', type: 'string', required: true, desc: 'Full name' },
                    { name: 'email', type: 'string', required: true, desc: 'Email address' },
                    { name: 'password', type: 'string', required: true, desc: 'Password (min 8 chars)' },
                  ],
                  testParams: [
                    { name: 'name', type: 'text', required: true, placeholder: 'New User' },
                    { name: 'email', type: 'email', required: true, placeholder: 'user@example.com' },
                    { name: 'password', type: 'password', required: true, placeholder: 'min 8 chars' },
                  ],
                  requestExample: 'POST /api/v1/auth/register\n{\n  "name": "New User",\n  "email": "user@example.com",\n  "password": "secret123"\n}',
                  responseExample: '{\n  "message": "Registration successful",\n  "data": {\n    "user": { ... },\n    "token": "1|abc123..."\n  }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'POST', path: '/api/v1/auth/logout', desc: 'Logout & revoke token', slug: 'auth-logout', auth: true,
                  requestExample: 'POST /api/v1/auth/logout\nAuthorization: Bearer {token}\n(no body)',
                  responseExample: '{\n  "message": "Logged out successfully"\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'GET', path: '/api/v1/auth/me', desc: 'Get current user', slug: 'auth-me', auth: true,
                  requestExample: 'GET /api/v1/auth/me\nAuthorization: Bearer {token}',
                  responseExample: '{\n  "data": {\n    "id": 1,\n    "name": "Admin",\n    "email": "admin@blogcms.test",\n    ...\n  }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
            ]
        },
        {
            key: 'posts', label: 'Posts',
            endpoints: [
                { method: 'GET', path: '/api/v1/posts', desc: 'List published posts', slug: 'posts-list',
                  requestExample: 'GET /api/v1/posts',
                  responseExample: '{\n  "data": [\n    {\n      "id": 1,\n      "title": "Post Title",\n      "slug": "post-title",\n      ...\n    }\n  ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'POST', path: '/api/v1/posts', desc: 'Create a new post', slug: 'posts-create', auth: true,
                  params: [
                    { name: 'title', type: 'string', required: true, desc: 'Post title' },
                    { name: 'content', type: 'string', required: true, desc: 'Post content (HTML)' },
                    { name: 'category_id', type: 'integer', required: false, desc: 'Category ID' },
                    { name: 'excerpt', type: 'string', required: false, desc: 'Short excerpt' },
                    { name: 'featured_image', type: 'string', required: false, desc: 'Image URL/path' },
                    { name: 'status', type: 'string', required: false, desc: 'draft or published' },
                    { name: 'seo_title', type: 'string', required: false, desc: 'SEO title' },
                    { name: 'seo_description', type: 'string', required: false, desc: 'SEO description' },
                    { name: 'tags', type: 'array', required: false, desc: 'Array of tag names or IDs (strings auto-create tags)' },
                  ],
                  testParams: [
                    { name: 'title', type: 'text', required: true, placeholder: 'My Article Title' },
                    { name: 'content', type: 'textarea', required: true, placeholder: '<h1>Article content</h1><p>...</p>' },
                    { name: 'category_id', type: 'text', required: false, placeholder: '1' },
                    { name: 'excerpt', type: 'text', required: false, placeholder: 'Brief summary...' },
                    { name: 'featured_image', type: 'text', required: false, placeholder: '/media/image.jpg' },
                    { name: 'status', type: 'text', required: false, placeholder: 'published' },
                    { name: 'seo_title', type: 'text', required: false, placeholder: 'SEO Title' },
                    { name: 'seo_description', type: 'text', required: false, placeholder: 'SEO Description' },
                    { name: 'tags', type: 'text', required: false, placeholder: '["tag1", "tag2"]' },
                  ],
                  requestExample: 'POST /api/v1/posts\n{\n  "title": "My Article",\n  "content": "<p>Hello</p>",\n  "category_id": 1,\n  "status": "published",\n  "tags": ["tech", "news"]\n}',
                  responseExample: '{\n  "message": "Post created",\n  "data": { "id": 1, "title": "My Article", ... }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'GET', path: '/api/v1/posts/{slug}', desc: 'Get post by slug', slug: 'posts-show',
                  params: [{ name: 'slug', type: 'string', required: true, desc: 'Post slug' }],
                  testParams: [{ name: 'slug', type: 'text', required: true, placeholder: 'post-slug' }],
                  requestExample: 'GET /api/v1/posts/my-post-slug',
                  responseExample: '{\n  "data": { "id": 1, "title": "My Post", ... }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'PUT', path: '/api/v1/posts/{id}', desc: 'Update a post', slug: 'posts-update', auth: true,
                  params: [
                    { name: 'id', type: 'integer', required: true, desc: 'Post ID' },
                    { name: 'title', type: 'string', required: false, desc: 'Post title' },
                    { name: 'content', type: 'string', required: false, desc: 'Post content' },
                    { name: 'status', type: 'string', required: false, desc: 'draft or published' },
                  ],
                  testParams: [
                    { name: 'id', type: 'text', required: true, placeholder: '1' },
                    { name: 'title', type: 'text', required: false, placeholder: 'Updated Title' },
                    { name: 'content', type: 'textarea', required: false, placeholder: 'Updated content...' },
                    { name: 'status', type: 'text', required: false, placeholder: 'published' },
                  ],
                  requestExample: 'PUT /api/v1/posts/1\n{\n  "title": "Updated Title",\n  "status": "published"\n}',
                  responseExample: '{\n  "message": "Post updated",\n  "data": { ... }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'DELETE', path: '/api/v1/posts/{id}', desc: 'Delete a post', slug: 'posts-delete', auth: true,
                  params: [{ name: 'id', type: 'integer', required: true, desc: 'Post ID' }],
                  testParams: [{ name: 'id', type: 'text', required: true, placeholder: '1' }],
                  requestExample: 'DELETE /api/v1/posts/1',
                  responseExample: '{\n  "message": "Post deleted"\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'GET', path: '/api/v1/posts/popular', desc: 'Get popular posts', slug: 'posts-popular',
                  requestExample: 'GET /api/v1/posts/popular',
                  responseExample: '{\n  "data": [ { "id": 1, "title": "Popular Post", ... } ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'GET', path: '/api/v1/posts/category/{slug}', desc: 'Posts by category slug', slug: 'posts-category',
                  testParams: [{ name: 'slug', type: 'text', required: true, placeholder: 'category-slug' }],
                  requestExample: 'GET /api/v1/posts/category/tech',
                  responseExample: '{\n  "data": [ ... ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'GET', path: '/api/v1/posts/tag/{slug}', desc: 'Posts by tag slug', slug: 'posts-tag',
                  testParams: [{ name: 'slug', type: 'text', required: true, placeholder: 'tag-slug' }],
                  requestExample: 'GET /api/v1/posts/tag/javascript',
                  responseExample: '{\n  "data": [ ... ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'GET', path: '/api/v1/posts/search?q=', desc: 'Search posts', slug: 'posts-search',
                  testParams: [{ name: 'q', type: 'text', required: true, placeholder: 'search keyword' }],
                  requestExample: 'GET /api/v1/posts/search?q=laravel',
                  responseExample: '{\n  "data": [ ... ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
            ]
        },
        {
            key: 'categories', label: 'Categories',
            endpoints: [
                { method: 'GET', path: '/api/v1/categories', desc: 'List all categories', slug: 'cats-list',
                  requestExample: 'GET /api/v1/categories',
                  responseExample: '{\n  "data": [\n    { "id": 1, "name": "Tech", "slug": "tech", ... }\n  ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'POST', path: '/api/v1/categories', desc: 'Create category', slug: 'cats-create', auth: true,
                  params: [
                    { name: 'name', type: 'string', required: true, desc: 'Category name' },
                    { name: 'slug', type: 'string', required: false, desc: 'Auto-generated if empty' },
                    { name: 'description', type: 'string', required: false, desc: 'Description' },
                  ],
                  testParams: [
                    { name: 'name', type: 'text', required: true, placeholder: 'Technology' },
                    { name: 'slug', type: 'text', required: false, placeholder: 'technology' },
                    { name: 'description', type: 'text', required: false, placeholder: 'Tech articles' },
                  ],
                  requestExample: 'POST /api/v1/categories\n{\n  "name": "Technology"\n}',
                  responseExample: '{\n  "message": "Category created",\n  "data": { "id": 1, "name": "Technology", ... }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'PUT', path: '/api/v1/categories/{id}', desc: 'Update category', slug: 'cats-update', auth: true,
                  params: [
                    { name: 'id', type: 'integer', required: true, desc: 'Category ID' },
                    { name: 'name', type: 'string', required: false, desc: 'Category name' },
                    { name: 'slug', type: 'string', required: false, desc: 'Slug' },
                  ],
                  testParams: [
                    { name: 'id', type: 'text', required: true, placeholder: '1' },
                    { name: 'name', type: 'text', required: false, placeholder: 'Updated Category' },
                    { name: 'slug', type: 'text', required: false, placeholder: 'updated-category' },
                  ],
                  requestExample: 'PUT /api/v1/categories/1\n{\n  "name": "Updated Category"\n}',
                  responseExample: '{\n  "message": "Category updated",\n  "data": { ... }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'DELETE', path: '/api/v1/categories/{id}', desc: 'Delete category', slug: 'cats-delete', auth: true,
                  params: [{ name: 'id', type: 'integer', required: true, desc: 'Category ID' }],
                  testParams: [{ name: 'id', type: 'text', required: true, placeholder: '1' }],
                  requestExample: 'DELETE /api/v1/categories/1',
                  responseExample: '{\n  "message": "Category deleted"\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
            ]
        },
        {
            key: 'tags', label: 'Tags',
            endpoints: [
                { method: 'GET', path: '/api/v1/tags', desc: 'List all tags', slug: 'tags-list',
                  requestExample: 'GET /api/v1/tags',
                  responseExample: '{\n  "data": [\n    { "id": 1, "name": "PHP", "slug": "php" }\n  ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'POST', path: '/api/v1/tags', desc: 'Create tag', slug: 'tags-create', auth: true,
                  params: [
                    { name: 'name', type: 'string', required: true, desc: 'Tag name' },
                    { name: 'slug', type: 'string', required: false, desc: 'Auto-generated if empty' },
                  ],
                  testParams: [
                    { name: 'name', type: 'text', required: true, placeholder: 'Laravel' },
                    { name: 'slug', type: 'text', required: false, placeholder: 'laravel' },
                  ],
                  requestExample: 'POST /api/v1/tags\n{\n  "name": "Laravel"\n}',
                  responseExample: '{\n  "message": "Tag created",\n  "data": { "id": 1, "name": "Laravel", ... }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'PUT', path: '/api/v1/tags/{id}', desc: 'Update tag', slug: 'tags-update', auth: true,
                  params: [
                    { name: 'id', type: 'integer', required: true, desc: 'Tag ID' },
                    { name: 'name', type: 'string', required: false, desc: 'Tag name' },
                  ],
                  testParams: [
                    { name: 'id', type: 'text', required: true, placeholder: '1' },
                    { name: 'name', type: 'text', required: false, placeholder: 'Updated Tag' },
                  ],
                  requestExample: 'PUT /api/v1/tags/1\n{\n  "name": "Updated Tag"\n}',
                  responseExample: '{\n  "message": "Tag updated",\n  "data": { ... }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'DELETE', path: '/api/v1/tags/{id}', desc: 'Delete tag', slug: 'tags-delete', auth: true,
                  params: [{ name: 'id', type: 'integer', required: true, desc: 'Tag ID' }],
                  testParams: [{ name: 'id', type: 'text', required: true, placeholder: '1' }],
                  requestExample: 'DELETE /api/v1/tags/1',
                  responseExample: '{\n  "message": "Tag deleted"\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
            ]
        },
        {
            key: 'media', label: 'Media',
            endpoints: [
                { method: 'GET', path: '/api/v1/media', desc: 'List all uploaded media', slug: 'media-list', auth: true,
                  requestExample: 'GET /api/v1/media\nAuthorization: Bearer {token}',
                  responseExample: '{\n  "data": [\n    { "path": "media/image.jpg", "url": "...", "size": 12345, "mime": "image/jpeg" }\n  ]\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'POST', path: '/api/v1/media/upload', desc: 'Upload an image', slug: 'media-upload', auth: true,
                  params: [
                    { name: 'file', type: 'file', required: true, desc: 'Image file (jpeg, png, jpg, gif, svg, webp)' },
                    { name: 'folder', type: 'string', required: false, desc: 'Subfolder (default: media)' },
                  ],
                  testParams: [
                    { name: 'file', type: 'file', required: true },
                    { name: 'folder', type: 'text', required: false, placeholder: 'media' },
                  ],
                  requestExample: 'POST /api/v1/media/upload\nContent-Type: multipart/form-data\nfile: <binary>',
                  responseExample: '{\n  "message": "File uploaded",\n  "data": { "path": "media/image.jpg", "url": "/storage/media/image.jpg" }\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
                { method: 'POST', path: '/api/v1/media/delete', desc: 'Delete a media file', slug: 'media-delete', auth: true,
                  params: [{ name: 'path', type: 'string', required: true, desc: 'File path to delete' }],
                  testParams: [{ name: 'path', type: 'text', required: true, placeholder: 'media/image.jpg' }],
                  requestExample: 'POST /api/v1/media/delete\n{\n  "path": "media/image.jpg"\n}',
                  responseExample: '{\n  "message": "File deleted"\n}',
                  open: false, loading: false, response: null, statusCode: null, responseTime: null,
                },
            ]
        },
    ];

    return {
        baseUrl: baseUrl,
        bearerToken: '',
        activeGroup: 'posts',
        groups: groups,

        methodClass(m) {
            const map = { GET: 'bg-emerald-100 text-emerald-700', POST: 'bg-teal-100 text-teal-700', PUT: 'bg-amber-100 text-amber-700', DELETE: 'bg-red-100 text-red-700' };
            return map[m] || 'bg-gray-100 text-gray-700';
        },

        async testEndpoint(ep) {
            ep.loading = true;
            ep.response = null;
            ep.statusCode = null;
            ep.responseTime = null;

            let url = this.baseUrl + ep.path;
            let method = ep.method;
            let headers = { 'Accept': 'application/json' };
            let body = null;

            // Replace path params with actual values
            if (ep.testParams) {
                ep.testParams.forEach(tp => {
                    const el = document.getElementById('ip-' + ep.method + '-' + ep.slug + '-' + tp.name);
                    if (el) {
                        let val = el.type === 'file' ? el.files[0] : el.value;
                        if (tp.type === 'file') {
                            // Handle file upload via FormData
                            const fd = new FormData();
                            if (val) fd.append(tp.name, val);
                            // Add other non-file params
                            ep.testParams.forEach(other => {
                                const otherEl = document.getElementById('ip-' + ep.method + '-' + ep.slug + '-' + other.name);
                                if (otherEl && other.type !== 'file' && otherEl.value) {
                                    fd.append(other.name, otherEl.value);
                                }
                            });
                            body = fd;
                            // Don't set Content-Type - let browser set multipart boundary
                            ep._isFormData = true;
                            return;
                        }
                        if (val) {
                            url = url.replace('{' + tp.name + '}', encodeURIComponent(val));
                        }
                    }
                });
            }

            // Build JSON body for non-file requests
            if (!ep._isFormData && ep.testParams) {
                const data = {};
                ep.testParams.forEach(tp => {
                    const el = document.getElementById('ip-' + ep.method + '-' + ep.slug + '-' + tp.name);
                    if (el && el.value && !url.includes('{' + tp.name + '}') && tp.type !== 'file') {
                        // Check if it's a query param
                        if (ep.path.includes('?')) {
                            // It's a query param, don't add to body
                        } else {
                            data[tp.name] = el.value;
                        }
                    }
                });
                if (Object.keys(data).length > 0) {
                    headers['Content-Type'] = 'application/json';
                    body = JSON.stringify(data);
                }
            }

            if (this.bearerToken) {
                headers['Authorization'] = 'Bearer ' + this.bearerToken;
            }

            const start = performance.now();
            try {
                const res = await fetch(url, { method, headers, body });
                const elapsed = Math.round(performance.now() - start);
                ep.statusCode = res.status;
                ep.responseTime = elapsed;
                const text = await res.text();
                try {
                    ep.response = JSON.stringify(JSON.parse(text), null, 2);
                } catch {
                    ep.response = text;
                }
            } catch (e) {
                ep.response = 'Error: ' + e.message;
                ep.statusCode = 0;
                ep.responseTime = null;
            }
            ep.loading = false;
            ep._isFormData = false;
        },
    };
}
</script>
@endsection
