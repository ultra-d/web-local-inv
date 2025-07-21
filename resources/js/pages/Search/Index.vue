<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout title="Búsqueda">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Búsqueda de Repuestos</h1>
          <p class="mt-2 text-gray-600">Encuentra repuestos por nombre, código, marca o descripción</p>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Término de búsqueda</label>
              <div class="relative">
                <input
                  v-model="searchQuery"
                  @input="performSearch"
                  type="text"
                  placeholder="Buscar por nombre, código, marca..."
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
              <select
                v-model="categoryFilter"
                @change="performSearch"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Filtros</label>
              <select
                v-model="statusFilter"
                @change="performSearch"
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos</option>
                <option value="available">Disponibles</option>
                <option value="in_stock">En stock</option>
                <option value="bestseller">Bestsellers</option>
              </select>
            </div>
          </div>

          <!-- Quick Search Buttons -->
          <div class="flex flex-wrap gap-2">
            <button
              v-for="suggestion in searchSuggestions"
              :key="suggestion"
              @click="quickSearch(suggestion)"
              class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full"
            >
              {{ suggestion }}
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Buscando repuestos...</p>
        </div>

        <!-- Search Results -->
        <div v-else-if="searchResults.length > 0" class="space-y-6">
          <!-- Results Header -->
          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-medium text-gray-900">
                  Resultados de búsqueda ({{ searchResults.length }})
                </h3>
                <p v-if="searchQuery" class="text-sm text-gray-500 mt-1">
                  Búsqueda: "{{ searchQuery }}"
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <select
                  v-model="sortBy"
                  @change="sortResults"
                  class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                  <option value="relevance">Relevancia</option>
                  <option value="name">Nombre</option>
                  <option value="price_asc">Precio: Menor a Mayor</option>
                  <option value="price_desc">Precio: Mayor a Menor</option>
                  <option value="stock">Stock</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Results Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="part in sortedResults"
              :key="part.id"
              class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
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

              <div class="p-6">
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1">
                    <h4 class="text-lg font-semibold text-gray-900 mb-1">{{ part.name }}</h4>
                    <p class="text-sm text-gray-600">{{ part.brand }}</p>
                  </div>
                  <div class="flex flex-col items-end">
                    <span v-if="part.is_bestseller" class="text-yellow-400 text-lg mb-1">⭐</span>
                    <span
                      :class="part.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                      class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                      {{ part.is_available ? 'Disponible' : 'No disponible' }}
                    </span>
                  </div>
                </div>

                <div class="space-y-2 mb-4">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Código:</span>
                    <span class="font-medium">{{ part.part_number }}</span>
                  </div>
                  <div v-if="part.original_code" class="flex justify-between text-sm">
                    <span class="text-gray-500">Código Original:</span>
                    <span class="font-medium">{{ part.original_code }}</span>
                  </div>
                  <div v-if="part.category" class="flex justify-between text-sm">
                    <span class="text-gray-500">Categoría:</span>
                    <span class="font-medium">{{ part.category.name }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Stock:</span>
                    <span
                      :class="getStockClass(part.stock_quantity, part.min_stock)"
                      class="font-medium"
                    >
                      {{ part.stock_quantity }} unidades
                    </span>
                  </div>
                </div>

                <div v-if="part.description" class="mb-4">
                  <p class="text-sm text-gray-600 line-clamp-2">{{ part.description }}</p>
                </div>

                <div class="flex justify-between items-center">
                  <span class="text-2xl font-bold text-green-600">
                    ${{ formatPrice(part.price) }}
                  </span>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                      Ver más
                    </button>
                    <Link
                      :href="route('parts.show', part.id)"
                      class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                    >
                      Detalles
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- No Results State -->
        <div v-else-if="hasSearched && !loading" class="bg-white rounded-lg shadow p-8 text-center">
          <div class="text-6xl mb-4">🔍</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron resultados</h3>
          <p class="text-gray-500 mb-6">
            Intenta con otros términos de búsqueda o verifica la ortografía
          </p>
          <div class="space-y-2">
            <p class="text-sm text-gray-600">Sugerencias:</p>
            <div class="flex flex-wrap justify-center gap-2">
              <button
                v-for="suggestion in searchSuggestions"
                :key="suggestion"
                @click="quickSearch(suggestion)"
                class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200"
              >
                {{ suggestion }}
              </button>
            </div>
          </div>
        </div>

        <!-- Initial State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
          <div class="text-6xl mb-4">🔍</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Buscar en el inventario</h3>
          <p class="text-gray-500 mb-6">
            Escribe en el buscador para encontrar repuestos específicos
          </p>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto">
            <div
              v-for="category in featuredCategories"
              :key="category.id"
              @click="searchByCategory(category)"
              class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
            >
              <div class="text-2xl mb-2">{{ getCategoryIcon(category.name) }}</div>
              <p class="text-sm font-medium text-gray-900">{{ category.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface SearchResult {
  id: number
  name: string
  part_number: string
  original_code?: string
  brand: string
  price: number
  stock_quantity: number
  min_stock: number
  is_available: boolean
  is_bestseller: boolean
  image_url?: string
  description?: string
  category?: {
    id: number
    name: string
  }
}

interface Category {
  id: number
  name: string
}

// State
const searchQuery = ref('')
const searchResults = ref<SearchResult[]>([])
const categories = ref<Category[]>([])
const featuredCategories = ref<Category[]>([])
const loading = ref(false)
const hasSearched = ref(false)
const categoryFilter = ref('')
const statusFilter = ref('')
const sortBy = ref('relevance')

// Search suggestions
const searchSuggestions = [
  'Motor', 'Frenos', 'Transmisión', 'Filtros', 'Lubricantes', 'Suspensión'
]

// Computed
const sortedResults = computed(() => {
  const results = [...searchResults.value]

  switch (sortBy.value) {
    case 'name':
      return results.sort((a, b) => a.name.localeCompare(b.name))
    case 'price_asc':
      return results.sort((a, b) => a.price - b.price)
    case 'price_desc':
      return results.sort((a, b) => b.price - a.price)
    case 'stock':
      return results.sort((a, b) => b.stock_quantity - a.stock_quantity)
    default:
      return results
  }
})

// Methods
let searchTimeout: number | null = null

const performSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  searchTimeout = setTimeout(async () => {
    if (searchQuery.value.length < 2 && !categoryFilter.value && !statusFilter.value) {
      searchResults.value = []
      hasSearched.value = false
      return
    }

    await executeSearch()
  }, 300)
}

const executeSearch = async () => {
  try {
    loading.value = true
    hasSearched.value = true

    const params = new URLSearchParams()
    if (searchQuery.value) params.append('q', searchQuery.value)
    if (categoryFilter.value) params.append('category', categoryFilter.value)
    if (statusFilter.value) params.append('status', statusFilter.value)

    const response = await fetch(`/api/search?${params.toString()}`)
    searchResults.value = await response.json()
  } catch (error) {
    console.error('Error performing search:', error)
    searchResults.value = []
  } finally {
    loading.value = false
  }
}

const quickSearch = (term: string) => {
  searchQuery.value = term
  categoryFilter.value = ''
  statusFilter.value = ''
  executeSearch()
}

const searchByCategory = (category: Category) => {
  searchQuery.value = ''
  categoryFilter.value = category.id.toString()
  statusFilter.value = ''
  executeSearch()
}

const sortResults = () => {
  // Trigger reactive computation
  sortBy.value = sortBy.value
}

const loadCategories = async () => {
  try {
    const response = await fetch('/api/categories')
    const data = await response.json()
    categories.value = data
    featuredCategories.value = data.slice(0, 8) // Show first 8 categories as featured
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const getStockClass = (stock: number, minStock: number): string => {
  if (stock === 0) return 'text-red-600'
  if (stock <= minStock) return 'text-yellow-600'
  return 'text-green-600'
}

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

const getCategoryIcon = (name: string): string => {
  const iconMap: Record<string, string> = {
    'motor': '🔧',
    'frenos': '🛑',
    'transmision': '⚙️',
    'suspension': '🏗️',
    'electrico': '⚡',
    'carroceria': '🚗',
    'filtros': '🔍',
    'lubricantes': '🛢️',
    'neumaticos': '🛞',
    'iluminacion': '💡'
  }

  for (const [key, icon] of Object.entries(iconMap)) {
    if (name.toLowerCase().includes(key)) {
      return icon
    }
  }

  return '📦'
}

// Lifecycle
onMounted(() => {
  loadCategories()

  // Check if there's a query parameter from URL
  const urlParams = new URLSearchParams(window.location.search)
  const queryParam = urlParams.get('q')
  if (queryParam) {
    searchQuery.value = queryParam
    executeSearch()
  }
})
</script>
