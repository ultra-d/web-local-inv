<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
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
  // Implementar búsqueda
  console.log('Searching for:', query)
}

const handleCategoryClick = (category: any) => {
  // Navegar a categoría
  console.log('Category clicked:', category)
}

const handleBrandClick = (brand: any) => {
  // Navegar a marca
  console.log('Brand clicked:', brand)
}

onMounted(() => {
  loadDashboardData()
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
                        <SearchBar @search="handleSearch" />
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
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Categorías Principales</h2>
                    <CategoryGrid :categories="mainCategories" @category-click="handleCategoryClick" />
                </section>

                <!-- Popular Brands -->
                <section class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Marcas Populares</h2>
                    <BrandGrid :brands="popularBrands" @brand-click="handleBrandClick" />
                </section>

                <!-- Recent Parts -->
                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Repuestos Recientes</h2>
                    <PartsList :parts="recentParts" />
                </section>
            </main>
        </div>
    </AppLayout>
</template>
