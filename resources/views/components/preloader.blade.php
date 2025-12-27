{{-- Preloader Component (optimizado) --}}
<style>
  /* El cuerpo se bloquea SOLO cuando el preloader está activo */
  .no-scroll { overflow: hidden !important; }

  /* Base: NO ocupa layout ni pinta ni anima */
  .preloader-overlay {
    position: fixed; inset: 0; width: 100%; height: 100%;
    background: #000; display: none; /* <- antes usabas visibility:hidden */
    align-items: center; justify-content: center;
    z-index: 9999;
  }
  /* Visible por defecto vía HTML (sin JS), ahora sí pinta */
  .preloader-overlay.is-visible { display: flex; }

  .preloader-spinner {
    width: 64px; height: 64px;
    border: 4px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: preloader-spin 1s ease-in-out infinite;
    /* Si el overlay NO es visible, pausa la animación (defensa extra si cambias display por visibility) */
    animation-play-state: running;
  }
  /* Pausa cuando el overlay no está visible (por si alguien cambia clases) */
  .preloader-overlay:not(.is-visible) .preloader-spinner {
    animation-play-state: paused;
  }

  @keyframes preloader-spin { to { transform: rotate(360deg); } }

  /* Respeta usuarios con reduce motion */
  @media (prefers-reduced-motion: reduce) {
    .preloader-spinner { animation: none !important; }
  }
</style>

{{-- Overlay visible por defecto --}}
<div id="preloader" class="preloader-overlay is-visible" role="status" aria-live="polite" aria-label="Cargando">
  <div class="preloader-spinner"></div>
</div>

<script>
  (function () {
    var MIN_MS = 500; // mínimo de visibilidad para evitar parpadeo
    var preloader = document.getElementById('preloader');
    var lastShowAt = performance.now(); // visible desde el inicio
    var pendingHideTimer = null;

    function actuallyShow() {
      if (!preloader) return;
      preloader.classList.add('is-visible');        // -> display:flex
      document.body && document.body.classList.add('no-scroll');
      lastShowAt = performance.now();
    }

    function actuallyHide() {
      if (!preloader) return;
      preloader.classList.remove('is-visible');     // -> display:none (pausa animación)
      document.body && document.body.classList.remove('no-scroll');
    }

    // APIs públicas (sin cambios)
    window.showPreloader = function () {
      if (!preloader.classList.contains('is-visible')) {
        actuallyShow();
      }
    };

    window.hidePreloader = function () {
      var elapsed = performance.now() - lastShowAt;
      var wait = Math.max(0, MIN_MS - elapsed);

      if (pendingHideTimer) {
        clearTimeout(pendingHideTimer);
        pendingHideTimer = null;
      }
      pendingHideTimer = setTimeout(function () {
        actuallyHide();
        pendingHideTimer = null;
      }, wait);
    };

    // Si por alguna razón el overlay está visible al cargar el DOM, bloquea scroll
    document.addEventListener('DOMContentLoaded', function () {
      if (preloader && preloader.classList.contains('is-visible')) {
        document.body && document.body.classList.add('no-scroll');
      }
    });

    // Oculta cuando todo haya cargado, respetando los 500 ms mínimos
    window.addEventListener('load', function () {
      window.hidePreloader();
    });
  })();
</script>

<noscript>
  <style>
    /* Si JS está deshabilitado, no bloquees la página */
    .preloader-overlay { display: none !important; }
    .no-scroll { overflow: auto !important; }
  </style>
</noscript>