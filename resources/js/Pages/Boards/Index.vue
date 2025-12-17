<template>
  <Head title="Tableros" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-secondary-800">Tableros</h2>
        <Link
          v-if="$page.props.auth.user.tipo_usuario === 'admin'"
          :href="route('boards.create')"
          class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Nuevo Tablero
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow dark:bg-secondary-800 dark:shadow-lg">
          <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <!-- Search -->
            <div>
              <label for="search" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">Buscar</label>
              <input
                id="search"
                v-model="filters.search"
                type="text"
                placeholder="Nombre o descripción..."
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100 dark:placeholder-secondary-400"
                @input="debouncedFilter"
              />
            </div>

            <!-- Sort By -->
            <div>
              <label for="sort_by" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">Ordenar por</label>
              <select
                id="sort_by"
                v-model="filters.sort_by"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
                @change="applyFilters"
              >
                <option value="created_at">Fecha de creación</option>
                <option value="name">Nombre</option>
                <option value="updated_at">Última actualización</option>
              </select>
            </div>

            <!-- Sort Direction -->
            <div>
              <label for="sort_dir" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">Dirección</label>
              <select
                id="sort_dir"
                v-model="filters.sort_dir"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
                @change="applyFilters"
              >
                <option value="desc">Descendente</option>
                <option value="asc">Ascendente</option>
              </select>
            </div>
          </div>

          <!-- Clear Filters -->
          <div v-if="hasActiveFilters" class="mt-4">
            <button
              @click="clearFilters"
              type="button"
              class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300"
            >
              Limpiar filtros
            </button>
          </div>
        </div>

        <!-- Boards Grid -->
        <div v-if="boards.data.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <Link
            v-for="board in boards.data"
            :key="board.id"
            :href="route('boards.show', board.id)"
            class="group relative rounded-lg border border-secondary-200 bg-white p-6 shadow-sm transition-all hover:border-primary-400 hover:shadow-md dark:border-secondary-700 dark:bg-secondary-800 dark:hover:border-primary-500 dark:shadow-lg"
          >
            <!-- Board Header -->
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-secondary-900 group-hover:text-primary-600 dark:text-secondary-100 dark:group-hover:text-primary-400">
                {{ board.name }}
              </h3>
              <p v-if="board.description" class="mt-1 text-sm text-secondary-600 line-clamp-2 dark:text-secondary-400">
                {{ board.description }}
              </p>
            </div>

            <!-- Board Stats -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-secondary-600 dark:text-secondary-400">Total de tareas</span>
                <span class="font-semibold text-secondary-900 dark:text-secondary-100">{{ board.tasks_count }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-secondary-600 dark:text-secondary-400">Completadas</span>
                <span class="font-semibold text-green-600 dark:text-green-400">{{ board.completed_tasks_count }}</span>
              </div>
              <div v-if="board.tasks_count > 0" class="pt-2">
                <div class="mb-1 flex items-center justify-between text-xs">
                  <span class="text-secondary-600 dark:text-secondary-400">Progreso</span>
                  <span class="font-semibold text-secondary-900 dark:text-secondary-100">{{ board.progress_percentage }}%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
                  <div
                    class="h-full rounded-full bg-green-500 transition-all duration-300 dark:bg-green-400"
                    :style="{ width: `${board.progress_percentage}%` }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Board Owner -->
            <div class="mt-4 border-t border-secondary-100 pt-4 text-xs text-secondary-500 dark:border-secondary-700 dark:text-secondary-400">
              <div class="flex items-center justify-between">
                <span>Propietario: {{ board.user.name }}</span>
                <span>{{ formatDate(board.created_at) }}</span>
              </div>
            </div>

            <!-- Edit Button (Admin or Owner) -->
            <div
              v-if="canEdit(board)"
              class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100"
            >
              <Link
                :href="route('boards.edit', board.id)"
                class="rounded-md bg-white p-2 text-secondary-600 shadow-sm hover:text-primary-600 dark:bg-secondary-700 dark:text-secondary-300 dark:hover:text-primary-400"
                @click.stop
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  ></path>
                </svg>
              </Link>
            </div>
          </Link>
        </div>

        <!-- Empty State -->
        <div v-else class="rounded-lg bg-white p-12 text-center shadow dark:bg-secondary-800 dark:shadow-lg">
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
              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"
            ></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-secondary-900 dark:text-secondary-100">No hay tableros</h3>
          <p class="mt-1 text-sm text-secondary-500 dark:text-secondary-400">
            {{ hasActiveFilters ? 'No se encontraron resultados con los filtros aplicados.' : 'Comienza creando un nuevo tablero.' }}
          </p>
          <div v-if="$page.props.auth.user.tipo_usuario === 'admin' && !hasActiveFilters" class="mt-6">
            <Link
              :href="route('boards.create')"
              class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
            >
              <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Crear Tablero
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="boards.data.length > 0" class="mt-6">
          <Pagination :links="boards.links" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const page = usePage()

const props = defineProps({
  boards: Object,
  filters: Object,
})

const filters = ref({
  search: props.filters?.search || '',
  sort_by: props.filters?.sort_by || 'created_at',
  sort_dir: props.filters?.sort_dir || 'desc',
})

const hasActiveFilters = computed(() => {
  return filters.value.search !== ''
})

const canEdit = (board) => {
  const user = page.props.auth.user
  // Solo el propietario puede editar (los admins NO pueden editar tableros de otros)
  return user?.id === board.user_id
}

const applyFilters = () => {
  router.get(route('boards.index'), filters.value, {
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
    sort_by: 'created_at',
    sort_dir: 'desc',
  }
  applyFilters()
}

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>
