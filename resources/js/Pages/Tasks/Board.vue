<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Tablero de Tareas</h1>
          <p class="mt-2 text-sm text-secondary-600">
            Vista estilo Kanban - Arrastra las tareas para cambiar su estado
          </p>
        </div>
        <div class="flex gap-2">
          <Link
            :href="route('tasks.index')"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-secondary-100 px-4 py-2.5 text-sm font-semibold text-secondary-700 transition-all hover:bg-secondary-200"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Vista Lista
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

      <!-- Filters -->
      <div class="rounded-lg border border-secondary-200 bg-white p-4">
        <div class="grid gap-4 sm:grid-cols-2">
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
        </div>
      </div>

      <!-- Kanban Board -->
      <div class="grid gap-4 lg:grid-cols-3">
        <!-- Por Hacer Column -->
        <div class="flex flex-col rounded-lg border border-secondary-200 bg-secondary-50">
          <div class="border-b border-secondary-200 bg-white px-4 py-3">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold text-secondary-900">Por Hacer</h3>
              <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                {{ tasksData.todo.length }}
              </span>
            </div>
          </div>
          <div
            class="flex-1 space-y-3 p-4 min-h-[500px]"
            @drop="handleDrop($event, 'todo')"
            @dragover.prevent
            @dragenter.prevent
          >
            <TaskCard
              v-for="task in tasksData.todo"
              :key="task.id"
              :task="task"
              @dragstart="handleDragStart($event, task)"
            />
            <div v-if="tasksData.todo.length === 0" class="text-center py-8 text-sm text-secondary-500">
              No hay tareas
            </div>
          </div>
        </div>

        <!-- En Progreso Column -->
        <div class="flex flex-col rounded-lg border border-secondary-200 bg-secondary-50">
          <div class="border-b border-secondary-200 bg-white px-4 py-3">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold text-secondary-900">En Progreso</h3>
              <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                {{ tasksData.in_progress.length }}
              </span>
            </div>
          </div>
          <div
            class="flex-1 space-y-3 p-4 min-h-[500px]"
            @drop="handleDrop($event, 'in_progress')"
            @dragover.prevent
            @dragenter.prevent
          >
            <TaskCard
              v-for="task in tasksData.in_progress"
              :key="task.id"
              :task="task"
              @dragstart="handleDragStart($event, task)"
            />
            <div v-if="tasksData.in_progress.length === 0" class="text-center py-8 text-sm text-secondary-500">
              No hay tareas
            </div>
          </div>
        </div>

        <!-- En Revisión Column -->
        <div class="flex flex-col rounded-lg border border-secondary-200 bg-secondary-50">
          <div class="border-b border-secondary-200 bg-white px-4 py-3">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold text-secondary-900">En Revisión</h3>
              <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">
                {{ tasksData.review.length }}
              </span>
            </div>
          </div>
          <div
            class="flex-1 space-y-3 p-4 min-h-[500px]"
            @drop="handleDrop($event, 'review')"
            @dragover.prevent
            @dragenter.prevent
          >
            <TaskCard
              v-for="task in tasksData.review"
              :key="task.id"
              :task="task"
              @dragstart="handleDragStart($event, task)"
            />
            <div v-if="tasksData.review.length === 0" class="text-center py-8 text-sm text-secondary-500">
              No hay tareas
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import TaskCard from '@/Components/TaskCard.vue'

const props = defineProps({
  tasks: Object,
  filters: Object,
  priorities: Object,
  users: Array,
})

const form = reactive({
  assigned_to: props.filters.assigned_to || '',
  priority: props.filters.priority || '',
})

const tasksData = reactive({
  todo: props.tasks.todo || [],
  in_progress: props.tasks.in_progress || [],
  review: props.tasks.review || [],
})

const draggedTask = ref(null)

const applyFilters = () => {
  router.get(route('tasks.board'), form, {
    preserveState: true,
    preserveScroll: true,
  })
}

const handleDragStart = (event, task) => {
  draggedTask.value = task
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.dropEffect = 'move'
}

const handleDrop = (event, newStatus) => {
  event.preventDefault()

  if (!draggedTask.value) return

  const task = draggedTask.value

  // Si el estado no cambió, no hacer nada
  if (task.status === newStatus) {
    draggedTask.value = null
    return
  }

  // Actualizar estado en el backend
  router.patch(
    route('tasks.position', task.id),
    {
      status: newStatus,
      position: 0,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // Actualizar visualmente las columnas
        removeTaskFromColumn(task.status, task.id)
        addTaskToColumn(newStatus, { ...task, status: newStatus })
        draggedTask.value = null
      },
      onError: () => {
        draggedTask.value = null
      },
    }
  )
}

const removeTaskFromColumn = (status, taskId) => {
  const column = getColumnByStatus(status)
  if (column) {
    const index = column.findIndex(t => t.id === taskId)
    if (index > -1) {
      column.splice(index, 1)
    }
  }
}

const addTaskToColumn = (status, task) => {
  const column = getColumnByStatus(status)
  if (column) {
    column.push(task)
  }
}

const getColumnByStatus = (status) => {
  switch (status) {
    case 'todo':
      return tasksData.todo
    case 'in_progress':
      return tasksData.in_progress
    case 'review':
      return tasksData.review
    default:
      return null
  }
}
</script>
