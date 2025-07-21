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
              <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                + Nuevo Repuesto
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">🔧</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
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
                <p class="text-2xl font-bold text-gray-900">{{ stats.available }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">⚠️</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Stock Bajo</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.lowStock }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">❌</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Sin Stock</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.outOfStock }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">⭐</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Bestsellers</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.bestsellers }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Advanced Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar repuesto</label>
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Nombre, código, descripción..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
              <select
                v-model="categoryFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas las categorías</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
              <select
                v-model="brandFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas las marcas</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado Stock</label>
              <select
                v-model="stockFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos</option>
                <option value="in_stock">En stock</option>
                <option value="low_stock">Stock bajo</option>
                <option value="out_of_stock">Sin stock</option>
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

          <!-- Quick Filters -->
          <div class="flex flex-wrap gap-2">
            <button
              @click="stockFilter = 'low_stock'"
              class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded-full hover:bg-yellow-200"
            >
              Stock Bajo
            </button>
            <button
              @click="stockFilter = 'out_of_stock'"
              class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-full hover:bg-red-200"
            >
              Sin Stock
            </button>
            <button
              @click="setBestsellerFilter"
              class="px-3 py-1 text-sm bg-purple-100 text-purple-800 rounded-full hover:bg-purple-200"
            >
              Bestsellers
            </button>
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

          <!-- Table View -->
          <div v-if="viewMode === 'table'" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Repuesto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Código
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Categoría
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stock
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Precio
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
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
                    <div v-if="part.original_code" class="text-sm text-gray-500">
                      Orig: {{ part.original_code }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="part.category" class="text-sm text-gray-900">
                      {{ part.category.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <span class="text-sm font-medium text-gray-900">{{ part.stock_quantity }}</span>
                      <span
                        :class="getStockStatusClass(part.stock_quantity, part.min_stock)"
                        class="ml-2 px-2 py-1 text-xs font-medium rounded-full"
                      >
                        {{ getStockStatus(part.stock_quantity, part.min_stock) }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-bold text-green-600">
                      ${{ formatPrice(part.price) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-col space-y-1">
                      <span
                        :class="part.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        class="px-2 py-1 text-xs font-medium rounded-full text-center"
                      >
                        {{ part.is_available ? 'Disponible' : 'No disponible' }}
                      </span>
                      <span
                        v-if="part.is_bestseller"
                        class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 text-center"
                      >
                        ⭐ Bestseller
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button class="text-blue-600 hover:text-blue-900">Ver</button>
                      <button class="text-gray-600 hover:text-gray-900">Editar</button>
                      <Link
                        :href="route('parts.show', part.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        Detalles
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Grid View -->
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
                <div class="flex items-start justify-between mb-2">
                  <h3 class="text-sm font-medium text-gray-900 line-clamp-2">{{ part.name }}</h3>
                  <span
                    v-if="part.is_bestseller"
                    class="ml-2 text-yellow-400 text-lg"
                  >
                    ⭐
                  </span>
                </div>

                <p class="text-sm text-gray-500 mb-2">{{ part.brand }}</p>
                <p class="text-xs text-gray-400 mb-2">{{ part.part_number }}</p>

                <div class="flex items-center justify-between mb-3">
                  <span class="text-lg font-bold text-green-600">
                    ${{ formatPrice(part.price) }}
                  </span>
                  <span
                    :class="getStockStatusClass(part.stock_quantity, part.min_stock)"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    Stock: {{ part.stock_quantity }}
                  </span>
                </div>

                <div class="flex justify-between items-center">
                  <span
                    :class="part.is_available ? 'text-green-600' : 'text-red-600'"
                    class="text-xs font-medium"
                  >
                    {{ part.is_available ? 'Disponible' : 'No disponible' }}
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
          <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
            + Agregar Primer Repuesto
          </button>
        </div>
      </div>
    </AppLayout>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Part {
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
}

interface Brand {
  id: number
  name: string
}

interface Stats {
  total: number
  available: number
  lowStock: number
  outOfStock: number
  bestsellers: number
}

// State
const parts = ref<Part[]>([])
const categories = ref<Category[]>([])
const brands = ref<Brand[]>([])
const loading = ref(true)
const viewMode = ref<'table' | 'grid'>('table')

// Filters
const searchTerm = ref('')
const categoryFilter = ref('')
const brandFilter = ref('')
const stockFilter = ref('')

const stats = ref<Stats>({
  total: 0,
  available: 0,
  lowStock: 0,
  outOfStock: 0,
  bestsellers: 0
})

// Computed
const filteredParts = computed(() => {
  let filtered = parts.value

  if (searchTerm.value) {
    const search = searchTerm.value.toLowerCase()
    filtered = filtered.filter(part =>
      part.name.toLowerCase().includes(search) ||
      part.part_number.toLowerCase().includes(search) ||
      (part.original_code && part.original_code.toLowerCase().includes(search)) ||
      part.brand.toLowerCase().includes(search)
    )
  }

  if (categoryFilter.value) {
    filtered = filtered.filter(part => part.category?.id.toString() === categoryFilter.value)
  }

  if (brandFilter.value) {
    filtered = filtered.filter(part => part.model?.brand?.name === brandFilter.value)
  }

  if (stockFilter.value) {
    filtered = filtered.filter(part => {
      const status = getStockStatus(part.stock_quantity, part.min_stock)
      return status.toLowerCase().replace(' ', '_') === stockFilter.value
    })
  }

  return filtered
})

// Methods
const loadParts = async () => {
  try {
    loading.value = true
    const [partsResponse, categoriesResponse, brandsResponse] = await Promise.all([
      fetch('/api/parts'),
      fetch('/api/categories'),
      fetch('/api/brands')
    ])

    parts.value = await partsResponse.json()
    categories.value = await categoriesResponse.json()
    brands.value = await brandsResponse.json()

    // Calculate stats
    stats.value = {
      total: parts.value.length,
      available: parts.value.filter(p => p.is_available).length,
      lowStock: parts.value.filter(p => p.stock_quantity <= p.min_stock && p.stock_quantity > 0).length,
      outOfStock: parts.value.filter(p => p.stock_quantity === 0).length,
      bestsellers: parts.value.filter(p => p.is_bestseller).length
    }
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
}

const getStockStatus = (stock: number, minStock: number): string => {
  if (stock === 0) return 'Sin Stock'
  if (stock <= minStock) return 'Stock Bajo'
  return 'En Stock'
}

const getStockStatusClass = (stock: number, minStock: number): string => {
  if (stock === 0) return 'bg-red-100 text-red-800'
  if (stock <= minStock) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

const setBestsellerFilter = () => {
  // Filter bestsellers by setting a custom filter
  searchTerm.value = ''
  categoryFilter.value = ''
  brandFilter.value = ''
  stockFilter.value = ''
  // This would need additional logic to filter bestsellers
}

const clearFilters = () => {
  searchTerm.value = ''
  categoryFilter.value = ''
  brandFilter.value = ''
  stockFilter.value = ''
}

// Lifecycle
onMounted(() => {
  loadParts()
})
</script>
