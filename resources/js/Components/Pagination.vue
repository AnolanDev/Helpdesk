<template>
  <nav v-if="links.length > 3" class="flex items-center justify-between rounded-lg bg-white px-4 py-3 shadow sm:px-6">
    <div class="flex flex-1 justify-between sm:hidden">
      <Link
        v-if="links[0].url"
        :href="links[0].url"
        class="relative inline-flex items-center rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-medium text-secondary-700 hover:bg-secondary-50"
      >
        Anterior
      </Link>
      <span v-else class="relative inline-flex items-center rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-medium text-secondary-300">
        Anterior
      </span>

      <Link
        v-if="links[links.length - 1].url"
        :href="links[links.length - 1].url"
        class="relative ml-3 inline-flex items-center rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-medium text-secondary-700 hover:bg-secondary-50"
      >
        Siguiente
      </Link>
      <span v-else class="relative ml-3 inline-flex items-center rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-medium text-secondary-300">
        Siguiente
      </span>
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-secondary-700">
          Mostrando
          <span class="font-medium">{{ meta.from }}</span>
          a
          <span class="font-medium">{{ meta.to }}</span>
          de
          <span class="font-medium">{{ meta.total }}</span>
          resultados
        </p>
      </div>
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <template v-for="(link, index) in links" :key="index">
            <!-- Previous Button -->
            <Link
              v-if="index === 0 && link.url"
              :href="link.url"
              class="relative inline-flex items-center rounded-l-md border border-secondary-300 bg-white px-2 py-2 text-sm font-medium text-secondary-500 hover:bg-secondary-50 focus:z-20"
            >
              <span class="sr-only">Anterior</span>
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>
            <span
              v-else-if="index === 0"
              class="relative inline-flex items-center rounded-l-md border border-secondary-300 bg-white px-2 py-2 text-sm font-medium text-secondary-300"
            >
              <span class="sr-only">Anterior</span>
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </span>

            <!-- Next Button -->
            <Link
              v-else-if="index === links.length - 1 && link.url"
              :href="link.url"
              class="relative inline-flex items-center rounded-r-md border border-secondary-300 bg-white px-2 py-2 text-sm font-medium text-secondary-500 hover:bg-secondary-50 focus:z-20"
            >
              <span class="sr-only">Siguiente</span>
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Link>
            <span
              v-else-if="index === links.length - 1"
              class="relative inline-flex items-center rounded-r-md border border-secondary-300 bg-white px-2 py-2 text-sm font-medium text-secondary-300"
            >
              <span class="sr-only">Siguiente</span>
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </span>

            <!-- Page Numbers -->
            <Link
              v-else-if="link.url"
              :href="link.url"
              :class="[
                'relative inline-flex items-center border px-4 py-2 text-sm font-medium focus:z-20',
                link.active
                  ? 'z-10 border-blue-500 bg-blue-50 text-blue-600'
                  : 'border-secondary-300 bg-white text-secondary-700 hover:bg-secondary-50'
              ]"
              v-html="link.label"
            />
            <span
              v-else
              class="relative inline-flex items-center border border-secondary-300 bg-white px-4 py-2 text-sm font-medium text-secondary-700"
              v-html="link.label"
            />
          </template>
        </nav>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  links: {
    type: Array,
    required: true,
  },
})

const meta = computed(() => {
  // Extract meta information from links if available
  // This assumes Laravel's standard pagination structure
  const firstLink = props.links[1] // Skip the "previous" link
  const lastLink = props.links[props.links.length - 2] // Skip the "next" link

  // Try to parse from link labels or provide defaults
  const activeLink = props.links.find(link => link.active)
  const currentPage = activeLink ? parseInt(activeLink.label) : 1
  const lastPage = lastLink && !isNaN(parseInt(lastLink.label)) ? parseInt(lastLink.label) : 1

  // These would normally come from the backend, but we'll estimate
  const perPage = 15 // Default per page
  const total = lastPage * perPage // Estimated total
  const from = (currentPage - 1) * perPage + 1
  const to = Math.min(currentPage * perPage, total)

  return {
    from,
    to,
    total,
  }
})
</script>
