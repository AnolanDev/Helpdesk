<template>
  <Head :title="task.title" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('boards.show', task.board.id)"
            class="text-secondary-600 hover:text-secondary-900 dark:text-secondary-400 dark:hover:text-secondary-100"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </Link>
          <div>
            <h2 class="text-xl font-semibold leading-tight text-secondary-800 dark:text-secondary-100">{{ task.title }}</h2>
            <p class="text-sm text-secondary-600 dark:text-secondary-400">{{ task.board.name }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <Link
            v-if="canEdit"
            :href="route('tasks.edit', task.id)"
            class="inline-flex items-center rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-200 dark:hover:bg-secondary-700"
          >
            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
              ></path>
            </svg>
            Editar
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Task Info Card -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-secondary-800 dark:shadow-lg">
              <div class="mb-4 flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <!-- Status Badge -->
                  <span
                    class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                    :class="statusColorClass"
                  >
                    {{ task.status_label }}
                  </span>
                  <!-- Priority Badge -->
                  <span
                    class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                    :class="priorityColorClass"
                  >
                    {{ task.priority_label }}
                  </span>
                </div>
              </div>

              <!-- Description -->
              <div class="mb-6">
                <h3 class="mb-2 text-sm font-medium text-secondary-700 dark:text-secondary-300">Descripción</h3>
                <p class="whitespace-pre-wrap text-sm text-secondary-900 dark:text-secondary-100">
                  {{ task.description || 'Sin descripción' }}
                </p>
              </div>

              <!-- Time Status -->
              <div class="rounded-md border p-4" :class="timeAlertClass">
                <div class="mb-2 flex items-center justify-between">
                  <span class="text-sm font-medium text-secondary-900 dark:text-secondary-100">Estado del Tiempo</span>
                  <span class="text-lg font-bold dark:text-secondary-200">
                    {{ task.hours_elapsed.toFixed(1) }}h / {{ task.target_hours }}h
                  </span>
                </div>
                <div class="mb-2 h-2 w-full overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
                  <div
                    class="h-full transition-all duration-300"
                    :class="timeProgressClass"
                    :style="{ width: `${Math.min(task.time_percentage, 100)}%` }"
                  ></div>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span v-if="task.time_status === 'overdue'" class="font-semibold text-red-700 dark:text-red-400">
                    ⚠️ Tiempo excedido ({{ task.time_percentage.toFixed(0) }}%)
                  </span>
                  <span v-else-if="task.time_status === 'warning'" class="font-semibold text-yellow-700 dark:text-yellow-400">
                    ⏰ Advertencia - {{ (100 - task.time_percentage).toFixed(0) }}% restante
                  </span>
                  <span v-else class="font-semibold text-green-700 dark:text-green-400">
                    ✓ Dentro del tiempo - {{ (100 - task.time_percentage).toFixed(0) }}% restante
                  </span>
                </div>
              </div>

              <!-- Change Status -->
              <div v-if="canUpdateStatus" class="mt-6 border-t border-secondary-200 pt-6 dark:border-secondary-700">
                <h3 class="mb-3 text-sm font-medium text-secondary-700 dark:text-secondary-300">Cambiar Estado</h3>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="transition in allowedTransitions"
                    :key="transition.value"
                    @click="changeStatus(transition.value)"
                    type="button"
                    class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm font-medium shadow-sm transition-colors"
                    :class="getTransitionButtonClass(transition.color)"
                  >
                    {{ transition.label }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Sub-Tasks Card -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-secondary-800 dark:shadow-lg">
              <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100">Sub-tareas</h3>
                <span v-if="task.sub_tasks.length > 0" class="text-sm text-secondary-600 dark:text-secondary-400">
                  {{ completedSubTasksCount }} / {{ task.sub_tasks.length }} completadas
                </span>
              </div>

              <!-- Progress Bar -->
              <div v-if="task.sub_tasks.length > 0" class="mb-4">
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
              </div>

              <!-- Add Sub-Task Form (SIEMPRE ARRIBA) -->
              <div v-if="canEdit" class="mb-4 rounded-md border-2 border-dashed border-primary-300 bg-primary-50/50 p-4 dark:border-primary-700 dark:bg-primary-900/10">
                <label class="mb-2 block text-sm font-medium text-secondary-700 dark:text-secondary-300">
                  Agregar nueva sub-tarea
                </label>
                <form @submit.prevent="addSubTask" class="flex gap-2">
                  <input
                    v-model="newSubTaskTitle"
                    type="text"
                    placeholder="Escribe el título de la sub-tarea..."
                    class="flex-1 rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100 dark:placeholder-secondary-400"
                  />
                  <button
                    type="submit"
                    :disabled="!newSubTaskTitle.trim() || addingSubTask"
                    class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ addingSubTask ? 'Agregando...' : 'Agregar' }}
                  </button>
                </form>
              </div>

              <!-- Sub-Tasks List -->
              <div v-if="task.sub_tasks.length > 0" class="space-y-2">
                <SubTaskItem
                  v-for="subTask in task.sub_tasks"
                  :key="subTask.id"
                  :sub-task="subTask"
                  :can-edit="canEdit"
                  :can-delete="canEdit"
                  :can-reorder="canEdit"
                />
              </div>

              <!-- Empty State -->
              <div v-else class="py-8 text-center">
                <svg
                  class="mx-auto h-10 w-10 text-secondary-400 dark:text-secondary-500"
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
                <p class="mt-2 text-sm text-secondary-600 dark:text-secondary-400">No hay sub-tareas todavía</p>
                <p class="text-xs text-secondary-500 dark:text-secondary-500">
                  {{ canEdit ? 'Usa el formulario de arriba para agregar la primera sub-tarea' : 'Este tablero no tiene sub-tareas' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Board Info -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-secondary-800 dark:shadow-lg">
              <h3 class="mb-4 text-sm font-medium text-secondary-700 dark:text-secondary-300">Tablero</h3>
              <Link
                :href="route('boards.show', task.board.id)"
                class="block rounded-md border border-secondary-200 p-3 transition-colors hover:border-primary-300 hover:bg-primary-50 dark:border-secondary-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20"
              >
                <p class="font-medium text-secondary-900 dark:text-secondary-100">{{ task.board.name }}</p>
                <p class="mt-1 text-xs text-secondary-600 dark:text-secondary-400">{{ task.board.user.name }}</p>
              </Link>
            </div>

            <!-- Task Details -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-secondary-800 dark:shadow-lg">
              <h3 class="mb-4 text-sm font-medium text-secondary-700 dark:text-secondary-300">Detalles</h3>
              <div class="space-y-3 text-sm">
                <div>
                  <span class="text-secondary-600 dark:text-secondary-400">Creada:</span>
                  <p class="font-medium text-secondary-900 dark:text-secondary-100">{{ formatDate(task.created_at) }}</p>
                </div>
                <div v-if="task.completed_at">
                  <span class="text-secondary-600 dark:text-secondary-400">Completada:</span>
                  <p class="font-medium text-secondary-900 dark:text-secondary-100">{{ formatDate(task.completed_at) }}</p>
                </div>
                <div v-if="task.cancel_reason">
                  <span class="text-secondary-600 dark:text-secondary-400">Razón de cancelación:</span>
                  <p class="mt-1 font-medium text-red-700 dark:text-red-400">{{ task.cancel_reason }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Modal -->
    <Modal :show="showCancelModal" @close="showCancelModal = false">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100">Cancelar Tarea</h2>
        <p class="mt-2 text-sm text-secondary-600 dark:text-secondary-400">
          Por favor, proporciona una razón para la cancelación de esta tarea.
        </p>
        <form @submit.prevent="submitCancellation" class="mt-4">
          <textarea
            v-model="cancelReason"
            rows="4"
            required
            class="block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100 dark:placeholder-secondary-400"
            placeholder="Describe la razón de la cancelación..."
          ></textarea>
          <div class="mt-4 flex justify-end gap-3">
            <button
              type="button"
              @click="showCancelModal = false"
              class="rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-200 dark:hover:bg-secondary-700"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="!cancelReason.trim()"
              class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              Confirmar Cancelación
            </button>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SubTaskItem from '@/Components/SubTaskItem.vue'
import Modal from '@/Components/Modal.vue'

const page = usePage()

const props = defineProps({
  task: Object,
  statuses: Object,
  priorities: Object,
})

const newSubTaskTitle = ref('')
const addingSubTask = ref(false)
const allowedTransitions = ref([])
const showCancelModal = ref(false)
const cancelReason = ref('')

const canEdit = computed(() => {
  const user = page.props.auth.user
  // Solo el propietario del tablero puede editar (los admins NO pueden editar tareas de otros)
  return user?.id === props.task.board.user_id
})

const isOwner = computed(() => {
  const user = page.props.auth.user
  return user?.id === props.task.board.user_id
})

const canUpdateStatus = computed(() => {
  return canEdit.value && allowedTransitions.value.length > 0
})

const completedSubTasksCount = computed(() => {
  return props.task.sub_tasks.filter(st => st.status === 'completada').length
})

const statusColorClass = computed(() => {
  switch (props.task.status_color) {
    case 'gray': return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
    case 'blue': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
    case 'purple': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
    case 'yellow': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
    case 'green': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
    case 'red': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    default: return 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300'
  }
})

const priorityColorClass = computed(() => {
  switch (props.task.priority_color) {
    case 'gray': return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
    case 'blue': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
    case 'orange': return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
    case 'red': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    default: return 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300'
  }
})

const timeAlertClass = computed(() => {
  switch (props.task.time_color) {
    case 'green': return 'border-green-300 bg-green-50 dark:border-green-700 dark:bg-green-900/30'
    case 'yellow': return 'border-yellow-300 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-900/30'
    case 'red': return 'border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/30'
    default: return 'border-secondary-300 bg-secondary-50 dark:border-secondary-600 dark:bg-secondary-700/50'
  }
})

const timeProgressClass = computed(() => {
  switch (props.task.time_color) {
    case 'green': return 'bg-green-500 dark:bg-green-400'
    case 'yellow': return 'bg-yellow-500 dark:bg-yellow-400'
    case 'red': return 'bg-red-500 dark:bg-red-400'
    default: return 'bg-secondary-500 dark:bg-secondary-400'
  }
})

const getTransitionButtonClass = (color) => {
  const baseClass = 'hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-secondary-800'
  switch (color) {
    case 'gray': return `${baseClass} border-gray-300 bg-gray-100 text-gray-800 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-300`
    case 'blue': return `${baseClass} border-blue-300 bg-blue-100 text-blue-800 focus:ring-blue-500 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-300`
    case 'purple': return `${baseClass} border-purple-300 bg-purple-100 text-purple-800 focus:ring-purple-500 dark:border-purple-700 dark:bg-purple-900/30 dark:text-purple-300`
    case 'yellow': return `${baseClass} border-yellow-300 bg-yellow-100 text-yellow-800 focus:ring-yellow-500 dark:border-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300`
    case 'green': return `${baseClass} border-green-300 bg-green-100 text-green-800 focus:ring-green-500 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300`
    case 'red': return `${baseClass} border-red-300 bg-red-100 text-red-800 focus:ring-red-500 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300`
    default: return `${baseClass} border-secondary-300 bg-secondary-100 text-secondary-800 focus:ring-secondary-500 dark:border-secondary-700 dark:bg-secondary-900/30 dark:text-secondary-300`
  }
}

const addSubTask = () => {
  if (!newSubTaskTitle.value.trim()) return

  addingSubTask.value = true
  router.post(
    route('sub-tasks.store', props.task.id),
    { title: newSubTaskTitle.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        newSubTaskTitle.value = ''
        addingSubTask.value = false
      },
      onError: () => {
        addingSubTask.value = false
      },
    }
  )
}

const changeStatus = (newStatus) => {
  if (newStatus === 'cancelada') {
    showCancelModal.value = true
    return
  }

  router.patch(
    route('tasks.status', props.task.id),
    { status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        fetchAllowedTransitions()
      },
    }
  )
}

const submitCancellation = () => {
  router.patch(
    route('tasks.status', props.task.id),
    {
      status: 'cancelada',
      cancel_reason: cancelReason.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        showCancelModal.value = false
        cancelReason.value = ''
        fetchAllowedTransitions()
      },
    }
  )
}

const fetchAllowedTransitions = async () => {
  try {
    const response = await fetch(route('tasks.transitions', props.task.id))
    const data = await response.json()
    allowedTransitions.value = data.allowed_transitions
  } catch (error) {
    console.error('Error fetching transitions:', error)
  }
}

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  fetchAllowedTransitions()
})
</script>
