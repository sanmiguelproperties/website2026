@extends('layouts.app')

@section('title', 'CMS - Gestionar Menús')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">🧭 Menús de Navegación</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona los menús del header y footer del sitio (bilingüe ES/EN)</p>
    </div>
  </div>

  <div id="menus-list" class="space-y-4"></div>
  <div id="menus-loading" class="hidden py-10"><div class="animate-pulse space-y-3"><div class="h-24 rounded-2xl bg-[var(--c-elev)]"></div></div></div>
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

  async function loadMenus() {
    $('#menus-loading').classList.remove('hidden');
    try {
      const res = await api(`${API}/cms/menus`);
      const menus = res?.data || [];
      const container = $('#menus-list');

      container.innerHTML = menus.map(m => `
        <div class="rounded-2xl border border-[var(--c-border)] bg-[var(--c-surface)] overflow-hidden">
          <div class="px-5 py-3 border-b border-[var(--c-border)] bg-[var(--c-elev)] flex items-center justify-between">
            <div>
              <h3 class="font-semibold text-[var(--c-text)]">🧭 ${esc(m.name)}</h3>
              <p class="text-xs text-[var(--c-muted)]">Slug: ${esc(m.slug)} • Ubicación: ${esc(m.location)}</p>
            </div>
            <span class="px-2 py-1 rounded-full text-xs ${m.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800'}">${m.is_active ? 'Activo' : 'Inactivo'}</span>
          </div>
          <div class="p-5">
            <table class="min-w-full text-sm">
              <thead><tr class="text-left text-xs text-[var(--c-muted)]"><th class="py-1 pr-3">Orden</th><th class="py-1 pr-3">Label ES</th><th class="py-1 pr-3">Label EN</th><th class="py-1 pr-3">URL / Ruta</th><th class="py-1">Activo</th></tr></thead>
              <tbody class="divide-y divide-[var(--c-border)]">
                ${(m.root_items || []).map((item, i) => `
                  <tr>
                    <td class="py-2 pr-3 text-[var(--c-muted)]">${i + 1}</td>
                    <td class="py-2 pr-3 font-medium text-[var(--c-text)]">${esc(item.label_es)}</td>
                    <td class="py-2 pr-3 text-[var(--c-muted)]">${esc(item.label_en || '—')}</td>
                    <td class="py-2 pr-3 text-[var(--c-muted)] text-xs">${esc(item.route_name || item.url || '—')}</td>
                    <td class="py-2">${item.is_active ? '✅' : '❌'}</td>
                  </tr>
                  ${(item.children || []).map((child, j) => `
                    <tr class="bg-[var(--c-elev)]/50">
                      <td class="py-1.5 pr-3 pl-6 text-[var(--c-muted)]">${i + 1}.${j + 1}</td>
                      <td class="py-1.5 pr-3 text-[var(--c-text)] text-xs">↳ ${esc(child.label_es)}</td>
                      <td class="py-1.5 pr-3 text-[var(--c-muted)] text-xs">${esc(child.label_en || '—')}</td>
                      <td class="py-1.5 pr-3 text-[var(--c-muted)] text-xs">${esc(child.route_name || child.url || '—')}</td>
                      <td class="py-1.5">${child.is_active ? '✅' : '❌'}</td>
                    </tr>
                  `).join('')}
                `).join('')}
              </tbody>
            </table>
            ${!(m.root_items || []).length ? '<p class="text-center py-4 text-sm text-[var(--c-muted)]">Este menú no tiene items.</p>' : ''}
          </div>
        </div>
      `).join('');
    } finally {
      $('#menus-loading').classList.add('hidden');
    }
  }

  loadMenus();
});
</script>
@endsection
