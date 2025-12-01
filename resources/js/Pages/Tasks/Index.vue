<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Mis Tareas</h1>
          <p class="mt-2 text-sm text-secondary-600">
            Gestiona tus tareas y colabora con tu equipo
          </p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="route('tasks.board')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-secondary-100 px-4 py-2.5 text-sm font-semibold text-secondary-700 transition-all hover:bg-secondary-200"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            </svg>
            Vista Tablero
          </Link>
          <Link
            :href="route('tasks.create')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Nueva Tarea
          </Link>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-secondary-200 bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Por Hacer</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.todo }}</p>
            </div>
            <div class="rounded-full bg-gray-100 p-3">
              <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-secondary-200 bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">En Progreso</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.in_progress }}</p>
            </div>
            <div class="rounded-full bg-blue-100 p-3">
              <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-secondary-200 bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">En Revisión</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.in_review }}</p>
            </div>
            <div class="rounded-full bg-yellow-100 p-3">
              <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-secondary-200 bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Vencidas</p>
              <p class="mt-2 text-3xl font-bold text-red-600">{{ stats.overdue }}</p>
            </div>
            <div class="rounded-full bg-red-100 p-3">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-lg border border-secondary-200 bg-white p-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700">Estado</label>
            <select
              v-model="form.status"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option v-for="(label, value) in statuses" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700">Prioridad</label>
            <select
              v-model="form.priority"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="">Todas</option>
              <option v-for="(label, value) in priorities" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700">Asignado a</label>
            <select
              v-model="form.assigned_to"
              @change="applyFilters"
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            >
              <option value="">Todos</option>
              <option value="me">Mis tareas</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700">Buscar</label>
            <input
              v-model="form.search"
              @input="debouncedSearch"
              type="text"
              placeholder="Buscar tareas..."
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
            />
          </div>
        </div>
      </div>

      <!-- Tasks Table -->
      <div class="rounded-lg border border-secondary-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Tarea
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Prioridad
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Asignado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Vencimiento
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-200 bg-white">
              <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-secondary-50">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div>
                      <div class="text-sm font-medium text-secondary-900">{{ task.title }}</div>
                      <div class="text-sm text-secondary-500">{{ task.task_number }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                    :class="{
                      'bg-gray-100 text-gray-800': task.status_color === 'gray',
                      'bg-blue-100 text-blue-800': task.status_color === 'blue',
                      'bg-yellow-100 text-yellow-800': task.status_color === 'yellow',
                      'bg-green-100 text-green-800': task.status_color === 'green',
                      'bg-red-100 text-red-800': task.status_color === 'red',
                    }"
                  >
                    {{ task.status_label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                    :class="{
                      'bg-gray-100 text-gray-800': task.priority_color === 'gray',
                      'bg-blue-100 text-blue-800': task.priority_color === 'blue',
                      'bg-orange-100 text-orange-800': task.priority_color === 'orange',
                      'bg-red-100 text-red-800': task.priority_color === 'red',
                    }"
                  >
                    {{ task.priority_label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">
                  {{ task.assigned_to_name || 'Sin asignar' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span v-if="task.due_date" :class="{ 'text-red-600 font-semibold': task.is_overdue }">
                    {{ formatDate(task.due_date) }}
                  </span>
                  <span v-else class="text-secondary-400">Sin fecha</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('tasks.show', task.id)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    Ver
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="tasks.data.length > 0" class="border-t border-secondary-200 bg-white px-4 py-3 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="text-sm text-secondary-700">
              Mostrando {{ tasks.from }} a {{ tasks.to }} de {{ tasks.total }} tareas
            </div>
            <div class="flex gap-2">
              <template v-for="link in tasks.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm rounded-md',
                    link.active
                      ? 'bg-primary-600 text-white'
                      : 'bg-white text-secondary-700 hover:bg-secondary-50 border border-secondary-300'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-3 py-2 text-sm rounded-md',
                    'bg-secondary-100 text-secondary-400 cursor-not-allowed border border-secondary-200'
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>

        <div v-else class="px-6 py-12 text-center">
          <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-secondary-900">No hay tareas</h3>
          <p class="mt-1 text-sm text-secondary-500">Comienza creando una nueva tarea.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  tasks: Object,
  filters: Object,
  statuses: Object,
  priorities: Object,
  users: Array,
  stats: Object,
})

const form = reactive({
  status: props.filters.status || '',
  priority: props.filters.priority || '',
  assigned_to: props.filters.assigned_to || '',
  search: props.filters.search || '',
})

const applyFilters = () => {
  router.get(route('tasks.index'), form, {
    preserveState: true,
    preserveScroll: true,
  })
}

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>
