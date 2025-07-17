<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
    <!-- Imagen del repuesto -->
    <div class="aspect-w-16 aspect-h-9 bg-gray-100">
      <img
        v-if="part.image_url"
        :src="part.image_url"
        :alt="part.name"
        class="w-full h-48 object-cover"
      />
      <div
        v-else
        class="w-full h-48 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200"
      >
        <span class="text-4xl text-gray-400">🔧</span>
      </div>
    </div>

    <!-- Contenido de la tarjeta -->
    <div class="p-6">
      <!-- Header con nombre y código -->
      <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
          {{ part.name }}
        </h3>
        <p class="text-sm text-gray-500">
          Código: <span class="font-mono">{{ part.part_number }}</span>
        </p>
      </div>

      <!-- Tags y badges -->
      <div class="flex flex-wrap gap-2 mb-4">
        <span
          v-if="part.is_bestseller"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
        >
          ⭐ Más vendido
        </span>
        <span
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
          :class="stockStatusClasses"
        >
          {{ stockStatusText }}
        </span>
      </div>

      <!-- Información del modelo y categoría -->
      <div class="space-y-2 mb-4">
        <div class="flex items-center text-sm text-gray-600">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h2M9 7h6m-6 4h6m-6 4h3" />
          </svg>
          {{ part.model?.name }} {{ part.model?.code }}
        </div>
        <div class="flex items-center text-sm text-gray-600">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
          {{ part.category?.name }}
        </div>
      </div>

      <!-- Precio y stock -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <p class="text-2xl font-bold text-gray-900">
            {{ part.formatted_price }}
          </p>
          <p class="text-sm text-gray-500">
            Stock: {{ part.stock_quantity }}
          </p>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-500 mb-1">Ubicación</p>
          <p class="text-sm font-medium text-gray-900">
            {{ part.location || 'N/A' }}
          </p>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="flex gap-2">
        <button
          @click="$emit('view-details', part)"
          class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium"
        >
          Ver detalles
        </button>
        <button
          @click="$emit('add-to-favorites', part)"
          class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          ❤️
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const emit = defineEmits(['view-details', 'add-to-favorites'])

const props = defineProps({
  part: {
    type: Object,
    required: true
  }
})

const stockStatusClasses = computed(() => {
  switch (props.part.stock_status) {
    case 'out_of_stock':
      return 'bg-red-100 text-red-800'
    case 'low_stock':
      return 'bg-yellow-100 text-yellow-800'
    case 'in_stock':
      return 'bg-green-100 text-green-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
})

const stockStatusText = computed(() => {
  switch (props.part.stock_status) {
    case 'out_of_stock':
      return 'Sin stock'
    case 'low_stock':
      return 'Stock bajo'
    case 'in_stock':
      return 'Disponible'
    default:
      return 'Desconocido'
  }
})
</script>
