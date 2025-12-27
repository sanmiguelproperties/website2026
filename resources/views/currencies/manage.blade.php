@extends('layouts.app')

@section('title', 'Administrar Monedas')

@section('content')
<div class="">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-[var(--c-text)]">Administrar Monedas</h1>
      <p class="text-[var(--c-muted)] mt-1">Gestiona las monedas del sistema</p>
    </div>
    <div class="flex gap-3">
      <button id="btn-create-currency" class="inline-flex items-center gap-2 px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-xl hover:opacity-95 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Nueva Moneda
      </button>
    </div>
  </div>

  <!-- Search and Filters -->
  <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-[var(--c-text)]">Monedas del Sistema</h2>
      <div class="flex items-center gap-2">
        <input type="text" id="search-currencies" placeholder="Buscar monedas..." class="px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg text-sm">
        <button id="btn-refresh-currencies" class="p-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg hover:bg-[var(--c-surface)] transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Currencies List -->
    <div id="currencies-list" class="space-y-3">
      <!-- Currencies will be loaded here -->
    </div>

    <!-- Pagination -->
    <div id="currencies-pagination" class="flex justify-between items-center mt-6">
      <!-- Pagination will be loaded here -->
    </div>
  </div>
</div>

<!-- Create/Edit Currency Modal -->
<div id="currency-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="relative mx-auto mt-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="bg-[var(--c-surface)] rounded-2xl border border-[var(--c-border)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--c-border)]">
        <h3 id="currency-modal-title" class="text-lg font-semibold text-[var(--c-text)]">Crear Moneda</h3>
      </div>
      <form id="currency-form" class="p-6">
        <input type="hidden" id="currency-id" name="id">

        <!-- Name -->
        <div>
          <label for="currency-name" class="block text-sm font-medium text-[var(--c-text)] mb-1">Nombre</label>
          <input type="text" id="currency-name" name="name" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
        </div>

        <!-- Code -->
        <div>
          <label for="currency-code" class="block text-sm font-medium text-[var(--c-text)] mb-1">Código</label>
          <input type="text" id="currency-code" name="code" maxlength="3" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent uppercase" required>
          <p class="text-xs text-[var(--c-muted)] mt-1">Código de 3 letras (ej: USD, EUR, PEN)</p>
        </div>

        <!-- Symbol -->
        <div>
          <label for="currency-symbol" class="block text-sm font-medium text-[var(--c-text)] mb-1">Símbolo</label>
          <input type="text" id="currency-symbol" name="symbol" maxlength="10" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required>
          <p class="text-xs text-[var(--c-muted)] mt-1">Símbolo de la moneda (ej: $, €, S/)</p>
        </div>

        <!-- Exchange Rate -->
        <div>
          <label for="currency-exchange-rate" class="block text-sm font-medium text-[var(--c-text)] mb-1">Tipo de Cambio</label>
          <input type="number" id="currency-exchange-rate" name="exchange_rate" step="0.01" min="0" max="999999.99" class="w-full px-3 py-2 bg-[var(--c-elev)] border border-[var(--c-border)] rounded-lg focus:ring-2 focus:ring-[var(--c-primary)] focus:border-transparent" required oninput="limitDecimalPlaces(this, 2)">
          <p class="text-xs text-[var(--c-muted)] mt-1">Valor relativo a la moneda base (máximo 2 decimales)</p>
        </div>

        <!-- Is Base -->
        <div>
          <label class="flex items-center">
            <input type="checkbox" id="currency-is-base" name="is_base" class="rounded border-[var(--c-border)] text-[var(--c-primary)] focus:ring-[var(--c-primary)]">
            <span class="ml-2 text-sm font-medium text-[var(--c-text)]">Moneda Base</span>
          </label>
          <p class="text-xs text-[var(--c-muted)] mt-1">Solo puede haber una moneda base en el sistema</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-[var(--c-border)]">
          <button type="button" id="btn-cancel-currency" class="px-4 py-2 text-[var(--c-muted)] hover:text-[var(--c-text)] transition">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const API_BASE = '/api';
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || null;

  // Verificar token antes de cargar datos
  if (!API_TOKEN) {
    showError('Autenticación requerida', 'No se encontró un token de acceso válido. Por favor, inicia sesión nuevamente.');
    return;
  }

  // Load initial data
  loadCurrencies();

  // Event listeners
  document.getElementById('btn-create-currency').addEventListener('click', () => openCurrencyModal());
  document.getElementById('btn-refresh-currencies').addEventListener('click', loadCurrencies);
  document.getElementById('search-currencies').addEventListener('input', debounce(loadCurrencies, 300));

  // Form submissions
  document.getElementById('currency-form').addEventListener('submit', saveCurrency);

  // Modal close buttons
  document.getElementById('btn-cancel-currency').addEventListener('click', () => closeCurrencyModal());

  // Functions
  async function loadCurrencies(page = 1) {
    const search = document.getElementById('search-currencies').value;
    const url = `${API_BASE}/currencies?page=${page}&per_page=15&search=${encodeURIComponent(search)}`;

    try {
      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        renderCurrencies(data.data);
        renderPagination(data.data);
      } else {
        showApiError('Error al cargar monedas', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudieron cargar las monedas. Verifica tu conexión a internet.');
    }
  }

  function renderCurrencies(currenciesData) {
    const container = document.getElementById('currencies-list');
    container.innerHTML = '';

    if (currenciesData.data.length === 0) {
      container.innerHTML = '<p class="text-[var(--c-muted)] text-center py-8">No se encontraron monedas</p>';
      return;
    }

    currenciesData.data.forEach(currency => {
      const currencyEl = document.createElement('div');
      currencyEl.className = 'flex items-center justify-between p-4 bg-[var(--c-elev)] rounded-xl border border-[var(--c-border)]';

      currencyEl.innerHTML = `
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-full bg-[var(--c-primary)] flex items-center justify-center text-white font-bold text-lg">
            ${currency.symbol}
          </div>
          <div>
            <h3 class="font-medium text-[var(--c-text)]">${currency.name}</h3>
            <p class="text-sm text-[var(--c-muted)]">${currency.code} • ${currency.symbol}${currency.exchange_rate}</p>
            ${currency.is_base ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">Base</span>' : ''}
          </div>
        </div>
        <div class="flex gap-2">
          <button class="edit-currency-btn px-3 py-1 text-sm bg-[var(--c-primary)] text-[var(--c-primary-ink)] rounded-lg hover:opacity-95 transition" data-id="${currency.id}" data-name="${currency.name}" data-code="${currency.code}" data-symbol="${currency.symbol}" data-exchange-rate="${currency.exchange_rate}" data-is-base="${currency.is_base}">Editar</button>
          <button class="delete-currency-btn px-3 py-1 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" data-id="${currency.id}" data-is-base="${currency.is_base}">Eliminar</button>
        </div>
      `;
      container.appendChild(currencyEl);
    });

    // Add event listeners
    container.querySelectorAll('.edit-currency-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        const name = e.target.dataset.name;
        const code = e.target.dataset.code;
        const symbol = e.target.dataset.symbol;
        const exchangeRate = e.target.dataset.exchangeRate;
        const isBase = e.target.dataset.isBase === '1';
        openCurrencyModal(id, name, code, symbol, exchangeRate, isBase);
      });
    });

    container.querySelectorAll('.delete-currency-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.target.dataset.id;
        const isBase = e.target.dataset.isBase === '1';
        deleteCurrency(id, isBase);
      });
    });
  }

  function renderPagination(currenciesData) {
    const container = document.getElementById('currencies-pagination');
    container.innerHTML = '';

    if (currenciesData.last_page <= 1) return;

    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Anterior';
    prevBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    prevBtn.disabled = !currenciesData.prev_page_url;
    prevBtn.addEventListener('click', () => loadCurrencies(currenciesData.current_page - 1));

    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Siguiente';
    nextBtn.className = 'px-3 py-2 rounded-lg bg-[var(--c-elev)] text-[var(--c-text)] hover:bg-[var(--c-elev)]/80 disabled:opacity-50';
    nextBtn.disabled = !currenciesData.next_page_url;
    nextBtn.addEventListener('click', () => loadCurrencies(currenciesData.current_page + 1));

    const pageInfo = document.createElement('div');
    pageInfo.textContent = `Página ${currenciesData.current_page} de ${currenciesData.last_page}`;
    pageInfo.className = 'text-sm text-[var(--c-muted)]';

    container.appendChild(prevBtn);
    container.appendChild(pageInfo);
    container.appendChild(nextBtn);
  }

  function openCurrencyModal(id = null, name = '', code = '', symbol = '', exchangeRate = '', isBase = false) {
    const modal = document.getElementById('currency-modal');
    const title = document.getElementById('currency-modal-title');
    const idField = document.getElementById('currency-id');
    const nameField = document.getElementById('currency-name');
    const codeField = document.getElementById('currency-code');
    const symbolField = document.getElementById('currency-symbol');
    const exchangeRateField = document.getElementById('currency-exchange-rate');
    const isBaseField = document.getElementById('currency-is-base');

    if (id) {
      title.textContent = 'Editar Moneda';
      idField.value = id;
      nameField.value = name;
      codeField.value = code;
      symbolField.value = symbol;
      exchangeRateField.value = exchangeRate;
      isBaseField.checked = isBase;
    } else {
      title.textContent = 'Crear Moneda';
      idField.value = '';
      nameField.value = '';
      codeField.value = '';
      symbolField.value = '';
      exchangeRateField.value = '';
      isBaseField.checked = false;
    }

    modal.classList.remove('hidden');
  }

  function closeCurrencyModal() {
    document.getElementById('currency-modal').classList.add('hidden');
  }

  async function saveCurrency(e) {
    e.preventDefault();

    const id = document.getElementById('currency-id').value;
    const name = document.getElementById('currency-name').value;
    const code = document.getElementById('currency-code').value;
    const symbol = document.getElementById('currency-symbol').value;
    const exchangeRate = document.getElementById('currency-exchange-rate').value;
    const isBase = document.getElementById('currency-is-base').checked;

    const method = id ? 'PUT' : 'POST';
    const url = id ? `${API_BASE}/currencies/${id}` : `${API_BASE}/currencies`;

    const formData = {
      name,
      code,
      symbol,
      exchange_rate: parseFloat(exchangeRate),
      is_base: isBase
    };

    try {
      const response = await fetch(url, {
        method: method,
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (response.ok && data.success) {
        closeCurrencyModal();
        loadCurrencies();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al guardar moneda', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo guardar la moneda. Verifica tu conexión a internet.');
    }
  }

  async function deleteCurrency(id, isBase) {
    if (isBase) {
      showError('No se puede eliminar', 'No se puede eliminar la moneda base del sistema.');
      return;
    }

    if (!confirm('¿Estás seguro de que quieres eliminar esta moneda?')) return;

    try {
      const response = await fetch(`${API_BASE}/currencies/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${API_TOKEN}`,
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();

      if (response.ok && data.success) {
        loadCurrencies();
        window.dispatchEvent(new CustomEvent('api:response', { detail: data }));
      } else {
        showApiError('Error al eliminar moneda', data);
      }
    } catch (error) {
      showError('Error de conexión', 'No se pudo eliminar la moneda. Verifica tu conexión a internet.');
    }
  }

  function showError(title, message) {
    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: message,
        code: 'CLIENT_ERROR',
        errors: { general: [message] }
      }
    }));
  }

  function showApiError(title, apiResponse) {
    console.error('API Error:', apiResponse);

    window.dispatchEvent(new CustomEvent('api:response', {
      detail: {
        success: false,
        message: apiResponse.message || 'Error desconocido',
        code: apiResponse.code || 'UNKNOWN_ERROR',
        errors: apiResponse.errors || null,
        status: apiResponse.status || null,
        raw: apiResponse
      }
    }));
  }

  function limitDecimalPlaces(input, maxDecimals) {
    const value = input.value;
    const parts = value.split('.');

    if (parts.length > 1) {
      const decimalPart = parts[1];
      if (decimalPart.length > maxDecimals) {
        input.value = parts[0] + '.' + decimalPart.substring(0, maxDecimals);
      }
    }
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
});
</script>
@endsection