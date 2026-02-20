@extends('layouts.app')

@section('title', 'CMS - Gestionar Posts')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">📝 Blog / Posts</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona las publicaciones del blog (bilingüe ES/EN)</p>
    </div>
    <button id="btn-create" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition shadow-soft">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
      Nuevo post
    </button>
  </div>

  <div id="posts-list" class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
    <div class="p-5">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-[var(--c-muted)]">
              <th class="py-2 pr-3">Título</th>
              <th class="py-2 pr-3">Slug</th>
              <th class="py-2 pr-3">Estado</th>
              <th class="py-2 pr-3">Destacado</th>
              <th class="py-2 pr-3">Publicado</th>
              <th class="py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody id="posts-tbody" class="divide-y divide-[var(--c-border)]"></tbody>
        </table>
      </div>
      <div id="posts-empty" class="hidden text-center py-12">
        <p class="text-[var(--c-text)] font-medium">No hay posts aún</p>
        <p class="text-sm text-[var(--c-muted)]">Crea tu primera publicación del blog.</p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API = '/api';
  const TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
  if (!TOKEN) return;

  const $ = (s) => document.querySelector(s);
  const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

  async function api(url) {
    const res = await fetch(url, { headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${TOKEN}` } });
    return res.json();
  }

  async function loadPosts() {
    const res = await api(`${API}/cms/posts?per_page=50`);
    const posts = res?.data?.data || res?.data || [];
    const tbody = $('#posts-tbody');
    const empty = $('#posts-empty');

    if (!posts.length) { empty.classList.remove('hidden'); tbody.innerHTML = ''; return; }
    empty.classList.add('hidden');

    tbody.innerHTML = posts.map(p => `
      <tr class="hover:bg-[var(--c-elev)]/50 transition">
        <td class="py-3 pr-3 font-medium text-[var(--c-text)]">${esc(p.title_es)}</td>
        <td class="py-3 pr-3 text-[var(--c-muted)]">/${esc(p.slug)}</td>
        <td class="py-3 pr-3"><span class="px-2 py-1 rounded-full text-xs ${p.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800'}">${esc(p.status)}</span></td>
        <td class="py-3 pr-3">${p.is_featured ? '⭐' : '—'}</td>
        <td class="py-3 pr-3 text-[var(--c-muted)] text-xs">${p.published_at ? new Date(p.published_at).toLocaleDateString() : '—'}</td>
        <td class="py-3 text-right text-[var(--c-muted)]">—</td>
      </tr>
    `).join('');
  }

  loadPosts();
});
</script>
@endsection
