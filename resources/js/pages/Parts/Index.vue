<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout title="Repuestos">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Gestión de Repuestos</h1>
              <p class="mt-2 text-gray-600">Administra el inventario completo de repuestos y autopartes</p>
            </div>
            <div class="flex space-x-3">
              <button class="bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium border border-gray-300">
                📥 Importar
              </button>
              <button
                @click="router.get('/parts/create')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"
              >
                + Nuevo Repuesto
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Cards Simplificadas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">🔧</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Repuestos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_parts || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">✅</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Disponibles</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.available_parts || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Advanced Filters Simplificados -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar repuesto</label>
              <input
                ref="searchInput"
                id="search-parts-input"
                v-model="searchTerm"
                type="text"
                placeholder="Nombre, código, descripción..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                @keyup.enter="loadParts"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
              <select
                v-model="categoryFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                @change="loadParts"
              >
                <option value="">Todas las categorías</option>
                <template v-for="category in categoriesForFilter" :key="category.id">
                  <option
                    :value="category.id"
                    :class="category.parent_id ? 'text-gray-600' : 'font-semibold'"
                  >
                    {{ category.parent_id ? '   ↳ ' : '📁 ' }}{{ category.name }}
                    <span v-if="category.parts_count && category.parts_count > 0" class="text-gray-400">
                        ({{ category.parts_count }}) </span>
                  </option>
                </template>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
              <select
                v-model="brandFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                @change="loadParts"
              >
                <option value="">Todas las marcas</option>
                <option v-for="brand in uniqueBrands" :key="brand" :value="brand">
                  {{ brand }}
                </option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="loadParts"
                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium"
              >
                Buscar
              </button>
            </div>
          </div>

          <!-- Quick Filters Simplificados -->
          <div class="flex flex-wrap gap-2">
            <button
              @click="clearFilters"
              class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full hover:bg-gray-200"
            >
              Limpiar filtros
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Cargando repuestos...</p>
        </div>

        <!-- Parts Table -->
        <div v-else-if="parts.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">
              Repuestos ({{ filteredParts.length }})
            </h3>
            <div class="flex items-center space-x-2">
              <button
                @click="viewMode = 'table'"
                :class="viewMode === 'table' ? 'bg-blue-100 text-blue-700' : 'text-gray-500'"
                class="p-2 rounded-md hover:bg-gray-100"
              >
                📋
              </button>
              <button
                @click="viewMode = 'grid'"
                :class="viewMode === 'grid' ? 'bg-blue-100 text-blue-700' : 'text-gray-500'"
                class="p-2 rounded-md hover:bg-gray-100"
              >
                ⚏
              </button>
            </div>
          </div>

          <!-- Table View Simplificada -->
          <div v-if="viewMode === 'table'" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Repuesto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Código Principal
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Categoría
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Precio
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="part in filteredParts" :key="part.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-12 w-12">
                        <img
                          v-if="part.image_url"
                          :src="part.image_url"
                          :alt="part.name"
                          class="h-12 w-12 rounded-lg object-cover"
                        >
                        <div
                          v-else
                          class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center"
                        >
                          <span class="text-gray-500 text-xs">🔧</span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ part.name }}</div>
                        <div class="text-sm text-gray-500">{{ part.brand }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ part.part_number }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="part.category" class="text-sm text-gray-900">
                      {{ part.category.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-bold text-green-600">
                      ${{ formatPrice(part.price) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <Link
                        :href="route('parts.show', part.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Ver
                      </Link>
                      <button class="text-gray-600 hover:text-gray-900">Editar</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Grid View Simplificada -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
            <div
              v-for="part in filteredParts"
              :key="part.id"
              class="border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
            >
              <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-lg bg-gray-200">
                <img
                  v-if="part.image_url"
                  :src="part.image_url"
                  :alt="part.name"
                  class="h-48 w-full object-cover object-center"
                >
                <div v-else class="h-48 w-full flex items-center justify-center bg-gray-100">
                  <span class="text-4xl text-gray-400">🔧</span>
                </div>
              </div>

              <div class="p-4">
                <h3 class="text-sm font-medium text-gray-900 line-clamp-2 mb-2">{{ part.name }}</h3>
                <p class="text-sm text-gray-500 mb-2">{{ part.brand }}</p>
                <p class="text-xs text-gray-400 mb-2">{{ part.part_number }}</p>

                <div class="flex items-center justify-between mb-3">
                  <span class="text-lg font-bold text-green-600">
                    ${{ formatPrice(part.price) }}
                  </span>
                </div>

                <div class="flex justify-between items-center">
                  <span class="text-xs font-medium text-green-600">
                    Disponible
                  </span>
                  <Link
                    :href="route('parts.show', part.id)"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    Ver →
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
          <div class="text-6xl mb-4">🔧</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No hay repuestos registrados</h3>
          <p class="text-gray-500 mb-6">Comienza agregando repuestos a tu inventario</p>
          <button
            @click="router.get('/parts/create')"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium"
          >
            + Crear Primer Repuesto
          </button>
        </div>
      </div>
    </AppLayout>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted} from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Part {
  id: number
  name: string
  part_number: string
  brand: string
  price: number
  is_available: boolean
  image_url?: string
  category?: {
    id: number
    name: string
  }
  model?: {
    id: number
    name: string
    brand: {
      name: string
    }
  }
}

interface Category {
  id: number
  name: string
  parent_id?: number | null
  parts_count?: number
}

interface Stats {
  total_parts: number
  available_parts: number
}

// Props que vienen del controlador
const props = defineProps<{
  parts: {
    data: Part[]
    total: number
    current_page: number
    last_page: number
  }
  categories: Category[]
  hierarchicalCategories?: Category[]
  filters: {
    search?: string
    category_id?: string
  }
  stats: Stats
}>()

// State
const loading = ref(false)
const viewMode = ref<'table' | 'grid'>('table')
const searchTimeout = ref<number | null>(null)
const lastCursorPosition = ref(0)

// Filters (usar los valores iniciales del backend)
const searchTerm = ref(props.filters.search || '')
const categoryFilter = ref(props.filters.category_id || '')
const brandFilter = ref('')

// Computed
const parts = computed(() => props.parts.data || [])
const stats = computed(() => props.stats)

// Helper para obtener el input
const getSearchInput = (): HTMLInputElement | null => {
  return document.getElementById('search-parts-input') as HTMLInputElement
}

const saveCursorPosition = () => {
  const input = getSearchInput()
  if (input) {
    lastCursorPosition.value = input.selectionStart || 0
  }
}

// Organizar categorías para el filtro (principales primero, luego subcategorías)
const categoriesForFilter = computed(() => {
  const categories = props.categories || []

  const principals = categories.filter(cat => !cat.parent_id).sort((a, b) => a.name.localeCompare(b.name))
  const subcategories = categories.filter(cat => cat.parent_id).sort((a, b) => a.name.localeCompare(b.name))

  const result: Category[] = []

  principals.forEach(principal => {
    result.push(principal)
    const subs = subcategories.filter(sub => sub.parent_id === principal.id)
    result.push(...subs)
  })

  return result
})

// Obtener marcas únicas de los repuestos
const uniqueBrands = computed(() => {
  const brands = parts.value.map(part => part.brand).filter(Boolean)
  return [...new Set(brands)].sort()
})

// Filtrar repuestos localmente (para filtros de cliente)
const filteredParts = computed(() => {
  let filtered = parts.value

  if (brandFilter.value) {
    filtered = filtered.filter(part => part.brand === brandFilter.value)
  }

  return filtered
})

// 🔥 BÚSQUEDA AUTOMÁTICA
watch(searchTerm, (newValue, oldValue) => {
  saveCursorPosition()

  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  if (!newValue.trim()) {
    loadPartsWithFocus()
    return
  }

  searchTimeout.value = setTimeout(() => {
    loadPartsWithFocus()
  }, 500)
}, { immediate: false })

// Methods
const loadParts = () => {
  const params = new URLSearchParams()

  if (searchTerm.value.trim()) params.append('search', searchTerm.value.trim())
  if (categoryFilter.value) params.append('category_id', categoryFilter.value)

  const url = '/parts' + (params.toString() ? '?' + params.toString() : '')

  router.get(url)
}

const loadPartsWithFocus = () => {
  const params = new URLSearchParams()

  if (searchTerm.value.trim()) params.append('search', searchTerm.value.trim())
  if (categoryFilter.value) params.append('category_id', categoryFilter.value)

  const newUrl = '/parts' + (params.toString() ? '?' + params.toString() : '')
  const currentUrl = window.location.pathname + window.location.search

  if (newUrl !== currentUrl) {
    router.get(newUrl, {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['parts', 'stats'],
      onFinish: () => {
        restoreFocus()
      }
    })
  }
}

const restoreFocus = () => {
  setTimeout(() => {
    const input = getSearchInput()
    if (input) {
      input.focus()
      const position = lastCursorPosition.value
      input.setSelectionRange(position, position)
    }
  }, 100)
}

onMounted(() => {
  const input = getSearchInput()
  if (input) {
    input.addEventListener('keyup', saveCursorPosition)
    input.addEventListener('click', saveCursorPosition)
  }
})

const clearFilters = () => {
  searchTerm.value = ''
  categoryFilter.value = ''
  brandFilter.value = ''
  router.get('/parts')
}

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}
</script>
