<template>
  <!-- Overlay -->
  <div class="fixed inset-0 z-60 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div
        class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75"
        @click="$emit('close')"
      ></div>

      <!-- Modal -->
      <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
              🏷️
            </span>
            Nueva Marca
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600"
          >
            ✕
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
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Descripción
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Descripción opcional de la marca..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
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
  description: ''
})

const errors = ref({})
const creating = ref(false)

const createBrand = async () => {
  try {
    creating.value = true
    errors.value = {}

    const response = await fetch('/parts/create-brand', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(form)
    })

    if (!response.ok) {
      const errorData = await response.json()
      errors.value = errorData.errors || {}
      return
    }

    const newBrand = await response.json()
    emit('created', newBrand)

  } catch (error) {
    console.error('Error creating brand:', error)
    errors.value = { general: 'Error al crear la marca' }
  } finally {
    creating.value = false
  }
}
</script>
