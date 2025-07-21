<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout title="Categorías">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Gestión de Categorías</h1>
              <p class="mt-2 text-gray-600">Organiza los repuestos por categorías para facilitar la búsqueda</p>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
              + Nueva Categoría
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">📁</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Categorías</p>
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
                <p class="text-sm font-medium text-gray-600">Activas</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.active }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">📦</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Con Repuestos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.withParts }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-2xl">🏗️</span>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Principales</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.parents }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar categoría</label>
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Nombre de la categoría..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
              <select
                v-model="typeFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas</option>
                <option value="parent">Principales</option>
                <option value="child">Subcategorías</option>
              </select>
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
                @click="loadCategories"
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
          <p class="mt-4 text-gray-600">Cargando categorías...</p>
        </div>

        <!-- Categories Tree -->
        <div v-else-if="categories.length > 0" class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              Categorías ({{ filteredCategories.length }})
            </h3>
          </div>

          <div class="divide-y divide-gray-200">
            <div
              v-for="category in filteredCategories"
              :key="category.id"
              class="p-6 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <!-- Icon based on category -->
                  <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-4"
                       :class="getCategoryIconClass(category.name)">
                    <span class="text-lg">{{ getCategoryIcon(category.name) }}</span>
                  </div>

                  <div>
                    <div class="flex items-center">
                      <h4 class="text-lg font-semibold text-gray-900">{{ category.name }}</h4>
                      <span v-if="!category.parent_id" class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                        Principal
                      </span>
                      <span v-else class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                        Subcategoría
                      </span>
                    </div>

                    <div class="flex items-center mt-1 text-sm text-gray-500">
                      <span>{{ category.parts_count || 0 }} repuestos</span>
                      <span v-if="category.parent" class="ml-2">
                        • Padre: {{ category.parent.name }}
                      </span>
                      <span v-if="category.children_count" class="ml-2">
                        • {{ category.children_count }} subcategorías
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex items-center space-x-4">
                  <span
                    :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ category.is_active ? 'Activa' : 'Inactiva' }}
                  </span>

                  <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                      Ver repuestos
                    </button>
                    <button class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                      Editar
                    </button>
                    <Link
                      :href="route('categories.show', category.id)"
                      class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                    >
                      Ver detalles →
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
          <div class="text-6xl mb-4">📁</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No hay categorías registradas</h3>
          <p class="text-gray-500 mb-6">Organiza tu inventario creando categorías para los repuestos</p>
          <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
            + Crear Primera Categoría
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

interface Category {
  id: number
  name: string
  slug: string
  parent_id?: number
  is_active: boolean
  parts_count?: number
  children_count?: number
  parent?: {
    name: string
  }
}

interface Stats {
  total: number
  active: number
  withParts: number
  parents: number
}

// State
const categories = ref<Category[]>([])
const loading = ref(true)
const searchTerm = ref('')
const typeFilter = ref('')
const statusFilter = ref('')

const stats = ref<Stats>({
  total: 0,
  active: 0,
  withParts: 0,
  parents: 0
})

// Computed
const filteredCategories = computed(() => {
  let filtered = categories.value

  if (searchTerm.value) {
    filtered = filtered.filter(category =>
      category.name.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  }

  if (typeFilter.value === 'parent') {
    filtered = filtered.filter(category => !category.parent_id)
  } else if (typeFilter.value === 'child') {
    filtered = filtered.filter(category => category.parent_id)
  }

  if (statusFilter.value === 'active') {
    filtered = filtered.filter(category => category.is_active)
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(category => !category.is_active)
  }

  return filtered
})

// Methods
const loadCategories = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/categories')
    const data = await response.json()
    categories.value = data

    // Calculate stats
    stats.value = {
      total: data.length,
      active: data.filter((c: Category) => c.is_active).length,
      withParts: data.filter((c: Category) => (c.parts_count || 0) > 0).length,
      parents: data.filter((c: Category) => !c.parent_id).length
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  } finally {
    loading.value = false
  }
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

const getCategoryIconClass = (name: string): string => {
  const classMap: Record<string, string> = {
    'motor': 'bg-red-100',
    'frenos': 'bg-orange-100',
    'transmision': 'bg-blue-100',
    'suspension': 'bg-green-100',
    'electrico': 'bg-yellow-100',
    'carroceria': 'bg-purple-100',
    'filtros': 'bg-indigo-100',
    'lubricantes': 'bg-pink-100',
    'neumaticos': 'bg-gray-100',
    'iluminacion': 'bg-cyan-100'
  }

  for (const [key, className] of Object.entries(classMap)) {
    if (name.toLowerCase().includes(key)) {
      return className
    }
  }

  return 'bg-gray-100'
}

// Lifecycle
onMounted(() => {
  loadCategories()
})
</script>
