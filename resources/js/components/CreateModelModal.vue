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
      <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg z-10">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
              🚗
            </span>
            Nuevo Modelo de Vehículo
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

        <form @submit.prevent="createModel" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nombre del Modelo *
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Ej: Corolla, Civic, Sentra..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                :class="{ 'border-red-500': errors.name }"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Código del Modelo *
              </label>
              <input
                v-model="form.code"
                type="text"
                required
                placeholder="Ej: COR-2020, CIV-2019..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                :class="{ 'border-red-500': errors.code }"
              />
              <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Marca *
            </label>
            <div class="flex space-x-2">
              <select
                v-model="form.brand_id"
                required
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                :class="{ 'border-red-500': errors.brand_id }"
              >
                <option value="">Seleccionar marca...</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.name }}
                </option>
              </select>
              <button
                type="button"
                @click="showCreateBrand = true"
                class="px-3 py-2 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition-colors"
                title="Crear nueva marca"
              >
                +
              </button>
            </div>
            <p v-if="errors.brand_id" class="mt-1 text-sm text-red-600">{{ errors.brand_id }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Año Desde
              </label>
              <input
                v-model="form.year_from"
                type="number"
                :min="1900"
                :max="currentYear + 5"
                placeholder="2020"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                :class="{ 'border-red-500': errors.year_from }"
              />
              <p v-if="errors.year_from" class="mt-1 text-sm text-red-600">{{ errors.year_from }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Año Hasta
              </label>
              <input
                v-model="form.year_to"
                type="number"
                :min="1900"
                :max="currentYear + 5"
                placeholder="2024"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-gray-900"
                :class="{ 'border-red-500': errors.year_to }"
              />
              <p v-if="errors.year_to" class="mt-1 text-sm text-red-600">{{ errors.year_to }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Descripción
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Descripción opcional del modelo..."
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
              class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors disabled:opacity-50"
            >
              <span v-if="creating">Creando...</span>
              <span v-else>Crear Modelo</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal anidado para crear marca -->
  <CreateBrandModal
    v-if="showCreateBrand"
    @close="showCreateBrand = false"
    @created="handleBrandCreated"
  />
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import CreateBrandModal from './CreateBrandModal.vue'

const props = defineProps({
  brands: Array
})

const emit = defineEmits(['close', 'created'])

const form = reactive({
  name: '',
  code: '',
  brand_id: '',
  year_from: '',
  year_to: '',
  description: ''
})

const errors = ref({})
const creating = ref(false)
const showCreateBrand = ref(false)

const currentYear = computed(() => new Date().getFullYear())

const createModel = async () => {
  try {
    creating.value = true
    errors.value = {}

    const response = await fetch('/models/store-ajax', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form)
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        errors.value = data.errors
      } else {
        errors.value = { general: data.message || 'Error al crear el modelo' }
      }
      creating.value = false
      return
    }

    // ✅ Solo cerrar si todo salió bien
    emit('created', data.model || data)

  } catch (error) {
    console.error('Error creating model:', error)
    errors.value = { general: 'Error de conexión: ' + error.message }
    creating.value = false
  }
}

const handleBrandCreated = (newBrand) => {
  props.brands.push(newBrand)
  form.brand_id = newBrand.id
  showCreateBrand.value = false
}
</script>
