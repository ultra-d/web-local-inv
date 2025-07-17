<template>
  <div>
    <!-- Controles de vista -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500">
          {{ parts.length }} repuestos encontrados
        </span>
        <select
          v-model="sortBy"
          @change="sortParts"
          class="border border-gray-300 rounded-md px-3 py-1 text-sm"
        >
          <option value="name">Ordenar por nombre</option>
          <option value="price">Ordenar por precio</option>
          <option value="stock">Ordenar por stock</option>
          <option value="popularity">Ordenar por popularidad</option>
        </select>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="viewMode = 'grid'"
          :class="viewMode === 'grid' ? 'bg-blue-100 text-blue-600' : 'text-gray-400'"
          class="p-2 rounded-md hover:bg-gray-100"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
        </button>
        <button
          @click="viewMode = 'list'"
          :class="viewMode === 'list' ? 'bg-blue-100 text-blue-600' : 'text-gray-400'"
          class="p-2 rounded-md hover:bg-gray-100"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Vista en cuadrícula -->
    <div
      v-if="viewMode === 'grid'"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
    >
      <PartCard
        v-for="part in sortedParts"
        :key="part.id"
        :part="part"
        @view-details="$emit('view-details', $event)"
        @add-to-favorites="$emit('add-to-favorites', $event)"
      />
    </div>

    <!-- Vista en lista -->
    <div v-else class="space-y-4">
      <div
        v-for="part in sortedParts"
        :key="part.id"
        class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-6">
          <!-- Imagen pequeña -->
          <img
            v-if="part.image_url"
            :src="part.image_url"
            :alt="part.name"
            class="w-16 h-16 object-cover rounded-lg"
          />
          <div v-else class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
            <span class="text-gray-400">🔧</span>
          </div>

          <!-- Información principal -->
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900">{{ part.name }}</h3>
            <p class="text-sm text-gray-500">{{ part.part_number }}</p>
            <div class="flex items-center gap-4 mt-2">
              <span class="text-sm text-gray-600">{{ part.model?.name }}</span>
              <span class="text-sm text-gray-600">{{ part.category?.name }}</span>
            </div>
          </div>

          <!-- Precio y acciones -->
          <div class="text-right">
            <p class="text-xl font-bold text-gray-900">{{ part.formatted_price }}</p>
            <p class="text-sm text-gray-500">Stock: {{ part.stock_quantity }}</p>
            <div class="flex gap-2 mt-2">
              <button
                @click="$emit('view-details', part)"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm"
              >
                Ver detalles
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado vacío -->
    <div
      v-if="parts.length === 0"
      class="text-center py-12"
    >
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H7a1 1 0 00-1 1v1m14 0h-1M5 5h1m0 0v.01M5 19h.01" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No hay repuestos</h3>
      <p class="mt-1 text-sm text-gray-500">
        No se encontraron repuestos que coincidan con los criterios de búsqueda.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import PartCard from './PartCard.vue'

const emit = defineEmits(['view-details', 'add-to-favorites'])

const props = defineProps({
  parts: {
    type: Array,
    default: () => []
  }
})

const viewMode = ref('grid')
const sortBy = ref('name')

const sortedParts = computed(() => {
  const sorted = [...props.parts]

  switch (sortBy.value) {
    case 'price':
      return sorted.sort((a, b) => (a.price || 0) - (b.price || 0))
    case 'stock':
      return sorted.sort((a, b) => b.stock_quantity - a.stock_quantity)
    case 'popularity':
      return sorted.sort((a, b) => b.view_count - a.view_count)
    default:
      return sorted.sort((a, b) => a.name.localeCompare(b.name))
  }
})

const sortParts = () => {
  // La reactividad se encarga automáticamente a través del computed
}
</script>
