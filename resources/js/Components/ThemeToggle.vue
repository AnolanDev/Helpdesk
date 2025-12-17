<template>
  <button
    @click="toggleTheme"
    type="button"
    class="group relative rounded-lg p-2.5 text-secondary-600 transition-all hover:bg-secondary-100 dark:text-secondary-300 dark:hover:bg-secondary-800"
    :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
  >
    <!-- Sun Icon (Light Mode) -->
    <svg
      v-if="!isDark"
      class="h-5 w-5 transition-transform group-hover:rotate-45"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
      />
    </svg>

    <!-- Moon Icon (Dark Mode) -->
    <svg
      v-else
      class="h-5 w-5 transition-transform group-hover:-rotate-12"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
      />
    </svg>

    <!-- Tooltip -->
    <span
      class="pointer-events-none absolute -bottom-10 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-secondary-900 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 dark:bg-secondary-700"
    >
      {{ isDark ? 'Modo claro' : 'Modo oscuro' }}
    </span>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const isDark = ref(false)

const setTheme = (dark) => {
  isDark.value = dark
  if (dark) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const toggleTheme = () => {
  setTheme(!isDark.value)
}

onMounted(() => {
  // Check localStorage first, then system preference
  const savedTheme = localStorage.getItem('theme')

  if (savedTheme) {
    setTheme(savedTheme === 'dark')
  } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    setTheme(true)
  } else {
    setTheme(false)
  }

  // Listen for system theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
      setTheme(e.matches)
    }
  })
})
</script>
