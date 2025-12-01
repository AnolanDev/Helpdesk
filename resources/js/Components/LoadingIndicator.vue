<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="isLoading" class="fixed top-0 left-0 right-0 z-[100]">
      <!-- Barra de progreso -->
      <div class="h-1 bg-gradient-to-r from-primary-600 via-primary-400 to-primary-600 animate-pulse shadow-lg shadow-primary-600/50">
        <div class="h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-50 animate-shimmer"></div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const isLoading = ref(false)

onMounted(() => {
  router.on('start', () => {
    isLoading.value = true
  })

  router.on('finish', () => {
    // Pequeño delay para que la animación se vea mejor
    setTimeout(() => {
      isLoading.value = false
    }, 200)
  })
})
</script>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 1s infinite;
}
</style>
