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
                    v-model="formData.name"
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
                    Marca *
                  </label>
                  <input
                    v-model="formData.brand"
                    type="text"
                    required
                    placeholder="Ej: Bosch, Denso, NGK, Gates"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.brand }"
                  />
                  <p v-if="errors.brand" class="mt-1 text-sm text-red-600">{{ errors.brand }}</p>
                </div>
              </div>
            </section>

            <!-- Códigos del Repuesto -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                  🏷️
                </span>
                Códigos del Repuesto
              </h3>

              <PartCodesInput
                ref="partCodesRef"
                v-model="formData.codes"
                :errors="errors"
              />
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
                      v-model="formData.category_id"
                      required
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                      :class="{ 'border-red-500': errors.category_id }"
                    >
                      <option value="">Seleccionar categoría...</option>
                      <template v-for="category in categories" :key="category.id">
                        <option
                          :value="category.id"
                          class="font-semibold"
                        >
                          📁 {{ category.name }}
                        </option>
                        <option
                          v-for="subcategory in category.children || []"
                          :key="subcategory.id"
                          :value="subcategory.id"
                          class="text-gray-600"
                        >
                          &nbsp;&nbsp;&nbsp;&nbsp;↳ {{ subcategory.name }}
                        </option>
                      </template>
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
                      v-model="formData.model_id"
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

            <!-- Precio -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                  💰
                </span>
                Precio
              </h3>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Precio de Venta *
                  </label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input
                      v-model="formData.price"
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
              </div>
            </section>

            <!-- Descripción -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                  📝
                </span>
                Descripción
              </h3>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Descripción del Repuesto
                </label>
                <textarea
                  v-model="formData.description"
                  rows="4"
                  placeholder="Descripción detallada del repuesto, características técnicas, compatibilidad..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                ></textarea>
              </div>
            </section>

            <!-- Imagen del Repuesto -->
            <section>
              <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center mr-3">
                  📸
                </span>
                Imagen del Repuesto
              </h3>

              <div class="space-y-4">
                <!-- Área de subida de imagen -->
                <div class="flex items-center justify-center w-full">
                  <label
                    v-if="!imagePreview"
                    for="image-upload"
                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors"
                  >
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <svg class="w-8 h-8 mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                      </svg>
                      <p class="mb-2 text-sm text-gray-500">
                        <span class="font-semibold">Clic para subir imagen</span> o arrastra y suelta
                      </p>
                      <p class="text-xs text-gray-500">PNG, JPG, JPEG hasta 5MB</p>
                    </div>
                    <input
                      id="image-upload"
                      type="file"
                      class="hidden"
                      accept="image/*"
                      @change="handleImageUpload"
                    />
                  </label>

                  <!-- Preview de la imagen -->
                  <div v-else class="relative w-full">
                    <img
                      :src="imagePreview"
                      alt="Preview del repuesto"
                      class="w-full h-64 object-cover rounded-lg border border-gray-300"
                    />
                    <button
                      type="button"
                      @click="removeImage"
                      class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors"
                      title="Eliminar imagen"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- Error de imagen -->
                <p v-if="errors.image" class="mt-1 text-sm text-red-600">{{ errors.image }}</p>

                <!-- Información adicional -->
                <div class="text-sm text-gray-500 bg-blue-50 p-3 rounded-md">
                  <p class="font-medium mb-1">💡 Tips para la imagen:</p>
                  <ul class="text-xs space-y-1">
                    <li>• Usa imágenes claras y bien iluminadas</li>
                    <li>• Preferiblemente con fondo blanco o neutro</li>
                    <li>• Tamaño recomendado: 800x600 píxeles o superior</li>
                    <li>• Máximo 5MB por imagen</li>
                  </ul>
                </div>
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
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PartCodesInput from '@/components/PartCodesInput.vue'
import CreateCategoryModal from '@/components/CreateCategoryModal.vue'
import CreateModelModal from '@/components/CreateModelModal.vue'

// Props del controlador
const props = defineProps({
  categories: Array,
  brands: Array,
  models: Array,
  formData: Object
})

//FormData manual
const formData = ref({
  name: '',
  brand: '',
  price: '',
  description: '',
  model_id: '',
  category_id: '',
  codes: [
    {
      code: '',
      type: 'internal',
      is_primary: true,
      is_active: true
    }
  ]
})

const errors = ref({})
const submitting = ref(false)
const imagePreview = ref(null)
const imageFile = ref(null) // ← Nueva variable separada para la imagen
const partCodesRef = ref(null)

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

// 🚀 MÉTODO CORREGIDO PARA ENVÍO DE FORMULARIO
const submitForm = async () => {
  try {
    submitting.value = true
    errors.value = {}

    // Validar códigos antes de enviar
    if (!formData.value.codes.length || !formData.value.codes.some(code => code.code.trim())) {
      errors.value.codes = 'Debe agregar al menos un código'
      submitting.value = false
      return
    }

    // Validar códigos en tiempo real
    if (partCodesRef.value) {
      const codesValid = await partCodesRef.value.validateAllCodes()
      if (!codesValid) {
        errors.value.codes = 'Por favor corrige los errores en los códigos antes de continuar'
        submitting.value = false
        return
      }
    }

    // Limpiar códigos vacíos y asegurar que haya un primary
    const validCodes = formData.value.codes.filter(code => code.code.trim())
    if (!validCodes.some(code => code.is_primary)) {
      validCodes[0].is_primary = true
    }
    formData.value.codes = validCodes

    // 🔧 CREAR FormData manualmente para incluir archivos
    const submitData = new FormData()

    // Agregar campos del formulario
    submitData.append('name', formData.value.name)
    submitData.append('brand', formData.value.brand)
    submitData.append('price', formData.value.price)
    submitData.append('description', formData.value.description || '')
    submitData.append('model_id', formData.value.model_id)
    submitData.append('category_id', formData.value.category_id)

    // Agregar códigos
    formData.value.codes.forEach((code, index) => {
      submitData.append(`codes[${index}][code]`, code.code)
      submitData.append(`codes[${index}][type]`, code.type)
      submitData.append(`codes[${index}][is_primary]`, code.is_primary ? '1' : '0')
      submitData.append(`codes[${index}][is_active]`, code.is_active ? '1' : '0')
    })

    // 🔥 AGREGAR IMAGEN SI EXISTE
    if (imageFile.value) {
      submitData.append('image', imageFile.value)
    }

    // Debug: Verificar FormData
    console.log('📤 Enviando FormData:')
    for (let [key, value] of submitData.entries()) {
      console.log(`${key}:`, value)
    }

    // 🚀 ENVIAR CON ROUTER.POST en lugar de useForm
    router.post('/parts', submitData, {
      preserveScroll: true,
      onSuccess: (page) => {
        console.log('✅ Repuesto creado exitosamente')
        // Redirigir se maneja en el controlador
      },
      onError: (formErrors) => {
        console.error('❌ Errores de validación:', formErrors)
        errors.value = formErrors
      },
      onFinish: () => {
        submitting.value = false
      }
    })

  } catch (error) {
    console.error('❌ Error submitting form:', error)
    submitting.value = false
  }
}

// Métodos para modales
const handleCategoryCreated = (newCategory) => {
  console.log('✅ Nueva categoría creada:', newCategory)

  if (newCategory && newCategory.id) {
    // Si es subcategoría, agregarla al padre correcto
    if (newCategory.parent_id) {
      const parentCategory = props.categories.find(cat => cat.id === newCategory.parent_id)
      if (parentCategory) {
        if (!parentCategory.children) {
          parentCategory.children = []
        }
        parentCategory.children.push(newCategory)
      }
    } else {
      // Es categoría principal
      props.categories.push({
        ...newCategory,
        children: []
      })
    }

    // Seleccionar automáticamente la nueva categoría
    formData.value.category_id = newCategory.id
  }
  showCreateCategory.value = false
}

const handleModelCreated = (newModel) => {
  console.log('✅ Nuevo modelo creado:', newModel)

  if (newModel && newModel.id) {
    props.models.push(newModel)

    formData.value.model_id = newModel.id
  }
  showCreateModel.value = false
}

const handleBrandCreated = (newBrand) => {
  console.log('✅ Nueva marca creada:', newBrand)

  if (newBrand && newBrand.id) {
    props.brands.push(newBrand)
  }
  showCreateBrand.value = false
}

//MANEJO DE IMÁGENES
const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return

  console.log('📸 Archivo seleccionado:', {
    name: file.name,
    size: file.size,
    type: file.type
  })

  if (!file.type.startsWith('image/')) {
    errors.value.image = 'Por favor selecciona un archivo de imagen válido'
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    errors.value.image = 'La imagen no puede ser mayor a 5MB'
    return
  }

  delete errors.value.image

  // 🔥 GUARDAR ARCHIVO E IMAGEN PREVIEW POR SEPARADO
  imageFile.value = file // ← Para el formulario

  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result // ← Para mostrar preview
  }
  reader.readAsDataURL(file)

  console.log('✅ Imagen cargada correctamente')
}

const removeImage = () => {
  imagePreview.value = null
  imageFile.value = null
  delete errors.value.image

  const input = document.getElementById('image-upload')
  if (input) input.value = ''
}
</script>
