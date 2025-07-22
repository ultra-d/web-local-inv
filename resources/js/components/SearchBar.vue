<template>
  <div class="relative max-w-lg w-full">
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <input
        v-model="searchQuery"
        @input="handleInput"
        @keyup.enter="handleSearch"
        type="text"
        placeholder="Buscar repuestos, códigos, marcas..."
        class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />
      <div class="absolute inset-y-0 right-0 flex items-center">
        <button
          @click="handleSearch"
          class="p-2 text-gray-400 hover:text-blue-500 focus:outline-none focus:text-blue-500"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Search Results Dropdown -->
    <div
      v-if="showResults && searchResults.length > 0"
      class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 max-h-96 overflow-auto"
    >
      <ul class="py-1">
        <li
          v-for="result in searchResults"
          :key="result.id"
          @click="selectResult(result)"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer flex items-center"
        >
          <img
            v-if="result.image_url"
            :src="result.image_url"
            :alt="result.name"
            class="w-8 h-8 object-cover rounded mr-3"
          />
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">{{ result.name }}</p>
            <p class="text-xs text-gray-500">{{ result.part_number }}</p>
          </div>
          <span v-if="result.category" class="text-xs text-gray-400">{{ result.category.name }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue'

const emit = defineEmits(['search', 'select'])

const searchQuery = ref('')
const searchResults = ref([])
const showResults = ref(false)
const searchTimeout = ref(null)
const isSearching = ref(false)

const handleInput = () => {
  clearTimeout(searchTimeout.value)

  if (searchQuery.value.length > 2) {
    searchTimeout.value = setTimeout(() => {
      performSearch()
    }, 300)
  } else {
    searchResults.value = []
    showResults.value = false
  }
}

const performSearch = async () => {
  if (isSearching.value) return // Prevenir múltiples búsquedas simultáneas

  try {
    isSearching.value = true
    const response = await fetch(`/api/search?q=${encodeURIComponent(searchQuery.value)}`)

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    // CORRECCIÓN: data ya es un array, no tiene propiedad .results
    searchResults.value = Array.isArray(data) ? data : []
    showResults.value = true
  } catch (error) {
    console.error('Search error:', error)
    searchResults.value = []
    showResults.value = false
  } finally {
    isSearching.value = false
  }
}

const handleSearch = () => {
  emit('search', searchQuery.value)
  showResults.value = false
}

const selectResult = (result) => {
  emit('select', result)
  searchQuery.value = result.name
  showResults.value = false
}

const closeResults = (event) => {
  // Mejorada la detección del click fuera
  const searchContainer = event.target.closest('.relative.max-w-lg')
  if (!searchContainer) {
    showResults.value = false
  }
}

// Cerrar resultados al hacer clic fuera - CORREGIDO
watch(showResults, (value) => {
  if (value) {
    setTimeout(() => {
      document.addEventListener('click', closeResults)
    }, 100) // Pequeño delay para evitar que se cierre inmediatamente
  } else {
    document.removeEventListener('click', closeResults)
  }
})

// Cleanup al desmontar el componente
onUnmounted(() => {
  document.removeEventListener('click', closeResults)
  clearTimeout(searchTimeout.value)
})
</script>
