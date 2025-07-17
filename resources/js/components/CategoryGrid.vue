<template>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <div
      v-for="category in categories"
      :key="category.id"
      @click="$emit('category-click', category)"
      class="group relative bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200 cursor-pointer hover:scale-105"
    >
      <div class="flex flex-col items-center text-center">
        <div
          class="w-16 h-16 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"
          :style="{ backgroundColor: category.color + '20', color: category.color }"
        >
          <span class="text-2xl">{{ category.icon || '🔧' }}</span>
        </div>
        <h3 class="font-semibold text-gray-900 mb-2">{{ category.name }}</h3>
        <p class="text-sm text-gray-500">{{ category.parts_count }} repuestos</p>
      </div>

      <!-- Badge para categorías populares -->
      <div
        v-if="category.is_popular"
        class="absolute -top-2 -right-2 bg-yellow-400 text-yellow-800 text-xs px-2 py-1 rounded-full font-medium"
      >
        Popular
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(['category-click'])
defineProps({
  categories: {
    type: Array,
    default: () => []
  }
})
</script>
