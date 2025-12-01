<template>
  <div
    draggable="true"
    @dragstart="handleDragStart"
    class="cursor-move rounded-lg border border-secondary-200 bg-white p-4 shadow-sm transition-all hover:shadow-md"
  >
    <Link :href="route('tasks.show', task.id)" class="block">
      <div class="flex items-start justify-between gap-2">
        <h4 class="font-semibold text-secondary-900 text-sm">{{ task.title }}</h4>
        <span
          class="inline-flex flex-shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
          :class="{
            'bg-gray-100 text-gray-800': task.priority_color === 'gray',
            'bg-blue-100 text-blue-800': task.priority_color === 'blue',
            'bg-orange-100 text-orange-800': task.priority_color === 'orange',
            'bg-red-100 text-red-800': task.priority_color === 'red',
          }"
        >
          {{ task.priority_label }}
        </span>
      </div>

      <p v-if="task.description" class="mt-2 text-xs text-secondary-600 line-clamp-2">
        {{ task.description }}
      </p>

      <div class="mt-3 flex items-center justify-between text-xs text-secondary-500">
        <span>{{ task.task_number }}</span>
        <span v-if="task.due_date" :class="{ 'text-red-600 font-semibold': task.is_overdue }">
          <svg class="inline h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          {{ formatDate(task.due_date) }}
        </span>
      </div>

      <div v-if="task.assigned_to_name" class="mt-2 flex items-center gap-2">
        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">
          {{ task.assigned_to_name.charAt(0).toUpperCase() }}
        </div>
        <span class="text-xs text-secondary-600">{{ task.assigned_to_name }}</span>
      </div>
    </Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  task: Object,
})

const emit = defineEmits(['dragstart'])

const handleDragStart = (event) => {
  emit('dragstart', event, props.task)
}

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('es-ES', {
    month: 'short',
    day: 'numeric',
  })
}
</script>
