<template>
  <Link
    :href="route('tasks.show', task.id)"
    class="group relative block rounded-lg border p-4 shadow-sm transition-all hover:shadow-lg hover:scale-[1.02] cursor-pointer"
    :class="[
      timeColorClass,
      { 'opacity-75': task.status === 'finalizada' || task.status === 'cancelada' }
    ]"
  >
    <!-- Botones de acción (solo para propietarios) -->
    <div v-if="canEdit" class="absolute right-2 top-2 z-10 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
      <Link
        :href="route('tasks.edit', task.id)"
        class="rounded-md bg-white p-1.5 text-secondary-600 shadow-sm hover:bg-primary-50 hover:text-primary-600 dark:bg-secondary-700 dark:text-secondary-300 dark:hover:bg-primary-900/20 dark:hover:text-primary-400"
        @click.stop
        title="Editar tarea"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
      </Link>
    </div>

    <div>
      <!-- Header -->
      <div class="mb-3 flex items-start justify-between gap-2">
        <h3 class="flex-1 text-sm font-semibold text-secondary-900 dark:text-secondary-100">{{ task.title }}</h3>

        <!-- Priority Badge -->
        <span
          class="inline-flex flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
          :class="priorityColorClass"
        >
          {{ task.priority_label }}
        </span>
      </div>

      <!-- Description -->
      <p v-if="task.description" class="mb-3 text-xs text-secondary-600 line-clamp-2 dark:text-secondary-400">
        {{ task.description }}
      </p>

      <!-- Progress Bar -->
      <div v-if="task.sub_tasks && task.sub_tasks.length > 0" class="mb-3">
        <div class="mb-1 flex items-center justify-between text-xs">
          <span class="text-secondary-600 dark:text-secondary-400">Progreso</span>
          <span class="font-semibold text-secondary-900 dark:text-secondary-100">{{ task.progress }}%</span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
          <div
            class="h-full rounded-full bg-green-500 transition-all duration-300 dark:bg-green-400"
            :style="{ width: `${task.progress}%` }"
          ></div>
        </div>
        <p class="mt-1 text-xs text-secondary-500 dark:text-secondary-400">
          {{ completedSubTasksCount }} / {{ task.sub_tasks.length }} completadas
        </p>
      </div>

      <!-- Status Badge -->
      <div class="mb-3">
        <span
          class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
          :class="statusColorClass"
        >
          {{ task.status_label }}
        </span>
      </div>

      <!-- Time Info -->
      <div class="mb-2 rounded-md border p-2" :class="timeAlertClass">
        <div class="flex items-center justify-between text-xs">
          <span class="font-medium dark:text-secondary-300">Tiempo transcurrido:</span>
          <span class="font-bold dark:text-secondary-200">{{ task.hours_elapsed.toFixed(1) }}h / {{ task.target_hours }}h</span>
        </div>
        <div v-if="task.time_status === 'overdue'" class="mt-1 text-xs font-semibold text-red-700 dark:text-red-400">
          ⚠️ Tiempo excedido
        </div>
        <div v-else-if="task.time_status === 'warning'" class="mt-1 text-xs font-semibold text-yellow-700 dark:text-yellow-400">
          ⏰ Menos del 50% del tiempo restante
        </div>
        <div v-else class="mt-1 text-xs text-green-700 dark:text-green-400">
          ✓ Dentro del tiempo
        </div>
      </div>

      <!-- Footer Info -->
      <div class="flex items-center justify-between text-xs text-secondary-500 dark:text-secondary-400">
        <span>Creada: {{ formatDate(task.created_at) }}</span>
        <span v-if="task.board">{{ task.board.name }}</span>
      </div>
    </div>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  task: Object,
  canEdit: {
    type: Boolean,
    default: false,
  },
})

const completedSubTasksCount = computed(() => {
  if (!props.task.sub_tasks) return 0
  return props.task.sub_tasks.filter(st => st.status === 'completada').length
})

const timeColorClass = computed(() => {
  switch (props.task.time_color) {
    case 'green':
      return 'border-green-300 bg-green-50 dark:border-green-800 dark:bg-green-900/20'
    case 'yellow':
      return 'border-yellow-300 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20'
    case 'red':
      return 'border-red-300 bg-red-50 dark:border-red-800 dark:bg-red-900/20'
    default:
      return 'border-secondary-200 bg-white dark:border-secondary-700 dark:bg-secondary-800'
  }
})

const timeAlertClass = computed(() => {
  switch (props.task.time_color) {
    case 'green':
      return 'border-green-300 bg-green-100 dark:border-green-700 dark:bg-green-900/30'
    case 'yellow':
      return 'border-yellow-300 bg-yellow-100 dark:border-yellow-700 dark:bg-yellow-900/30'
    case 'red':
      return 'border-red-300 bg-red-100 dark:border-red-700 dark:bg-red-900/30'
    default:
      return 'border-secondary-300 bg-secondary-100 dark:border-secondary-600 dark:bg-secondary-700/50'
  }
})

const priorityColorClass = computed(() => {
  switch (props.task.priority_color) {
    case 'gray':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
    case 'blue':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
    case 'orange':
      return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
    case 'red':
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    default:
      return 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300'
  }
})

const statusColorClass = computed(() => {
  switch (props.task.status_color) {
    case 'gray':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
    case 'blue':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
    case 'purple':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
    case 'yellow':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
    case 'green':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
    case 'red':
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    default:
      return 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300'
  }
})

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>
