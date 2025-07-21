<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout title="Modelos">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Gestión de Modelos</h1>
              <p class="mt-2 text-gray-600">Administra los modelos de vehículos por marca</p>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
              + Nuevo Modelo
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">🚗</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Modelos</p>
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
                <p class="text-sm font-medium text-gray-600">Activos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.active }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">⭐</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Populares</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.popular }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">🔧</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Con Repuestos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.withParts }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar modelo</label>
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Nombre del modelo..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
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
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <select
                v-model="statusFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
                <option value="popular">Populares</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="loadModels"
                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium"
              >
                Buscar
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Cargando modelos...</p>
        </div>

        <!-- Models Grid -->
        <div v-else-if="models.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="model in filteredModels"
            :key="model.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow overflow-hidden"
          >
            <!-- Model Image -->
            <div class="aspect-w-16 aspect-h-9 bg-gray-200">
              <img
                v-if="model.image_url"
                :src="model.image_url"
                :alt="model.name"
                class="w-full h-48 object-cover"
              >
              <div v-else class="w-full h-48 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                <div class="text-center">
                  <span class="text-4xl text-gray-400">🚗</span>
                  <p class="text-sm text-gray-500 mt-2">{{ model.brand.name }}</p>
                </div>
              </div>
            </div>

            <!-- Model Info -->
            <div class="p-6">
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                  <div class="flex items-center mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ model.name }}</h3>
                    <span v-if="model.is_popular" class="ml-2 text-yellow-400 text-lg">⭐</span>
                  </div>
                  <p class="text-sm text-gray-600 mb-1">{{ model.brand.name }}</p>
                  <p v-if="model.code" class="text-xs text-gray-500">Código: {{ model.code }}</p>
                </div>
                <span
                  :class="model.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ model.is_active ? 'Activo' : 'Inactivo' }}
                </span>
              </div>

              <!-- Year Range -->
              <div v-if="model.year_from || model.year_to" class="mb-3">
                <div class="flex items-center text-sm text-gray-600">
                  <span class="text-gray-500">📅</span>
                  <span class="ml-2">
                    {{ getYearRange(model.year_from, model.year_to) }}
                  </span>
                </div>
              </div>

              <!-- Description -->
              <p v-if="model.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
                {{ model.description }}
              </p>

              <!-- Stats -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex space-x-4 text-sm text-gray-500">
                  <div class="flex items-center">
                    <span class="text-blue-500">🔧</span>
                    <span class="ml-1">{{ model.parts_count || 0 }} repuestos</span>
                  </div>
                  <div v-if="model.view_count" class="flex items-center">
                    <span class="text-gray-500">👁️</span>
                    <span class="ml-1">{{ model.view_count }} vistas</span>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                  <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Ver repuestos
                  </button>
                  <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                    Editar
                  </button>
                </div>
                <Link
                  :href="route('models.show', model.id)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Ver detalles →
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
          <div class="text-6xl mb-4">🚗</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No hay modelos registrados</h3>
          <p class="text-gray-500 mb-6">Comienza agregando modelos de vehículos por marca</p>
          <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
            + Crear Primer Modelo
          </button>
        </div>

        <!-- Popular Brands Section -->
        <div v-if="popularBrands.length > 0" class="mt-12">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Explorar por Marca</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <button
              v-for="brand in popularBrands"
              :key="brand.id"
              @click="filterByBrand(brand.id)"
              class="p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow text-center"
            >
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                <span class="text-blue-600 font-bold">{{ brand.name.charAt(0) }}</span>
              </div>
              <h3 class="text-sm font-medium text-gray-900">{{ brand.name }}</h3>
              <p class="text-xs text-gray-500">{{ brand.models_count || 0 }} modelos</p>
            </button>
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

interface Model {
  id: number
  name: string
  code?: string
  year_from?: number
  year_to?: number
  image_url?: string
  description?: string
  is_active: boolean
  is_popular: boolean
  parts_count?: number
  view_count?: number
  brand: {
    id: number
    name: string
  }
}

interface Brand {
  id: number
  name: string
  models_count?: number
}

interface Stats {
  total: number
  active: number
  popular: number
  withParts: number
}

// State
const models = ref<Model[]>([])
const brands = ref<Brand[]>([])
const popularBrands = ref<Brand[]>([])
const loading = ref(true)
const searchTerm = ref('')
const brandFilter = ref('')
const statusFilter = ref('')

const stats = ref<Stats>({
  total: 0,
  active: 0,
  popular: 0,
  withParts: 0
})

// Computed
const filteredModels = computed(() => {
  let filtered = models.value

  if (searchTerm.value) {
    filtered = filtered.filter(model =>
      model.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      (model.code && model.code.toLowerCase().includes(searchTerm.value.toLowerCase()))
    )
  }

  if (brandFilter.value) {
    filtered = filtered.filter(model => model.brand.id.toString() === brandFilter.value)
  }

  if (statusFilter.value === 'active') {
    filtered = filtered.filter(model => model.is_active)
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(model => !model.is_active)
  } else if (statusFilter.value === 'popular') {
    filtered = filtered.filter(model => model.is_popular)
  }

  return filtered
})

// Methods
const loadModels = async () => {
  try {
    loading.value = true
    const [modelsResponse, brandsResponse] = await Promise.all([
      fetch('/api/models'),
      fetch('/api/brands')
    ])

    models.value = await modelsResponse.json()
    brands.value = await brandsResponse.json()
    popularBrands.value = brands.value.filter(brand => (brand.models_count || 0) > 0).slice(0, 12)

    // Calculate stats
    stats.value = {
      total: models.value.length,
      active: models.value.filter(m => m.is_active).length,
      popular: models.value.filter(m => m.is_popular).length,
      withParts: models.value.filter(m => (m.parts_count || 0) > 0).length
    }
  } catch (error) {
    console.error('Error loading models:', error)
  } finally {
    loading.value = false
  }
}

const getYearRange = (yearFrom?: number, yearTo?: number): string => {
  if (yearFrom && yearTo) {
    return `${yearFrom} - ${yearTo}`
  } else if (yearFrom) {
    return `${yearFrom} en adelante`
  } else if (yearTo) {
    return `Hasta ${yearTo}`
  }
  return 'Año no especificado'
}

const filterByBrand = (brandId: number) => {
  brandFilter.value = brandId.toString()
  searchTerm.value = ''
  statusFilter.value = ''
}

// Lifecycle
onMounted(() => {
  loadModels()
})
</script>
