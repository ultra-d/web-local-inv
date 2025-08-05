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
              ref="nameInput"
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
              <!-- 🔥 SOLO MOSTRAR CATEGORÍAS PRINCIPALES COMO PADRE -->
              <option
                v-for="category in parentCategories"
                :key="category.id"
                :value="category.id"
              >
                📁 {{ category.name }}
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

          <!-- 🔥 MOSTRAR ERRORES DE VALIDACIÓN -->
          <div v-if="errors.general" class="p-3 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-600">{{ errors.general }}</p>
          </div>

          <!-- 🔥 MOSTRAR ÉXITO TEMPORAL -->
          <div v-if="showSuccess" class="p-3 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-600">✅ Categoría creada exitosamente</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
              :disabled="creating"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="creating || !form.name.trim()"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50"
            >
              <span v-if="creating">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creando...
              </span>
              <span v-else>Crear Categoría</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, nextTick, onMounted } from 'vue'

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'created'])

// 🔥 FORMULARIO REACTIVO
const form = reactive({
  name: '',
  parent_id: '',
  color: '#3B82F6',
  icon: '📦'
})

const errors = ref({})
const creating = ref(false)
const showSuccess = ref(false)
const nameInput = ref(null)

// 🔥 COMPUTED PARA CATEGORÍAS PRINCIPALES (solo las que no tienen padre)
const parentCategories = computed(() => {
  return props.categories.filter(category => !category.parent_id)
})

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

// 🔥 FUNCIÓN PARA CREAR CATEGORÍA (AJAX)
const createCategory = async () => {
  try {
    creating.value = true
    errors.value = {}

    console.log('📦 Enviando datos:', form)

    // 🔥 PETICIÓN AJAX QUE NO AFECTA EL FORMULARIO PRINCIPAL
    const response = await fetch('/categories/store-ajax', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: form.name.trim(),
        parent_id: form.parent_id || null,
        color: form.color,
        icon: form.icon
      })
    })

    const data = await response.json()
    console.log('📦 Respuesta del servidor:', data)

    if (!response.ok) {
      if (data.errors) {
        errors.value = data.errors
      } else {
        errors.value = { general: data.message || 'Error al crear la categoría' }
      }
      return
    }

    // 🎉 ÉXITO: Mostrar mensaje y emitir evento
    showSuccess.value = true

    // 🔥 EMITIR LA NUEVA CATEGORÍA AL COMPONENTE PADRE
    const newCategory = data.category || data
    console.log('✅ Emitiendo categoría creada:', newCategory)
    emit('created', newCategory)

    // 🔄 RESETEAR FORMULARIO
    form.name = ''
    form.parent_id = ''
    form.color = '#3B82F6'
    form.icon = '📦'

    // ⏰ CERRAR MODAL DESPUÉS DE 1.5 SEGUNDOS
    setTimeout(() => {
      emit('close')
    }, 1500)

  } catch (error) {
    console.error('❌ Error creating category:', error)
    errors.value = { general: 'Error de conexión. Inténtalo de nuevo.' }
  } finally {
    creating.value = false
  }
}

// 🎯 ENFOCAR INPUT AL MONTAR
onMounted(() => {
  nextTick(() => {
    if (nameInput.value) {
      nameInput.value.focus()
    }
  })
})
</script>

<style scoped>
/* Animación para el spinner */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
