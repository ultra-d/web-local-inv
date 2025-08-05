<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, Link } from '@inertiajs/vue3'
import SearchBar from '@/components/SearchBar.vue'
import StatsCard from '@/components/StatsCard.vue'
import CategoryGrid from '@/components/CategoryGrid.vue'
import BrandGrid from '@/components/BrandGrid.vue'
import PartsList from '@/components/PartsList.vue'

// Interfaces
interface Part {
  id: number
  name: string
  part_number: string
  brand: string
  price: number
  image_path?: string
  category?: {
    id: number
    name: string
  }
  codes?: Array<{
    id: number
    code: string
    type: string
    is_primary: boolean
    is_active: boolean
  }>
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
]

// Estados reactivos simplificados
const stats = ref({
  totalParts: 0,
  availableParts: 0,
  categories: 0
})

const mainCategories = ref([])
const popularBrands = ref([])
const recentParts = ref<Part[]>([])

// Estado del dropdown "Crear Nuevo"
const showCreateDropdown = ref(false)
const createDropdown = ref<HTMLElement | null>(null)

// 🔥 FUNCIONES PARA MANEJO DE IMÁGENES
const getImageUrl = (imagePath: string | undefined): string => {
  if (!imagePath) return ''
  const cleanPath = imagePath.startsWith('parts/') ? imagePath : `parts/${imagePath}`
  return `${window.location.origin}/storage/${cleanPath}`
}

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  console.error('Dashboard image failed to load:', target.src)
  target.style.display = 'none'
}

const getPrimaryCode = (part: Part) => {
  if (part.codes && part.codes.length > 0) {
    const primaryCode = part.codes.find(code => code.is_primary)
    return primaryCode ? primaryCode.code : part.codes[0].code
  }
  return part.part_number || 'N/A'
}

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

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

                            <!-- Botón Crear Nuevo circular con dropdown -->
                            <div class="relative" ref="createDropdown">
                                <button
                                    @click="toggleCreateDropdown"
                                    class="bg-blue-600 hover:bg-blue-700 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center group transition-all duration-200 hover:scale-105"
                                >
                                    <svg
                                        class="w-6 h-6 transition-transform duration-200"
                                        :class="{ 'rotate-45': showCreateDropdown }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </button>

                                <!-- Dropdown menu -->
                                <transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 transform scale-95"
                                    enter-to-class="opacity-1 transform scale-100"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-1 transform scale-100"
                                    leave-to-class="opacity-0 transform scale-95"
                                >
                                    <div
                                        v-if="showCreateDropdown"
                                        class="absolute right-0 mt-3 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                                    >
                                        <button
                                            @click="navigateToCreate('/parts/create')"
                                            class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
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
                                            class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
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
                                            class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
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
                                            class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
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
                                            class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
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
                                </transition>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Stats Cards Simplificadas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="stats-card">
                        <StatsCard
                            title="Total Repuestos"
                            :value="stats.totalParts"
                            icon="🔧"
                            color="blue"
                        />
                    </div>
                    <div class="stats-card">
                        <StatsCard
                            title="Disponibles"
                            :value="stats.availableParts"
                            icon="✅"
                            color="green"
                        />
                    </div>
                    <div class="stats-card">
                        <StatsCard
                            title="Categorías"
                            :value="stats.categories"
                            icon="📁"
                            color="purple"
                        />
                    </div>
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

                <!-- 🔥 REPUESTOS RECIENTES -->
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

                    <!-- Grid de cards como antes pero CON IMÁGENES -->
                    <div v-if="recentParts && recentParts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="part in recentParts"
                            :key="part.id"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <!-- 🔥 IMAGEN EN LA PARTE SUPERIOR -->
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

                                <!-- Badge de precio en la esquina superior derecha -->
                                <div class="absolute top-3 right-3 bg-green-600 text-white px-3 py-1 rounded-full text-sm font-bold shadow-lg">
                                    ${{ formatPrice(part.price) }}
                                </div>
                            </div>

                            <!-- Contenido de la card -->
                            <div class="p-4">
                                <!-- Título del repuesto -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                    {{ part.name }}
                                </h3>

                                <!-- Marca -->
                                <p class="text-sm text-gray-600 mb-3">
                                    {{ part.brand }}
                                </p>

                                <!-- Información adicional -->
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ getPrimaryCode(part) }}
                                        </span>
                                    </div>
                                    <span class="text-xs">
                                        {{ part.category?.name || 'Sin categoría' }}
                                    </span>
                                </div>

                                <!-- Información del estado -->
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                        Disponible
                                    </span>
                                </div>

                                <!-- Botones de acción -->
                                <div class="flex space-x-2">
                                    <Link
                                        :href="route('parts.show', part.id)"
                                        class="flex-1 bg-gray-100 text-gray-700 text-center py-2 px-4 rounded-md hover:bg-gray-200 transition-colors text-sm font-medium flex items-center justify-center space-x-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Ver</span>
                                    </Link>
                                    <Link
                                        :href="route('parts.edit', part.id)"
                                        class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors text-sm font-medium flex items-center justify-center space-x-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Editar</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Si no hay repuestos -->
                    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No hay repuestos</h3>
                        <p class="text-sm text-gray-500 mb-4">Comienza agregando tu primer repuesto al inventario</p>
                        <Link
                            :href="route('parts.create')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Crear Repuesto
                        </Link>
                    </div>
                </section>
            </main>
        </div>
    </AppLayout>
</template>

<style scoped>
.rotate-45 {
  transform: rotate(45deg);
}
</style>
