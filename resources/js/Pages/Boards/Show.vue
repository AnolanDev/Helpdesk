<template>
  <Head :title="board.name" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('boards.index')"
            class="text-secondary-600 hover:text-secondary-900 dark:text-secondary-400 dark:hover:text-secondary-100"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </Link>
          <h2 class="text-xl font-semibold leading-tight text-secondary-800 dark:text-secondary-100">{{ board.name }}</h2>
        </div>
        <div class="flex items-center gap-3">
          <!-- Badge de visualización (solo para admins viendo tableros de otros) -->
          <span
            v-if="!isOwner"
            class="inline-flex items-center gap-1.5 rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
          >
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Solo lectura
          </span>

          <!-- Botones de acción -->
          <!-- Botón compartir (solo para propietarios) -->
          <button
            v-if="canShare"
            @click="showShareModal = true"
            class="inline-flex items-center rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-200 dark:hover:bg-secondary-700"
          >
            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
            </svg>
            Compartir
          </button>

          <!-- Botones de acción (para propietarios o con permisos de escritura) -->
          <template v-if="hasWritePermission">
            <Link
              :href="route('tasks.create', { board_id: board.id })"
              class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
            >
              <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Nueva Tarea
            </Link>
            <Link
              v-if="isOwner"
              :href="route('boards.edit', board.id)"
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
              Editar Tablero
            </Link>
          </template>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Board Info -->
        <div class="mb-6 rounded-lg bg-white p-6 shadow dark:bg-secondary-800 dark:shadow-lg">
          <div class="grid gap-6 md:grid-cols-2">
            <div>
              <h3 class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Descripción</h3>
              <p class="mt-1 text-sm text-secondary-900 dark:text-secondary-100">
                {{ board.description || 'Sin descripción' }}
              </p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Propietario</h3>
              <p class="mt-1 text-sm text-secondary-900 dark:text-secondary-100">{{ board.user.name }}</p>
            </div>
          </div>

          <!-- Board Stats -->
          <div class="mt-6 grid gap-4 border-t border-secondary-200 pt-6 sm:grid-cols-2 md:grid-cols-4 dark:border-secondary-700">
            <div class="rounded-lg bg-primary-50 p-4 dark:bg-primary-900/20">
              <div class="text-sm font-medium text-primary-700 dark:text-primary-300">Total de Tareas</div>
              <div class="mt-1 text-2xl font-semibold text-primary-900 dark:text-primary-100">{{ board.tasks.length }}</div>
            </div>
            <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
              <div class="text-sm font-medium text-green-700 dark:text-green-300">Completadas</div>
              <div class="mt-1 text-2xl font-semibold text-green-900 dark:text-green-100">{{ completedTasksCount }}</div>
            </div>
            <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
              <div class="text-sm font-medium text-yellow-700 dark:text-yellow-300">En Progreso</div>
              <div class="mt-1 text-2xl font-semibold text-yellow-900 dark:text-yellow-100">{{ inProgressTasksCount }}</div>
            </div>
            <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
              <div class="text-sm font-medium text-red-700 dark:text-red-300">Vencidas</div>
              <div class="mt-1 text-2xl font-semibold text-red-900 dark:text-red-100">{{ overdueTasksCount }}</div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div v-if="board.tasks.length > 0" class="mt-6">
            <div class="mb-2 flex items-center justify-between text-sm">
              <span class="font-medium text-secondary-700 dark:text-secondary-300">Progreso General</span>
              <span class="font-semibold text-secondary-900 dark:text-secondary-100">{{ progressPercentage }}%</span>
            </div>
            <div class="h-3 w-full overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
              <div
                class="h-full rounded-full bg-green-500 transition-all duration-300 dark:bg-green-400"
                :style="{ width: `${progressPercentage}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Tasks Section -->
        <div v-if="board.tasks.length > 0">
          <!-- Header de sección de tareas -->
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100">
              Tareas ({{ board.tasks.length }})
            </h3>
            <Link
              v-if="hasWritePermission"
              :href="route('tasks.create', { board_id: board.id })"
              class="inline-flex items-center rounded-md bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
            >
              <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Nueva Tarea
            </Link>
          </div>

          <!-- Lista de tareas con acciones -->
          <div class="mb-6 overflow-hidden rounded-lg bg-white shadow dark:bg-secondary-800">
            <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
              <thead class="bg-secondary-50 dark:bg-secondary-900/50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700 dark:text-secondary-300">
                    Tarea
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700 dark:text-secondary-300">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700 dark:text-secondary-300">
                    Prioridad
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700 dark:text-secondary-300">
                    Progreso
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-secondary-700 dark:text-secondary-300">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-secondary-200 bg-white dark:divide-secondary-700 dark:bg-secondary-800">
                <tr v-for="task in board.tasks" :key="task.id" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-medium text-secondary-900 dark:text-secondary-100">
                          {{ task.title }}
                        </div>
                        <div v-if="task.description" class="text-sm text-secondary-500 line-clamp-1 dark:text-secondary-400">
                          {{ task.description }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="getStatusClass(task.status_color)">
                      {{ task.status_label }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="getPriorityClass(task.priority_color)">
                      {{ task.priority_label }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="h-2 w-24 overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
                        <div
                          class="h-full rounded-full bg-green-500 transition-all dark:bg-green-400"
                          :style="{ width: `${task.progress}%` }"
                        ></div>
                      </div>
                      <span class="text-xs text-secondary-600 dark:text-secondary-400">{{ task.progress }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="route('tasks.show', task.id)"
                        class="rounded-md bg-primary-100 p-2 text-primary-700 hover:bg-primary-200 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-900/50"
                        title="Ver detalles"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </Link>
                      <Link
                        v-if="hasWritePermission"
                        :href="route('tasks.edit', task.id)"
                        class="rounded-md bg-secondary-100 p-2 text-secondary-700 hover:bg-secondary-200 dark:bg-secondary-700 dark:text-secondary-300 dark:hover:bg-secondary-600"
                        title="Editar"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </Link>
                      <button
                        v-if="hasWritePermission"
                        @click="openDeleteModal(task)"
                        class="rounded-md bg-red-100 p-2 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                        title="Eliminar"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Vista de tarjetas (opcional, colapsable) -->
          <details class="mb-6">
            <summary class="cursor-pointer rounded-lg bg-secondary-100 px-4 py-2 text-sm font-medium text-secondary-700 hover:bg-secondary-200 dark:bg-secondary-900/50 dark:text-secondary-300 dark:hover:bg-secondary-900">
              Vista de tarjetas
            </summary>
            <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              <TaskCard v-for="task in board.tasks" :key="task.id" :task="task" :can-edit="isOwner" />
            </div>
          </details>
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
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            ></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-secondary-900 dark:text-secondary-100">No hay tareas</h3>
          <p class="mt-1 text-sm text-secondary-500 dark:text-secondary-400">Comienza creando una nueva tarea para este tablero.</p>
          <div v-if="hasWritePermission" class="mt-6">
            <Link
              :href="route('tasks.create', { board_id: board.id })"
              class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
            >
              <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Crear Primera Tarea
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de compartir -->
    <ShareBoardModal
      :show="showShareModal"
      :board="board"
      @close="showShareModal = false"
    />

    <!-- Modal de confirmación de eliminación -->
    <ConfirmDeleteModal
      :show="showDeleteModal"
      :title="`¿Eliminar tarea '${taskToDelete?.title}'?`"
      message="Esta acción no se puede deshacer."
      :warning-message="`Se eliminarán también ${taskToDelete?.sub_tasks?.length || 0} sub-tareas asociadas.`"
      confirm-text="Sí, eliminar tarea"
      cancel-text="Cancelar"
      :processing="deletingTask"
      @close="closeDeleteModal"
      @confirm="confirmDelete"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import TaskCard from '@/Components/TaskCard.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import ShareBoardModal from '@/Components/ShareBoardModal.vue'

const page = usePage()

const props = defineProps({
  board: Object,
  currentUserPermission: String,
  canShare: Boolean,
})

// Estado del modal de eliminación
const showDeleteModal = ref(false)
const taskToDelete = ref(null)
const deletingTask = ref(false)

// Estado del modal de compartir
const showShareModal = ref(false)

const canEdit = computed(() => {
  const user = page.props.auth.user
  // Solo el propietario puede editar (los admins NO pueden editar tableros de otros)
  return user?.id === props.board.user_id
})

const isOwner = computed(() => {
  const user = page.props.auth.user
  return user?.id === props.board.user_id
})

const hasWritePermission = computed(() => {
  return props.currentUserPermission === 'write'
})

const completedTasksCount = computed(() => {
  return props.board.tasks.filter(task => task.status === 'finalizada').length
})

const inProgressTasksCount = computed(() => {
  return props.board.tasks.filter(task => task.status === 'en_progreso').length
})

const overdueTasksCount = computed(() => {
  return props.board.tasks.filter(task => task.is_overdue).length
})

const progressPercentage = computed(() => {
  if (props.board.tasks.length === 0) return 0
  return Math.round((completedTasksCount.value / props.board.tasks.length) * 100)
})

// Función para obtener clases de color según estado
const getStatusClass = (color) => {
  const classes = {
    'gray': 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
    'blue': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'purple': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    'yellow': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    'green': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    'red': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  }
  return classes[color] || 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300'
}

// Función para obtener clases de color según prioridad
const getPriorityClass = (color) => {
  const classes = {
    'gray': 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
    'blue': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'orange': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
    'red': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  }
  return classes[color] || 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-300'
}

// Funciones para el modal de eliminación
const openDeleteModal = (task) => {
  taskToDelete.value = task
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  setTimeout(() => {
    taskToDelete.value = null
  }, 200) // Esperar a que termine la animación
}

const confirmDelete = () => {
  if (!taskToDelete.value) return

  deletingTask.value = true

  router.delete(route('tasks.destroy', taskToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal()
    },
    onError: (errors) => {
      console.error('Error al eliminar:', errors)
    },
    onFinish: () => {
      deletingTask.value = false
    }
  })
}
</script>
