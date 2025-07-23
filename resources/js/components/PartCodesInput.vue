<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <label class="form-label">
        Códigos del Repuesto *
      </label>
      <button
        type="button"
        @click="addCode"
        class="text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded-md hover:bg-blue-100 transition-colors flex items-center space-x-1"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Agregar código</span>
      </button>
    </div>

    <div class="space-y-3">
      <div
        v-for="(code, index) in modelValue"
        :key="index"
        class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg border"
        :class="{ 'bg-blue-50 border-blue-200': code.is_primary }"
      >
        <!-- Tipo de código -->
        <div class="flex-shrink-0 w-32">
          <select
            v-model="code.type"
            @change="updateCode(index, 'type', $event.target.value)"
            class="form-select text-sm"
          >
            <option value="internal">🏢 Interno</option>
            <option value="oem">⚙️ OEM</option>
            <option value="aftermarket">🔧 Aftermarket</option>
            <option value="universal">🌐 Universal</option>
          </select>
        </div>

        <!-- Código -->
        <div class="flex-1">
          <input
            v-model="code.code"
            @input="updateCode(index, 'code', $event.target.value)"
            @blur="validateCode(index)"
            type="text"
            :placeholder="getPlaceholder(code.type)"
            class="form-input"
            :class="{
              'error': hasError(index, 'code'),
              'border-red-500': codeValidationErrors[index],
              'border-green-500': code.code && !codeValidationErrors[index] && codeValidationChecked[index]
            }"
            required
          />
          <p v-if="hasError(index, 'code')" class="form-error">
            {{ getError(index, 'code') }}
          </p>
          <p v-if="codeValidationErrors[index]" class="form-error">
            {{ codeValidationErrors[index] }}
          </p>
          <p v-if="code.code && !codeValidationErrors[index] && codeValidationChecked[index]" class="mt-1 text-sm text-green-600">
            ✅ Código disponible
          </p>
        </div>

        <!-- Opciones -->
        <div class="flex items-center space-x-2">
          <!-- Primary -->
          <label class="flex items-center text-sm" :title="code.is_primary ? 'Código principal' : 'Marcar como principal'">
            <input
              type="radio"
              :checked="code.is_primary"
              @change="setPrimary(index)"
              class="form-radio mr-1"
            />
            <span class="text-xs text-gray-600">Principal</span>
          </label>

          <!-- Eliminar -->
          <button
            v-if="modelValue.length > 1"
            type="button"
            @click="removeCode(index)"
            class="text-red-600 hover:text-red-800 p-1"
            title="Eliminar código"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Ayuda -->
    <div class="text-sm text-gray-500 bg-blue-50 p-3 rounded-md">
      <p class="font-medium mb-1">💡 Tipos de códigos:</p>
      <ul class="text-xs space-y-1">
        <li><strong>Interno:</strong> Código de tu inventario (ej: REP-001)</li>
        <li><strong>OEM:</strong> Código del fabricante original del repuesto (ej: PH3593A)</li>
        <li><strong>Aftermarket:</strong> Código de fabricante alternativo (ej: K&N-33-2131)</li>
        <li><strong>Universal:</strong> Código genérico (ej: FILTER-OIL-2.5L)</li>
      </ul>
    </div>

    <!-- Errores generales -->
    <div v-if="errors && errors.codes" class="form-error">
      {{ errors.codes }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [
      {
        code: '',
        type: 'internal',
        is_primary: true,
        is_active: true
      }
    ]
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

// Estados para validación en tiempo real
const codeValidationErrors = ref({})
const codeValidationChecked = ref({})
const validatingCodes = ref({})

// Métodos
const updateCode = (index, field, value) => {
  const newCodes = [...props.modelValue]
  newCodes[index][field] = value

  // Limpiar validación anterior si se está editando el código
  if (field === 'code') {
    delete codeValidationErrors.value[index]
    delete codeValidationChecked.value[index]
  }

  emit('update:modelValue', newCodes)
}

const validateCode = async (index) => {
  const code = props.modelValue[index]?.code?.trim()

  if (!code) {
    delete codeValidationErrors.value[index]
    delete codeValidationChecked.value[index]
    return
  }

  // Verificar duplicados en el mismo formulario
  const allCodes = props.modelValue.map(c => c.code?.trim()).filter(Boolean)
  const duplicateInForm = allCodes.filter(c => c === code).length > 1

  if (duplicateInForm) {
    codeValidationErrors.value[index] = 'Código duplicado en el formulario'
    delete codeValidationChecked.value[index]
    return
  }

  // Validar en el servidor
  try {
    validatingCodes.value[index] = true

    const response = await fetch('/api/validate-part-code', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({
        code: code
      })
    })

    const result = await response.json()

    if (result.exists) {
      codeValidationErrors.value[index] = `${result.message} en: ${result.used_in}`
      delete codeValidationChecked.value[index]
    } else {
      delete codeValidationErrors.value[index]
      codeValidationChecked.value[index] = true
    }
  } catch (error) {
    console.error('Error validating code:', error)
    codeValidationErrors.value[index] = 'Error al validar código'
  } finally {
    delete validatingCodes.value[index]
  }
}

const addCode = () => {
  const newCodes = [...props.modelValue, {
    code: '',
    type: 'internal',
    is_primary: false,
    is_active: true
  }]
  emit('update:modelValue', newCodes)
}

const removeCode = (index) => {
  if (props.modelValue.length <= 1) return

  const newCodes = props.modelValue.filter((_, i) => i !== index)

  // Si eliminamos el primary, hacer primary al primero
  if (props.modelValue[index].is_primary && newCodes.length > 0) {
    newCodes[0].is_primary = true
  }

  // Limpiar validaciones del índice eliminado
  delete codeValidationErrors.value[index]
  delete codeValidationChecked.value[index]

  emit('update:modelValue', newCodes)
}

const setPrimary = (index) => {
  const newCodes = props.modelValue.map((code, i) => ({
    ...code,
    is_primary: i === index
  }))
  emit('update:modelValue', newCodes)
}

const getPlaceholder = (type) => {
  switch (type) {
    case 'internal': return 'REP-001, FO-123'
    case 'oem': return 'PH3593A, W712/52'
    case 'aftermarket': return 'K&N-33-2131, FRAM-CA10467'
    case 'universal': return 'FILTER-OIL-2.5L'
    default: return 'Ingresa el código...'
  }
}

const hasError = (index, field) => {
  return props.errors && props.errors[`codes.${index}.${field}`]
}

const getError = (index, field) => {
  return props.errors ? props.errors[`codes.${index}.${field}`] : null
}

// Computed para validar si hay errores de códigos
const hasCodeValidationErrors = computed(() => {
  return Object.keys(codeValidationErrors.value).length > 0
})

// Exponer el estado de validación al componente padre
defineExpose({
  hasValidationErrors: hasCodeValidationErrors,
  validateAllCodes: async () => {
    for (let i = 0; i < props.modelValue.length; i++) {
      await validateCode(i)
    }
    return !hasCodeValidationErrors.value
  }
})
</script>
