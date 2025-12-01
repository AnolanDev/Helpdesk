<template>
  <div class="flex flex-col items-center justify-center py-12 px-4">
    <div class="relative">
      <!-- Círculo de fondo decorativo -->
      <div class="absolute inset-0 -m-8">
        <div class="h-32 w-32 rounded-full bg-gradient-to-br from-primary-100 to-primary-50 blur-2xl opacity-50"></div>
      </div>

      <!-- Icono -->
      <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-secondary-100 to-secondary-50 shadow-sm">
        <svg
          v-if="icon === 'inbox'"
          class="h-8 w-8 text-secondary-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>

        <svg
          v-else-if="icon === 'clipboard'"
          class="h-8 w-8 text-secondary-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>

        <svg
          v-else-if="icon === 'search'"
          class="h-8 w-8 text-secondary-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>

        <svg
          v-else-if="icon === 'users'"
          class="h-8 w-8 text-secondary-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>

        <svg
          v-else
          class="h-8 w-8 text-secondary-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </div>
    </div>

    <!-- Título -->
    <h3 class="mt-6 text-base font-semibold text-secondary-900">
      {{ title }}
    </h3>

    <!-- Descripción -->
    <p class="mt-2 max-w-sm text-center text-sm text-secondary-600">
      {{ description }}
    </p>

    <!-- Acción opcional -->
    <div v-if="$slots.action" class="mt-6">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: 'inbox',
    validator: (value) => ['inbox', 'clipboard', 'search', 'users'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  }
})
</script>
