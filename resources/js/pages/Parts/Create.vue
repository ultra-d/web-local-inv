<template>
  <div class="min-h-screen bg-gray-50">
    <AppLayout title="Crear Repuesto">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center space-x-4">
            <button
              @click="router.get('/parts')"
              class="text-gray-500 hover:text-gray-700"
            >
              ← Volver a repuestos
            </button>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo Repuesto</h1>
              <p class="mt-2 text-gray-600">Agrega un nuevo repuesto al inventario</p>
            </div>
          </div>
        </div>

        <!-- Formulario -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <form @submit.prevent="submitForm" class="space-y-8 p-8">

            <!-- Información Básica -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                  🔧
                </span>
                Información Básica
              </h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del Repuesto *
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="Ej: Filtro de aceite motor 2.5L"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.name }"
                  />
                  <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Código del Repuesto *
                  </label>
                  <input
                    v-model="form.part_number"
                    type="text"
                    required
                    placeholder="Ej: FO-001"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.part_number }"
                  />
                  <p v-if="errors.part_number" class="mt-1 text-sm text-red-600">{{ errors.part_number }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Código Original
                  </label>
                  <input
                    v-model="form.original_code"
                    type="text"
                    placeholder="Ej: TOY-FO-001"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Marca *
                  </label>
                  <input
                    v-model="form.brand"
                    type="text"
                    required
                    placeholder="Ej: Toyota, Honda, etc."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.brand }"
                  />
                  <p v-if="errors.brand" class="mt-1 text-sm text-red-600">{{ errors.brand }}</p>
                </div>
              </div>
            </section>

            <!-- Clasificación -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                  📁
                </span>
                Clasificación
              </h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Categoría con opción de crear -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Categoría *
                  </label>
                  <div class="flex space-x-2">
                    <select
                      v-model="form.category_id"
                      required
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': errors.category_id }"
                    >
                      <option value="">Seleccionar categoría...</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <button
                      type="button"
                      @click="showCreateCategory = true"
                      class="px-3 py-2 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors"
                      title="Crear nueva categoría"
                    >
                      +
                    </button>
                  </div>
                  <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                </div>

                <!-- Modelo con opción de crear -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Modelo de Vehículo *
                  </label>
                  <div class="flex space-x-2">
                    <select
                      v-model="form.model_id"
                      required
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': errors.model_id }"
                    >
                      <option value="">Seleccionar modelo...</option>
                      <optgroup v-for="brand in groupedModels" :key="brand.name" :label="brand.name">
                        <option v-for="model in brand.models" :key="model.id" :value="model.id">
                          {{ model.name }} {{ model.code }}
                        </option>
                      </optgroup>
                    </select>
                    <button
                      type="button"
                      @click="showCreateModel = true"
                      class="px-3 py-2 bg-purple-100 text-purple-700 rounded-md hover:bg-purple-200 transition-colors"
                      title="Crear nuevo modelo"
                    >
                      +
                    </button>
                  </div>
                  <p v-if="errors.model_id" class="mt-1 text-sm text-red-600">{{ errors.model_id }}</p>
                </div>
              </div>
            </section>

            <!-- Inventario y Precios -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                  💰
                </span>
                Inventario y Precios
              </h3>

              <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Precio *
                  </label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input
                      v-model="form.price"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      placeholder="0.00"
                      class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': errors.price }"
                    />
                  </div>
                  <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Stock Actual *
                  </label>
                  <input
                    v-model="form.stock_quantity"
                    type="number"
                    min="0"
                    required
                    placeholder="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.stock_quantity }"
                  />
                  <p v-if="errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ errors.stock_quantity }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Stock Mínimo *
                  </label>
                  <input
                    v-model="form.min_stock"
                    type="number"
                    min="0"
                    required
                    placeholder="5"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.min_stock }"
                  />
                  <p v-if="errors.min_stock" class="mt-1 text-sm text-red-600">{{ errors.min_stock }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Ubicación
                  </label>
                  <input
                    v-model="form.location"
                    type="text"
                    placeholder="Ej: A-1-3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>
            </section>

            <!-- Descripción y Notas -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                  📝
                </span>
                Descripción y Notas
              </h3>

              <div class="grid grid-cols-1 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Descripción detallada del repuesto..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notas Internas
                  </label>
                  <textarea
                    v-model="form.notes"
                    rows="2"
                    placeholder="Notas para uso interno..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>
              </div>
            </section>

            <!-- Opciones -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                  ⚙️
                </span>
                Opciones
              </h3>

              <div class="space-y-4">
                <label class="flex items-center">
                  <input
                    v-model="form.is_bestseller"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">
                    Marcar como bestseller ⭐
                  </span>
                </label>

                <label class="flex items-center">
                  <input
                    v-model="form.is_available"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">
                    Disponible para venta ✅
                  </span>
                </label>
              </div>
            </section>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
              <button
                type="button"
                @click="router.get('/parts')"
                class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
              >
                <span v-if="submitting">Creando...</span>
                <span v-else>Crear Repuesto</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </AppLayout>

    <!-- Modal Crear Categoría -->
    <CreateCategoryModal
      v-if="showCreateCategory"
      :categories="categories"
      @close="showCreateCategory = false"
      @created="handleCategoryCreated"
    />

    <!-- Modal Crear Modelo -->
    <CreateModelModal
      v-if="showCreateModel"
      :brands="brands"
      @close="showCreateModel = false"
      @created="handleModelCreated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import CreateCategoryModal from '@/components/CreateCategoryModal.vue'
import CreateModelModal from '@/components/CreateModelModal.vue'

// Props del controlador
const props = defineProps({
  categories: Array,
  brands: Array,
  models: Array,
  formData: Object
})

// Form reactivo
const form = useForm(props.formData)
const errors = ref({})
const submitting = ref(false)

// Estados de modales
const showCreateCategory = ref(false)
const showCreateModel = ref(false)

// Computed para agrupar modelos por marca
const groupedModels = computed(() => {
  const grouped = {}

  props.models.forEach(model => {
    const brandName = model.brand.name
    if (!grouped[brandName]) {
      grouped[brandName] = {
        name: brandName,
        models: []
      }
    }
    grouped[brandName].models.push(model)
  })

  return Object.values(grouped)
})

// Métodos
const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    form.post('/parts', {
      onSuccess: () => {
        // Redirigir se maneja en el controlador
      },
      onError: (formErrors) => {
        errors.value = formErrors
      },
      onFinish: () => {
        submitting.value = false
      }
    })
  } catch (error) {
    console.error('Error submitting form:', error)
    submitting.value = false
  }
}

const handleCategoryCreated = (newCategory) => {
  props.categories.push(newCategory)
  form.category_id = newCategory.id
  showCreateCategory.value = false
}

const handleModelCreated = (newModel) => {
  props.models.push(newModel)
  form.model_id = newModel.id
  showCreateModel.value = false
}
</script>
