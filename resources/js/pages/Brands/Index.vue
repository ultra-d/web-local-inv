<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout title="Marcas">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Gestión de Marcas</h1>
              <p class="mt-2 text-gray-600">Administra las marcas de vehículos disponibles en el inventario</p>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
              + Nueva Marca
            </button>
          </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar marca</label>
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Nombre de la marca..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <select
                v-model="statusFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos</option>
                <option value="active">Activas</option>
                <option value="inactive">Inactivas</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="loadBrands"
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
          <p class="mt-4 text-gray-600">Cargando marcas...</p>
        </div>

        <!-- Brands Grid -->
        <div v-else-if="brands.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="brand in filteredBrands"
            :key="brand.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-lg">{{ brand.name.charAt(0) }}</span>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-lg font-semibold text-gray-900">{{ brand.name }}</h3>
                    <p class="text-sm text-gray-500">{{ brand.models_count || 0 }} modelos</p>
                  </div>
                </div>
                <span
                  :class="brand.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ brand.is_active ? 'Activa' : 'Inactiva' }}
                </span>
              </div>

              <p v-if="brand.description" class="text-gray-600 text-sm mb-4">
                {{ brand.description.substring(0, 100) }}{{ brand.description.length > 100 ? '...' : '' }}
              </p>

              <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                  <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Ver modelos
                  </button>
                  <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                    Editar
                  </button>
                </div>
                <Link
                  :href="route('brands.show', brand.id)"
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
          <h3 class="text-lg font-medium text-gray-900 mb-2">No hay marcas registradas</h3>
          <p class="text-gray-500 mb-6">Comienza agregando tu primera marca al sistema</p>
          <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
            + Crear Primera Marca
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

interface Brand {
  id: number
  name: string
  description?: string
  is_active: boolean
  models_count?: number
}

// State
const brands = ref<Brand[]>([])
const loading = ref(true)
const searchTerm = ref('')
const statusFilter = ref('')

// Computed
const filteredBrands = computed(() => {
  let filtered = brands.value

  if (searchTerm.value) {
    filtered = filtered.filter(brand =>
      brand.name.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  }

  if (statusFilter.value === 'active') {
    filtered = filtered.filter(brand => brand.is_active)
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(brand => !brand.is_active)
  }

  return filtered
})

// Methods
const loadBrands = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/brands')
    brands.value = await response.json()
  } catch (error) {
    console.error('Error loading brands:', error)
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadBrands()
})
</script>
