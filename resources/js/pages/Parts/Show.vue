<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout :title="part.name">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header con navegación -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                @click="router.get('/parts')"
                class="text-gray-500 hover:text-gray-700 flex items-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Volver a repuestos</span>
              </button>
            </div>

            <div class="flex space-x-3">
              <Link
                :href="route('parts.edit', part.id)"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Editar</span>
              </Link>

              <button
                @click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors flex items-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Eliminar</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Contenido principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

          <!-- Columna izquierda: Imagen -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
              <div class="aspect-square relative">
                <!-- 🔥 IMAGEN DEL REPUESTO -->
                <img
                  v-if="part.image_path"
                  :src="getImageUrl(part.image_path)"
                  :alt="part.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                  @load="handleImageLoad"
                />

                <!-- Placeholder si no hay imagen -->
                <div
                  v-else
                  class="w-full h-full bg-gray-100 flex items-center justify-center"
                >
                  <div class="text-center text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm">Sin imagen</p>
                  </div>
                </div>

                <!-- Estado de carga de imagen -->
                <div
                  v-if="imageLoading"
                  class="absolute inset-0 bg-gray-100 flex items-center justify-center"
                >
                  <div class="text-center text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                    <p class="text-sm">Cargando imagen...</p>
                  </div>
                </div>

                <!-- Error de imagen -->
                <div
                  v-if="imageError"
                  class="absolute inset-0 bg-red-50 flex items-center justify-center"
                >
                  <div class="text-center text-red-500">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm">Error al cargar imagen</p>
                    <p class="text-xs mt-1">{{ imageErrorMessage }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Columna derecha: Información -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Información básica -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ part.name }}</h1>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Marca</h3>
                  <p class="text-lg text-gray-900">{{ part.brand }}</p>
                </div>

                <div>
                  <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Precio</h3>
                  <p class="text-2xl font-bold text-green-600">${{ formatPrice(part.price) }}</p>
                </div>

                <div>
                  <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Categoría</h3>
                  <p class="text-lg text-gray-900">{{ part.category?.name || 'Sin categoría' }}</p>
                </div>

                <div>
                  <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Modelo Compatible</h3>
                  <p class="text-lg text-gray-900">
                    {{ part.model?.brand?.name }} {{ part.model?.name }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Códigos -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                  🏷️
                </span>
                Códigos del Repuesto
              </h3>

              <div class="space-y-3">
                <div
                  v-for="code in part.codes"
                  :key="code.id"
                  class="flex items-center justify-between p-3 rounded-lg border"
                  :class="{
                    'bg-blue-50 border-blue-200': code.is_primary,
                    'bg-gray-50 border-gray-200': !code.is_primary
                  }"
                >
                  <div class="flex items-center space-x-3">
                    <span class="text-lg">{{ getCodeIcon(code.type) }}</span>
                    <div>
                      <div class="font-medium text-gray-900">{{ code.code }}</div>
                      <div class="text-sm text-gray-500">{{ getCodeTypeName(code.type) }}</div>
                    </div>
                  </div>

                  <div class="flex items-center space-x-2">
                    <span
                      v-if="code.is_primary"
                      class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                    >
                      Principal
                    </span>
                    <span
                      v-if="code.is_active"
                      class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full"
                    >
                      Activo
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Descripción -->
            <div v-if="part.description" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                  📝
                </span>
                Descripción
              </h3>
              <p class="text-gray-700 leading-relaxed">{{ part.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  part: Object
})

const imageLoading = ref(false)
const imageError = ref(false)
const imageErrorMessage = ref('')
const showDebugInfo = ref(false) // Cambiar a true para debug

// 🔥 FUNCIÓN PARA GENERAR URL DE IMAGEN
const getImageUrl = (imagePath) => {
  if (!imagePath) return null

  // Asegurar que la ruta no tenga doble slash
  const cleanPath = imagePath.startsWith('parts/') ? imagePath : `parts/${imagePath}`
  const url = `${window.location.origin}/storage/${cleanPath}`

  console.log('🖼️ Generated image URL:', url)
  return url
}

const handleImageLoad = () => {
  imageLoading.value = false
  imageError.value = false
  console.log('✅ Image loaded successfully')
}

const handleImageError = (event) => {
  imageLoading.value = false
  imageError.value = true
  imageErrorMessage.value = `URL: ${event.target.src}`
  console.error('❌ Image failed to load:', event.target.src)
}

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const getCodeIcon = (type) => {
  const icons = {
    'internal': '🏢',
    'oem': '⚙️',
    'aftermarket': '🔧',
    'universal': '🌐'
  }
  return icons[type] || '📦'
}

const getCodeTypeName = (type) => {
  const names = {
    'internal': 'Interno',
    'oem': 'OEM',
    'aftermarket': 'Aftermarket',
    'universal': 'Universal'
  }
  return names[type] || type
}

const confirmDelete = () => {
  if (confirm(`¿Estás seguro de que quieres eliminar "${props.part.name}"?`)) {
    router.delete(`/parts/${props.part.id}`)
  }
}

// Debug info
onMounted(() => {
  console.log('🔍 Part data:', props.part)
  if (props.part.image_path) {
    console.log('🖼️ Image path:', props.part.image_path)
    console.log('🔗 Image URL:', getImageUrl(props.part.image_path))
  }
})
</script>
