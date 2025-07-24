<template>
  <!-- Overlay -->
  <div class="fixed inset-0 z-[99999] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay - fondo súper sutil -->
      <div
        class="fixed inset-0 transition-opacity bg-white bg-opacity-40"
        @click="$emit('close')"
      ></div>

      <!-- Modal -->
      <div class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg z-10">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              📁
            </span>
            Nueva Categoría
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

        <form @submit.prevent="createCategory" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nombre de la Categoría *
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Ej: Motor, Frenos, Transmisión..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Categoría Padre (Opcional)
            </label>
            <select
              v-model="form.parent_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
            >
              <option value="">Categoría principal</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Color
              </label>
              <div class="flex space-x-2">
                <input
                  v-model="form.color"
                  type="color"
                  class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                />
                <input
                  v-model="form.color"
                  type="text"
                  placeholder="#3B82F6"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Icono
              </label>
              <select
                v-model="form.icon"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
              >
                <option v-for="icon in iconOptions" :key="icon.value" :value="icon.value">
                  {{ icon.emoji }} {{ icon.label }}
                </option>
              </select>
            </div>
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
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50"
            >
              <span v-if="creating">Creando...</span>
              <span v-else>Crear Categoría</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const props = defineProps({
  categories: Array
})

const emit = defineEmits(['close', 'created'])

const form = reactive({
  name: '',
  parent_id: '',
  color: '#3B82F6',
  icon: '📦'
})

const errors = ref({})
const creating = ref(false)

const iconOptions = [
  { value: '🔧', label: 'Motor', emoji: '🔧' },
  { value: '🛑', label: 'Frenos', emoji: '🛑' },
  { value: '⚙️', label: 'Transmisión', emoji: '⚙️' },
  { value: '🏗️', label: 'Suspensión', emoji: '🏗️' },
  { value: '⚡', label: 'Eléctrico', emoji: '⚡' },
  { value: '🚗', label: 'Carrocería', emoji: '🚗' },
  { value: '🔍', label: 'Filtros', emoji: '🔍' },
  { value: '🛢️', label: 'Lubricantes', emoji: '🛢️' },
  { value: '🛞', label: 'Neumáticos', emoji: '🛞' },
  { value: '💡', label: 'Iluminación', emoji: '💡' },
  { value: '📦', label: 'General', emoji: '📦' },
  { value: '🔩', label: 'Tornillería', emoji: '🔩' },
  { value: '🌡️', label: 'Refrigeración', emoji: '🌡️' },
  { value: '🎵', label: 'Audio', emoji: '🎵' },
  { value: '🛡️', label: 'Seguridad', emoji: '🛡️' }
]

const createCategory = async () => {
  try {
    creating.value = true
    errors.value = {}

    const response = await fetch('/categories/store-ajax', {
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
        errors.value = { general: data.message || 'Error al crear la categoría' }
      }
      return
    }

    // Emitir el evento con la nueva categoría
    emit('created', data.category || data)

  } catch (error) {
    console.error('Error creating category:', error)
    errors.value = { general: 'Error de conexión. Inténtalo de nuevo.' }
  } finally {
    creating.value = false
  }
}
</script>
