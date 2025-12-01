<template>
  <AppLayout>
    <div class="mx-auto max-w-5xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link
            :href="route('tasks.index')"
            class="rounded-lg p-2 text-secondary-600 transition-all hover:bg-secondary-100"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-secondary-900">{{ task.title }}</h1>
            <p class="text-sm text-secondary-600">{{ task.task_number }}</p>
          </div>
        </div>
        <Link
          v-if="canEdit"
          :href="route('tasks.edit', task.id)"
          class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Editar
        </Link>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Task Details Card -->
          <div class="rounded-lg border border-secondary-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-secondary-900 mb-4">Detalles</h2>

            <div class="space-y-4">
              <div>
                <label class="text-sm font-medium text-secondary-500">Descripción</label>
                <p class="mt-1 text-sm text-secondary-900 whitespace-pre-wrap">
                  {{ task.description || 'Sin descripción' }}
                </p>
              </div>

              <!-- Blocked Alert -->
              <div v-if="task.blocked_reason" class="rounded-lg bg-orange-50 border border-orange-200 p-4">
                <div class="flex gap-3">
                  <svg class="h-5 w-5 text-orange-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-orange-900">Tarea Bloqueada</p>
                    <p class="mt-1 text-sm text-orange-800">{{ task.blocked_reason }}</p>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-secondary-500">Estado</label>
                  <div class="mt-1">
                    <span
                      class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                      :class="{
                        'bg-gray-100 text-gray-800': task.status_color === 'gray',
                        'bg-purple-100 text-purple-800': task.status_color === 'purple',
                        'bg-blue-100 text-blue-800': task.status_color === 'blue',
                        'bg-orange-100 text-orange-800': task.status_color === 'orange',
                        'bg-yellow-100 text-yellow-800': task.status_color === 'yellow',
                        'bg-green-100 text-green-800': task.status_color === 'green',
                        'bg-red-100 text-red-800': task.status_color === 'red',
                        'bg-slate-100 text-slate-800': task.status_color === 'slate',
                      }"
                    >
                      {{ task.status_label }}
                    </span>
                  </div>
                </div>

                <div>
                  <label class="text-sm font-medium text-secondary-500">Prioridad</label>
                  <div class="mt-1">
                    <span
                      class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
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
                </div>
              </div>
            </div>
          </div>

          <!-- Comments Section -->
          <div class="rounded-lg border border-secondary-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-secondary-900 mb-4">Comentarios</h2>

            <!-- Comment Form -->
            <form @submit.prevent="submitComment" class="mb-6">
              <textarea
                v-model="commentForm.comment"
                rows="3"
                placeholder="Agregar un comentario..."
                class="block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              ></textarea>
              <div class="mt-2 flex justify-end">
                <button
                  type="submit"
                  :disabled="commentForm.processing || !commentForm.comment"
                  class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700 disabled:opacity-50"
                >
                  Comentar
                </button>
              </div>
            </form>

            <!-- Comments List -->
            <div class="space-y-4">
              <div
                v-for="comment in task.comments"
                :key="comment.id"
                class="flex gap-3 rounded-lg bg-secondary-50 p-4"
              >
                <div class="flex-shrink-0">
                  <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 text-white font-semibold">
                    {{ comment.user_name.charAt(0).toUpperCase() }}
                  </div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-secondary-900">{{ comment.user_name }}</p>
                    <p class="text-xs text-secondary-500">{{ formatDate(comment.created_at) }}</p>
                  </div>
                  <p class="mt-1 text-sm text-secondary-700 whitespace-pre-wrap">{{ comment.comment }}</p>
                </div>
              </div>

              <div v-if="!task.comments || task.comments.length === 0" class="text-center py-8">
                <p class="text-sm text-secondary-500">No hay comentarios aún</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Actions Card -->
          <div class="rounded-lg border border-secondary-200 bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-secondary-900 mb-3">Acciones</h3>
            <div class="space-y-2">
              <form @submit.prevent="updateStatus" class="space-y-2">
                <select
                  v-model="statusForm.status"
                  class="block w-full rounded-md border-secondary-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                  <option :value="task.status">{{ task.status_label }} (actual)</option>
                  <option v-for="transition in allowedTransitions" :key="transition.value" :value="transition.value">
                    {{ transition.label }}
                    <span v-if="transition.requires_confirmation">⚠️</span>
                  </option>
                </select>
                <button
                  type="submit"
                  :disabled="statusForm.processing || statusForm.status === task.status"
                  class="w-full rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white transition-all hover:bg-primary-700 disabled:opacity-50"
                >
                  Cambiar Estado
                </button>
              </form>

              <form @submit.prevent="assignTask" class="space-y-2 pt-2 border-t border-secondary-200">
                <select
                  v-model="assignForm.assigned_to"
                  class="block w-full rounded-md border-secondary-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                  <option value="">Sin asignar</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
                <button
                  type="submit"
                  :disabled="assignForm.processing"
                  class="w-full rounded-lg bg-secondary-600 px-3 py-2 text-sm font-semibold text-white transition-all hover:bg-secondary-700 disabled:opacity-50"
                >
                  Asignar
                </button>
              </form>

              <!-- Quick Actions -->
              <div class="pt-2 border-t border-secondary-200 space-y-2">
                <!-- Block/Unblock -->
                <button
                  v-if="task.status === 'blocked' || task.blocked_reason"
                  @click="unblockTask"
                  :disabled="unblockForm.processing"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white transition-all hover:bg-orange-700 disabled:opacity-50"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                  </svg>
                  Desbloquear
                </button>
                <button
                  v-else
                  @click="showBlockModal = true"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white transition-all hover:bg-orange-700"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  Bloquear Tarea
                </button>

                <!-- Archive -->
                <button
                  v-if="task.status === 'done' || task.status === 'cancelled'"
                  @click="archiveTask"
                  :disabled="archiveForm.processing"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-slate-600 px-3 py-2 text-sm font-semibold text-white transition-all hover:bg-slate-700 disabled:opacity-50"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                  </svg>
                  Archivar
                </button>
              </div>
            </div>
          </div>

          <!-- Block Modal -->
          <div v-if="showBlockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
              <form @submit.prevent="blockTask">
                <div class="p-6">
                  <h3 class="text-lg font-semibold text-secondary-900 mb-4">Bloquear Tarea</h3>
                  <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-2">
                      Razón del bloqueo
                    </label>
                    <textarea
                      v-model="blockForm.blocked_reason"
                      rows="4"
                      required
                      placeholder="Describe por qué esta tarea está bloqueada..."
                      class="block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                  </div>
                </div>
                <div class="bg-secondary-50 px-6 py-3 flex justify-end gap-2 rounded-b-lg">
                  <button
                    type="button"
                    @click="showBlockModal = false"
                    class="px-4 py-2 text-sm font-semibold text-secondary-700 hover:bg-secondary-200 rounded-lg transition-all"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="blockForm.processing"
                    class="px-4 py-2 text-sm font-semibold bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-all disabled:opacity-50"
                  >
                    Bloquear
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Info Card -->
          <div class="rounded-lg border border-secondary-200 bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-secondary-900 mb-3">Información</h3>
            <dl class="space-y-3 text-sm">
              <div>
                <dt class="font-medium text-secondary-500">Creado por</dt>
                <dd class="mt-1 text-secondary-900">{{ task.created_by_name }}</dd>
              </div>
              <div>
                <dt class="font-medium text-secondary-500">Asignado a</dt>
                <dd class="mt-1 text-secondary-900">{{ task.assigned_to_name || 'Sin asignar' }}</dd>
              </div>
              <div>
                <dt class="font-medium text-secondary-500">Fecha de vencimiento</dt>
                <dd class="mt-1" :class="{ 'text-red-600 font-semibold': task.is_overdue }">
                  {{ task.due_date ? formatDate(task.due_date) : 'Sin fecha' }}
                </dd>
              </div>
              <div>
                <dt class="font-medium text-secondary-500">Creada</dt>
                <dd class="mt-1 text-secondary-900">{{ formatDate(task.created_at) }}</dd>
              </div>
              <div v-if="task.completed_at">
                <dt class="font-medium text-secondary-500">Completada</dt>
                <dd class="mt-1 text-secondary-900">{{ formatDate(task.completed_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  task: Object,
  users: Array,
  statuses: Object,
  priorities: Object,
  canEdit: Boolean,
})

const showBlockModal = ref(false)
const allowedTransitions = ref([])

const commentForm = useForm({
  comment: '',
})

const statusForm = useForm({
  status: props.task.status,
})

const assignForm = useForm({
  assigned_to: props.task.assigned_to || '',
})

const blockForm = useForm({
  blocked_reason: '',
})

const unblockForm = useForm({})

const archiveForm = useForm({})

const submitComment = () => {
  commentForm.post(route('tasks.comments', props.task.id), {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset()
    },
  })
}

const assignTask = () => {
  assignForm.patch(route('tasks.assign', props.task.id), {
    preserveScroll: true,
  })
}

const blockTask = () => {
  blockForm.patch(route('tasks.block', props.task.id), {
    preserveScroll: true,
    onSuccess: () => {
      showBlockModal.value = false
      blockForm.reset()
    },
  })
}

const unblockTask = () => {
  if (confirm('¿Estás seguro de que deseas desbloquear esta tarea?')) {
    unblockForm.patch(route('tasks.unblock', props.task.id), {
      preserveScroll: true,
    })
  }
}

const archiveTask = () => {
  if (confirm('¿Estás seguro de que deseas archivar esta tarea?')) {
    archiveForm.patch(route('tasks.archive', props.task.id), {
      preserveScroll: true,
    })
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadAllowedTransitions = async () => {
  try {
    const response = await axios.get(route('tasks.transitions', props.task.id))
    allowedTransitions.value = response.data.allowed_transitions
  } catch (error) {
    console.error('Error loading transitions:', error)
    // Fallback: usar todos los estados si hay error
    allowedTransitions.value = Object.entries(props.statuses)
      .filter(([value]) => value !== props.task.status)
      .map(([value, label]) => ({
        value,
        label,
        requires_confirmation: false
      }))
  }
}

const updateStatus = () => {
  const selectedTransition = allowedTransitions.value.find(
    t => t.value === statusForm.status
  )

  if (selectedTransition && selectedTransition.requires_confirmation) {
    if (!confirm(`⚠️ Esta transición requiere confirmación.\n\n¿Estás seguro de que deseas cambiar el estado a "${selectedTransition.label}"?`)) {
      statusForm.status = props.task.status
      return
    }
  }

  statusForm.patch(route('tasks.status', props.task.id), {
    preserveScroll: true,
    onSuccess: () => {
      loadAllowedTransitions()
    }
  })
}

onMounted(() => {
  loadAllowedTransitions()
})
</script>
