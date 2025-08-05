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

        <!-- Parts Table/Grid -->
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

          <!-- 🔥 TABLE VIEW CON MINIATURAS -->
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
                  <!-- 🔥 COLUMNA CON IMAGEN + NOMBRE -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-4">
                      <!-- Miniatura de imagen -->
                      <div class="flex-shrink-0 w-12 h-12 relative">
                        <img
                          v-if="part.image_path"
                          :src="getImageUrl(part.image_path)"
                          :alt="part.name"
                          class="w-12 h-12 rounded-lg object-cover border border-gray-200"
                          @error="handleImageError"
                        />
                        <!-- Placeholder si no hay imagen -->
                        <div
                          v-else
                          class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center border border-gray-200"
                        >
                          <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                        </div>
                      </div>

                      <!-- Información del repuesto -->
                      <div>
                        <div class="text-sm font-medium text-gray-900">
                          {{ part.name }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ part.brand }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Código Principal -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ getPrimaryCode(part) }}
                    </span>
                  </td>

                  <!-- Categoría -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ part.category?.name || 'Sin categoría' }}
                  </td>

                  <!-- Precio -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                    ${{ formatPrice(part.price) }}
                  </td>

                  <!-- Acciones -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <Link
                      :href="route('parts.show', part.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Ver
                    </Link>
                    <Link
                      :href="route('parts.edit', part.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Editar
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 🔥 GRID VIEW CON IMÁGENES GRANDES -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
            <div
              v-for="part in filteredParts"
              :key="part.id"
              class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
            >
              <!-- 🔥 IMAGEN EN LA CARD -->
              <div class="aspect-video relative bg-gray-100">
                <img
                  v-if="part.image_path"
                  :src="getImageUrl(part.image_path)"
                  :alt="part.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                <!-- Placeholder si no hay imagen -->
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center text-gray-400"
                >
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>

                <!-- Badge de precio en la esquina -->
                <div class="absolute top-2 right-2 bg-green-600 text-white px-2 py-1 rounded-md text-sm font-medium">
                  ${{ formatPrice(part.price) }}
                </div>
              </div>

              <!-- Contenido de la card -->
              <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ part.name }}</h3>
                <p class="text-sm text-gray-600 mb-2">{{ part.brand }}</p>

                <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                  <span>{{ part.category?.name || 'Sin categoría' }}</span>
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ getPrimaryCode(part) }}
                  </span>
                </div>

                <!-- Botones de acción -->
                <div class="flex space-x-2">
                  <Link
                    :href="route('parts.show', part.id)"
                    class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium"
                  >
                    Ver detalles
                  </Link>
                  <Link
                    :href="route('parts.edit', part.id)"
                    class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                    title="Editar"
                  >
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
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
  image_path?: string
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
  codes?: Array<{
    id: number
    code: string
    type: string
    is_primary: boolean
    is_active: boolean
  }>
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

// 🔥 FUNCIONES PARA MANEJO DE IMÁGENES
const getImageUrl = (imagePath: string | undefined): string => {
  if (!imagePath) return ''
  const cleanPath = imagePath.startsWith('parts/') ? imagePath : `parts/${imagePath}`
  return `${window.location.origin}/storage/${cleanPath}`
}

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  console.error('Image failed to load:', target.src)
  // Ocultar imagen rota
  target.style.display = 'none'
}

const getPrimaryCode = (part: Part) => {
  if (part.codes && part.codes.length > 0) {
    const primaryCode = part.codes.find(code => code.is_primary)
    return primaryCode ? primaryCode.code : part.codes[0].code
  }
  return part.part_number || 'Sin código'
}

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
