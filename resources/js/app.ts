import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .mount(el);
  },

  //PROGRESO PARA INERTIA.JS
  progress: {
    delay: 250,
    color: '#3B82F6',
    includeCSS: true,
    showSpinner: true,
  }
});

// ✨ CONFIGURACIÓN ADICIONAL DE NPROGRESS (después de que Inertia lo inicialice)
document.addEventListener('DOMContentLoaded', () => {
  // Configurar NProgress después de que se cargue
  if (typeof window !== 'undefined' && window.NProgress) {
    window.NProgress.configure({
      minimum: 0.3,
      easing: 'ease',
      speed: 500,
      trickle: true,
      trickleSpeed: 200,
      template: `
        <div class="bar" role="bar">
          <div class="peg"></div>
        </div>
        <div class="spinner" role="spinner">
          <div class="spinner-icon"></div>
        </div>
      `
    });
  }
});

// ✨ EVENTOS GLOBALES PARA UX
document.addEventListener('inertia:start', (event) => {
  // Agregar clase al body durante navegación
  document.body.classList.add('inertia-loading');

  // Agregar efecto de cursor de carga
  document.body.style.cursor = 'progress';

  // Log para debugging (con verificación de tipo)
  if (event.detail?.visit?.url?.pathname) {
    console.log('🚀 Navegando a:', event.detail.visit.url.pathname);
  }
});

document.addEventListener('inertia:progress', (event) => {
  // Log del progreso (con verificación de tipo)
  if (event.detail?.progress?.percentage) {
    const percentage = Math.round(event.detail.progress.percentage);
    console.log(`📊 Progreso: ${percentage}%`);
  }
});

document.addEventListener('inertia:finish', (event) => {
  // Remover clase del body
  document.body.classList.remove('inertia-loading');

  // Restaurar cursor normal
  document.body.style.cursor = '';

  // Log de finalización
  console.log('✅ Navegación completada');

  // Scroll suave al top en navegaciones nuevas (con verificación)
  if (event.detail?.visit?.url?.pathname &&
      event.detail.visit.url.pathname !== window.location.pathname) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

// ✨ MANEJO DE ERRORES
document.addEventListener('inertia:error', (event) => {
  console.error('❌ Error en navegación:', event.detail?.errors);

  // Limpiar estados de carga
  document.body.classList.remove('inertia-loading');
  document.body.style.cursor = '';
});

// ✨ NAVEGACIÓN INTELIGENTE CON CONFIRMACIÓN
document.addEventListener('inertia:before', (event) => {
  // Solo aplicar en navegaciones GET (no en forms)
  if (event.detail?.visit?.method === 'get') {
    // Buscar formularios con cambios sin guardar
    const unsavedForms = document.querySelectorAll('form[data-unsaved="true"]');

    if (unsavedForms.length > 0) {
      const confirmLeave = confirm('Tienes cambios sin guardar. ¿Deseas continuar?');
      if (!confirmLeave) {
        event.preventDefault();
        return;
      }
    }

    // Detectar campos de formulario modificados (opcional)
    const modifiedInputs = document.querySelectorAll('input[data-original-value], textarea[data-original-value]');
    let hasChanges = false;

    modifiedInputs.forEach((input) => {
      const element = input as HTMLInputElement | HTMLTextAreaElement;
      const originalValue = element.getAttribute('data-original-value');
      if (element.value !== originalValue) {
        hasChanges = true;
      }
    });

    if (hasChanges) {
      const confirmLeave = confirm('Detectamos cambios en el formulario. ¿Deseas continuar sin guardar?');
      if (!confirmLeave) {
        event.preventDefault();
      }
    }
  }
});

// This will set light / dark mode on page load...
initializeTheme();
