<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import SearchBar from '@/components/SearchBar.vue'

interface Props {
  title?: string
  showBreadcrumbs?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showBreadcrumbs: true
})

const page = usePage()
const mobileMenuOpen = ref(false)

const pageTitle = computed(() => props.title || page.props.title)

const isCurrentRoute = (routePattern: string) => {
  const currentRoute = route().current()
  if (routePattern.includes('*')) {
    const baseRoute = routePattern.replace('.*', '')
    return currentRoute?.startsWith(baseRoute)
  }
  return currentRoute === routePattern
}

const handleGlobalSearch = (query: string) => {
  router.visit(route('search'), {
    data: { q: query },
    preserveState: true
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navegación principal -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo y navegación principal -->
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <Link :href="route('dashboard')" class="text-2xl font-bold text-blue-600">
                🔧 Repuestos
              </Link>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <Link
                :href="route('dashboard')"
                :class="isCurrentRoute('dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Dashboard
              </Link>
              <Link
                :href="route('brands.index')"
                :class="isCurrentRoute('brands.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Marcas
              </Link>
              <Link
                :href="route('categories.index')"
                :class="isCurrentRoute('categories.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Categorías
              </Link>
              <Link
                :href="route('parts.index')"
                :class="isCurrentRoute('parts.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Repuestos
              </Link>
            </div>
          </div>

          <!-- Búsqueda -->
          <div class="flex-1 flex items-center justify-center px-2 lg:ml-6 lg:justify-end">
            <div class="max-w-lg w-full lg:max-w-xs">
              <SearchBar @search="handleGlobalSearch" />
            </div>
          </div>

          <!-- Menú móvil -->
          <div class="sm:hidden flex items-center">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Menú móvil -->
      <div :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen}" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <Link
            :href="route('dashboard')"
            :class="isCurrentRoute('dashboard') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
          >
            Dashboard
          </Link>
          <Link
            :href="route('brands.index')"
            :class="isCurrentRoute('brands.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
          >
            Marcas
          </Link>
          <Link
            :href="route('categories.index')"
            :class="isCurrentRoute('categories.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
          >
            Categorías
          </Link>
          <Link
            :href="route('parts.index')"
            :class="isCurrentRoute('parts.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
          >
            Repuestos
          </Link>
        </div>
      </div>
    </nav>

    <!-- Breadcrumbs -->
    <div v-if="showBreadcrumbs" class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-2 py-3 text-sm">
          <Link :href="route('dashboard')" class="text-gray-500 hover:text-gray-700">
            Inicio
          </Link>
          <svg class="flex-shrink-0 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
          <span class="text-gray-900">{{ pageTitle }}</span>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center text-gray-500 text-sm">
          <p>&copy; 2025 Catálogo de Repuestos. Sistema interno de gestión.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
