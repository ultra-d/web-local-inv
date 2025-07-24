<template>
  <!-- Overlay -->
  <div class="fixed inset-0 z-[99999] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay - fondo súper sutil -->
      <div
        class="fixed inset-0 transition-opacity bg-white bg-opacity-10"
        @click="$emit('close')"
      ></div>

      <!-- Modal -->
      <div class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg z-10">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
              🏷️
            </span>
            Nueva Marca
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="createBrand" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nombre de la Marca *
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Ej: Toyota, Honda, Ford..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Código (Opcional)
            </label>
            <input
              v-model="form.code"
              type="text"
              placeholder="Ej: TOY, HON, FOR (se genera automáticamente si está vacío)"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
              :class="{ 'border-red-500': errors.code }"
            />
            <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              País de Origen
            </label>
            <input
              v-model="form.country"
              type="text"
              placeholder="Ej: Japón, Estados Unidos, Alemania..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Descripción
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Descripción opcional de la marca..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
            ></textarea>
          </div>

          <div v-if="errors.general" class="p-3 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-600">{{ errors.general }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="creating"
              class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors disabled:opacity-50"
            >
              <span v-if="creating">Creando...</span>
              <span v-else>Crear Marca</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const emit = defineEmits(['close', 'created'])

const form = reactive({
  name: '',
  code: '',
  country: '',
  description: ''
})

const errors = ref({})
const creating = ref(false)

const createBrand = async () => {
  try {
    creating.value = true
    errors.value = {}

    const response = await fetch('/brands/store-ajax', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form)
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        errors.value = data.errors
      } else {
        errors.value = { general: data.message || 'Error al crear la marca' }
      }
      return
    }

    emit('created', data.brand || data)

  } catch (error) {
    console.error('Error creating brand:', error)
    errors.value = { general: 'Error de conexión. Inténtalo de nuevo.' }
  } finally {
    creating.value = false
  }
}
</script>
