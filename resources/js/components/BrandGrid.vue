<template>
  <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
    <div
      v-for="brand in brands"
      :key="brand.id"
      @click="$emit('brand-click', brand)"
      class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all duration-200 cursor-pointer hover:scale-105"
    >
      <div class="flex flex-col items-center text-center">
        <img
          v-if="brand.logo_url"
          :src="brand.logo_url"
          :alt="brand.name"
          class="w-12 h-12 object-contain mb-3"
        />
        <div
          v-else
          class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-3"
        >
          <span class="text-lg font-bold text-gray-400">
            {{ brand.name.charAt(0) }}
          </span>
        </div>
        <h3 class="font-medium text-gray-900 text-sm">{{ brand.name }}</h3>
        <p class="text-xs text-gray-500 mt-1">
          {{ brand.models_count }} modelos
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(['brand-click'])
defineProps({
  brands: {
    type: Array,
    default: () => []
  }
})
</script>
