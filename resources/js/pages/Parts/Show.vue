<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout :title="part.name">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <button
                @click="router.get('/parts')"
                class="text-gray-500 hover:text-gray-700"
              >
                ← Volver a repuestos
              </button>
              <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ part.name }}</h1>
                <p class="mt-2 text-gray-600">{{ part.brand }}</p>
              </div>
            </div>
            <div class="flex space-x-3">
              <Link
                :href="route('parts.edit', part.id)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium"
              >
                ✏️ Editar
              </Link>
              <button
                @click="showDeleteModal = true"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium"
              >
                🗑️ Eliminar
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Imagen -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
              <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200">
                <img
                  v-if="part.image_url"
                  :src="part.image_url"
                  :alt="part.name"
                  class="h-64 w-full object-cover object-center"
                >
                <div v-else class="h-64 w-full flex items-center justify-center bg-gray-100">
                  <span class="text-6xl text-gray-400">🔧</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Información Principal -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información Básica -->
            <div class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                  🔧
                </span>
                Información Básica
              </h3>

              <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Precio</dt>
                  <dd class="mt-1 text-2xl font-bold text-green-600">
                    ${{ formatPrice(part.price) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Estado</dt>
                  <dd class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      ✅ Disponible
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <span v-if="part.category" class="inline-flex items-center">
                      📁 {{ part.category.name }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Modelo de Vehículo</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <span v-if="part.model && part.model.brand">
                      🚗 {{ part.model.brand.name }} {{ part.model.name }}
                    </span>
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Códigos -->
            <div class="bg-white rounded-lg shadow p-6">
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
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                  :class="{ 'bg-blue-50 border border-blue-200': code.is_primary }"
                >
                  <div class="flex items-center space-x-3">
                    <span class="text-lg">{{ getTypeIcon(code.type) }}</span>
                    <div>
                      <div class="flex items-center space-x-2">
                        <span class="font-mono text-lg font-bold text-gray-900">
                          {{ code.code }}
                        </span>
                        <span
                          v-if="code.is_primary"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                        >
                          Principal
                        </span>
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ getTypeLabel(code.type) }}
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center">
                    <span
                      :class="code.is_active ? 'text-green-600' : 'text-gray-400'"
                      class="text-sm font-medium"
                    >
                      {{ code.is_active ? '✅ Activo' : '⭕ Inactivo' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Descripción -->
            <div v-if="part.description" class="bg-white rounded-lg shadow p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                  📝
                </span>
                Descripción
              </h3>
              <p class="text-gray-700 whitespace-pre-wrap">{{ part.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>

    <!-- Modal de Confirmación de Eliminación -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click="showDeleteModal = false"
    >
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-2">Eliminar Repuesto</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              ¿Estás seguro de que deseas eliminar el repuesto "{{ part.name }}"?
              Esta acción no se puede deshacer.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <div class="flex space-x-3">
              <button
                @click="showDeleteModal = false"
                class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
              >
                Cancelar
              </button>
              <button
                @click="deletePart"
                :disabled="deleting"
                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
              >
                <span v-if="deleting">Eliminando...</span>
                <span v-else>Eliminar</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// Props del controlador
const props = defineProps({
  part: Object
})

// Estados
const showDeleteModal = ref(false)
const deleting = ref(false)

// Métodos
const formatPrice = (price) => {
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

const getTypeIcon = (type) => {
  switch (type) {
    case 'internal': return '🏢'
    case 'oem': return '⚙️'
    case 'aftermarket': return '🔧'
    case 'universal': return '🌐'
    default: return '📦'
  }
}

const getTypeLabel = (type) => {
  switch (type) {
    case 'internal': return 'Código Interno'
    case 'oem': return 'Código OEM'
    case 'aftermarket': return 'Código Aftermarket'
    case 'universal': return 'Código Universal'
    default: return type
  }
}

const deletePart = async () => {
  try {
    deleting.value = true

    router.delete(route('parts.destroy', props.part.id), {
      onSuccess: () => {
        showDeleteModal.value = false
        // La redirección se maneja en el controlador
      },
      onError: (errors) => {
        console.error('Error deleting part:', errors)
        alert('Error al eliminar el repuesto')
      },
      onFinish: () => {
        deleting.value = false
      }
    })
  } catch (error) {
    console.error('Error:', error)
    deleting.value = false
  }
}
</script>
