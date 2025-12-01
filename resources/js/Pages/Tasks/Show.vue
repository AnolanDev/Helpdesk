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

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-secondary-500">Estado</label>
                  <div class="mt-1">
                    <span
                      class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
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
                  <option v-for="(label, value) in statuses" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <button
                  type="submit"
                  :disabled="statusForm.processing"
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
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  task: Object,
  users: Array,
  statuses: Object,
  priorities: Object,
  canEdit: Boolean,
})

const commentForm = useForm({
  comment: '',
})

const statusForm = useForm({
  status: props.task.status,
})

const assignForm = useForm({
  assigned_to: props.task.assigned_to || '',
})

const submitComment = () => {
  commentForm.post(route('tasks.comments', props.task.id), {
    preserveScroll: true,
    onSuccess: () => {
      commentForm.reset()
    },
  })
}

const updateStatus = () => {
  statusForm.patch(route('tasks.status', props.task.id), {
    preserveScroll: true,
  })
}

const assignTask = () => {
  assignForm.patch(route('tasks.assign', props.task.id), {
    preserveScroll: true,
  })
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
</script>
