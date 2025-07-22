<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import SearchBar from '@/components/SearchBar.vue'
import StatsCard from '@/components/StatsCard.vue'
import CategoryGrid from '@/components/CategoryGrid.vue'
import BrandGrid from '@/components/BrandGrid.vue'
import PartsList from '@/components/PartsList.vue'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
]

// Estados reactivos
const stats = ref({
  totalParts: 0,
  lowStock: 0,
  bestsellers: 0,
  categories: 0
})

const mainCategories = ref([])
const popularBrands = ref([])
const recentParts = ref([])

// Estado del dropdown "Crear Nuevo"
const showCreateDropdown = ref(false)
const createDropdown = ref<HTMLElement | null>(null)

// Métodos
const loadDashboardData = async () => {
  try {
    const response = await fetch('/api/dashboard')
    const data = await response.json()

    stats.value = data.stats
    mainCategories.value = data.categories
    popularBrands.value = data.brands
    recentParts.value = data.recentParts
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  }
}

const handleSearch = (query: string) => {
  router.get('/search', { q: query })
}

const handleSelectPart = (part: any) => {
  router.get(`/parts/${part.id}`)
}

const handleCategoryClick = (category: any) => {
  router.get(`/categories/${category.id}`)
}

const handleBrandClick = (brand: any) => {
  router.get(`/brands/${brand.id}`)
}

// Métodos del dropdown "Crear Nuevo"
const toggleCreateDropdown = () => {
  showCreateDropdown.value = !showCreateDropdown.value
}

const closeCreateDropdown = (event: Event) => {
  const target = event.target as HTMLElement | null
  if (createDropdown.value && target && !createDropdown.value.contains(target)) {
    showCreateDropdown.value = false
  }
}

const navigateToCreate = (route: string) => {
  router.get(route)
  showCreateDropdown.value = false
}

const openImportModal = () => {
  // Implementar modal de importación más adelante
  console.log('Abrir modal de importación')
  showCreateDropdown.value = false
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
  document.addEventListener('click', closeCreateDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeCreateDropdown)
})
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center">
                            <h1 class="text-2xl font-bold text-gray-900">
                                🔧 Catálogo de Repuestos
                            </h1>
                        </div>

                        <!-- Contenedor SearchBar + Botón Crear -->
                        <div class="flex items-center space-x-4">
                            <!-- SearchBar existente -->
                            <SearchBar
                                @search="handleSearch"
                                @select="handleSelectPart"
                            />

                            <!-- Botón Crear Nuevo con dropdown -->
                            <div class="relative" ref="createDropdown">
                                <button
                                    @click="toggleCreateDropdown"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center space-x-2 shadow-sm transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Crear Nuevo</span>
                                    <svg
                                        class="w-4 h-4 transition-transform"
                                        :class="{ 'rotate-180': showCreateDropdown }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown menu -->
                                <div
                                    v-if="showCreateDropdown"
                                    class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                                >
                                    <button
                                        @click="navigateToCreate('/parts/create')"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                    >
                                        <span class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-lg">🔧</span>
                                        </span>
                                        <div class="text-left">
                                            <div class="font-medium">Nuevo Repuesto</div>
                                            <div class="text-xs text-gray-500">Agregar autopartes al inventario</div>
                                        </div>
                                    </button>

                                    <button
                                        @click="navigateToCreate('/categories/create')"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                    >
                                        <span class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-lg">📁</span>
                                        </span>
                                        <div class="text-left">
                                            <div class="font-medium">Nueva Categoría</div>
                                            <div class="text-xs text-gray-500">Organizar repuestos por tipo</div>
                                        </div>
                                    </button>

                                    <button
                                        @click="navigateToCreate('/models/create')"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                    >
                                        <span class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-lg">🚗</span>
                                        </span>
                                        <div class="text-left">
                                            <div class="font-medium">Nuevo Modelo</div>
                                            <div class="text-xs text-gray-500">Agregar modelo de vehículo</div>
                                        </div>
                                    </button>

                                    <button
                                        @click="navigateToCreate('/brands/create')"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                    >
                                        <span class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-lg">🏷️</span>
                                        </span>
                                        <div class="text-left">
                                            <div class="font-medium">Nueva Marca</div>
                                            <div class="text-xs text-gray-500">Registrar marca de vehículo</div>
                                        </div>
                                    </button>

                                    <hr class="my-2 border-gray-200">

                                    <button
                                        @click="openImportModal"
                                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                    >
                                        <span class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                            <span class="text-lg">📥</span>
                                        </span>
                                        <div class="text-left">
                                            <div class="font-medium">Importar Datos</div>
                                            <div class="text-xs text-gray-500">Subir archivo Excel/CSV</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <StatsCard
                        title="Total Repuestos"
                        :value="stats.totalParts"
                        icon="🔧"
                        color="blue"
                    />
                    <StatsCard
                        title="Stock Bajo"
                        :value="stats.lowStock"
                        icon="⚠️"
                        color="yellow"
                    />
                    <StatsCard
                        title="Más Vendidos"
                        :value="stats.bestsellers"
                        icon="⭐"
                        color="green"
                    />
                    <StatsCard
                        title="Categorías"
                        :value="stats.categories"
                        icon="📁"
                        color="purple"
                    />
                </div>

                <!-- Quick Access Categories -->
                <section class="mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Categorías Principales</h2>
                        <button
                            @click="router.get('/categories')"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                            Ver todas →
                        </button>
                    </div>
                    <CategoryGrid :categories="mainCategories" @category-click="handleCategoryClick" />
                </section>

                <!-- Popular Brands -->
                <section class="mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Marcas Populares</h2>
                        <button
                            @click="router.get('/brands')"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                            Ver todas →
                        </button>
                    </div>
                    <BrandGrid :brands="popularBrands" @brand-click="handleBrandClick" />
                </section>

                <!-- Recent Parts -->
                <section>
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Repuestos Recientes</h2>
                        <button
                            @click="router.get('/parts')"
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                        >
                            Ver todos →
                        </button>
                    </div>
                    <PartsList :parts="recentParts" />
                </section>
            </main>
        </div>
    </AppLayout>
</template>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style>
