<template>
  <Head title="Tareas" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <h2 class="text-xl font-semibold leading-tight text-secondary-800 dark:text-secondary-100">Tareas</h2>
          <button
            @click="manualRefresh"
            :disabled="isRefreshing"
            class="rounded-lg p-2 text-secondary-600 transition-all hover:bg-secondary-100 hover:text-secondary-900 disabled:opacity-50 dark:text-secondary-400 dark:hover:bg-secondary-700 dark:hover:text-secondary-100"
            :class="{ 'animate-spin': isRefreshing }"
            title="Actualizar tareas"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>
        <Link
          :href="route('tasks.create')"
          class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600"
        >
          <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Nueva Tarea
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
          <div class="rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Creadas</p>
                <p class="mt-1 text-2xl font-semibold text-secondary-900 dark:text-secondary-100">{{ stats.creada }}</p>
              </div>
              <div class="rounded-full bg-gray-100 p-3 dark:bg-gray-900/30">
                <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Analizadas</p>
                <p class="mt-1 text-2xl font-semibold text-blue-900 dark:text-blue-400">{{ stats.analizada }}</p>
              </div>
              <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Programadas</p>
                <p class="mt-1 text-2xl font-semibold text-purple-900 dark:text-purple-400">{{ stats.programada }}</p>
              </div>
              <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/30">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">En Progreso</p>
                <p class="mt-1 text-2xl font-semibold text-yellow-900 dark:text-yellow-400">{{ stats.en_progreso }}</p>
              </div>
              <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/30">
                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Finalizadas</p>
                <p class="mt-1 text-2xl font-semibold text-green-900 dark:text-green-400">{{ stats.finalizada }}</p>
              </div>
              <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Canceladas</p>
                <p class="mt-1 text-2xl font-semibold text-red-900 dark:text-red-400">{{ stats.cancelada }}</p>
              </div>
              <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/30">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
            </div>
          </div>

          <div class="rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Vencidas</p>
                <p class="mt-1 text-2xl font-semibold text-orange-900 dark:text-orange-400">{{ stats.overdue }}</p>
              </div>
              <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900/30">
                <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-secondary-800">
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Search -->
            <div>
              <label for="search" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">Buscar</label>
              <input
                id="search"
                v-model="filters.search"
                type="text"
                placeholder="Título o descripción..."
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
                @input="debouncedFilter"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label for="status" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">Estado</label>
              <select
                id="status"
                v-model="filters.status"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
                @change="applyFilters"
              >
                <option value="">Todos los estados</option>
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Priority Filter -->
            <div>
              <label for="priority" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">Prioridad</label>
              <select
                id="priority"
                v-model="filters.priority"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
                @change="applyFilters"
              >
                <option value="">Todas las prioridades</option>
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Board Filter -->
            <div>
              <label for="board_id" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">Tablero</label>
              <select
                id="board_id"
                v-model="filters.board_id"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
                @change="applyFilters"
              >
                <option value="">Todos los tableros</option>
                <option v-for="board in boards" :key="board.id" :value="board.id">
                  {{ board.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Additional Filters -->
          <div class="mt-4 flex flex-wrap items-center gap-4">
            <label class="flex items-center">
              <input
                v-model="filters.show_completed"
                type="checkbox"
                class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500 dark:border-secondary-600 dark:bg-secondary-700"
                @change="applyFilters"
              />
              <span class="ml-2 text-sm text-secondary-700 dark:text-secondary-300">Mostrar completadas</span>
            </label>
            <label class="flex items-center">
              <input
                v-model="filters.show_overdue"
                type="checkbox"
                class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500 dark:border-secondary-600 dark:bg-secondary-700"
                @change="applyFilters"
              />
              <span class="ml-2 text-sm text-secondary-700 dark:text-secondary-300">Solo vencidas</span>
            </label>
          </div>

          <!-- Clear Filters -->
          <div v-if="hasActiveFilters" class="mt-4">
            <button
              @click="clearFilters"
              type="button"
              class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
            >
              Limpiar filtros
            </button>
          </div>
        </div>

        <!-- Tasks Grid -->
        <div v-if="tasks.data.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <TaskCard v-for="task in tasks.data" :key="task.id" :task="task" />
        </div>

        <!-- Empty State -->
        <div v-else class="rounded-lg bg-white p-12 text-center shadow dark:bg-secondary-800">
          <svg
            class="mx-auto h-12 w-12 text-secondary-400 dark:text-secondary-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            ></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-secondary-900 dark:text-secondary-100">No hay tareas</h3>
          <p class="mt-1 text-sm text-secondary-500 dark:text-secondary-400">
            {{ hasActiveFilters ? 'No se encontraron resultados con los filtros aplicados.' : 'Comienza creando una nueva tarea.' }}
          </p>
          <div v-if="!hasActiveFilters" class="mt-6">
            <Link
              :href="route('tasks.create')"
              class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600"
            >
              <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Crear Tarea
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="tasks.data.length > 0" class="mt-6">
          <Pagination :links="tasks.links" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TaskCard from '@/Components/TaskCard.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  tasks: Object,
  filters: Object,
  statuses: Object,
  priorities: Object,
  boards: Array,
  stats: Object,
})

// Auto-refresh state
const isRefreshing = ref(false)
const refreshInterval = ref(null)
const REFRESH_INTERVAL = 30000 // 30 segundos

const filters = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  priority: props.filters?.priority || '',
  board_id: props.filters?.board_id || '',
  show_completed: props.filters?.show_completed || false,
  show_overdue: props.filters?.show_overdue || false,
  sort_by: props.filters?.sort_by || 'created_at',
  sort_dir: props.filters?.sort_dir || 'desc',
})

const hasActiveFilters = computed(() => {
  return (
    filters.value.search !== '' ||
    filters.value.status !== '' ||
    filters.value.priority !== '' ||
    filters.value.board_id !== '' ||
    filters.value.show_completed ||
    filters.value.show_overdue
  )
})

const applyFilters = () => {
  router.get(route('tasks.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

let filterTimeout = null
const debouncedFilter = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    priority: '',
    board_id: '',
    show_completed: false,
    show_overdue: false,
    sort_by: 'created_at',
    sort_dir: 'desc',
  }
  applyFilters()
}

// Auto-refresh functions
const refreshTasks = () => {
  if (isRefreshing.value) return

  isRefreshing.value = true

  router.reload({
    preserveState: true,
    preserveScroll: true,
    only: ['tasks', 'stats'],
    onFinish: () => {
      isRefreshing.value = false
    },
  })
}

const manualRefresh = () => {
  refreshTasks()
}

const startAutoRefresh = () => {
  refreshInterval.value = setInterval(() => {
    if (!document.hidden) {
      refreshTasks()
    }
  }, REFRESH_INTERVAL)
}

const stopAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
    refreshInterval.value = null
  }
}

// Lifecycle hooks
onMounted(() => {
  startAutoRefresh()

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopAutoRefresh()
    } else {
      // Refresh inmediatamente al volver a la pestaña
      refreshTasks()
      startAutoRefresh()
    }
  })
})

onUnmounted(() => {
  stopAutoRefresh()
})
</script>
