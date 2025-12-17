<template>
  <Head title="Editar Tarea" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('boards.show', task.board_id)"
            class="text-secondary-600 hover:text-secondary-900"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </Link>
          <div>
            <h2 class="text-xl font-semibold leading-tight text-secondary-800">Editar Tarea</h2>
            <p v-if="task.board" class="text-sm text-secondary-600">{{ task.board.name }}</p>
          </div>
        </div>
        <button
          v-if="canDelete"
          @click="deleteTask"
          type="button"
          class="inline-flex items-center rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50"
        >
          <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
          Eliminar Tarea
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6">
            <!-- Title -->
            <div class="mb-6">
              <label for="title" class="block text-sm font-medium text-secondary-700">
                Título <span class="text-red-500">*</span>
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.title }"
                placeholder="Ej: Implementar autenticación de usuario"
              />
              <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                {{ form.errors.title }}
              </p>
            </div>

            <!-- Description -->
            <div class="mb-6">
              <label for="description" class="block text-sm font-medium text-secondary-700">
                Descripción
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="6"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.description }"
                placeholder="Describe los detalles de la tarea, objetivos, requisitos, etc..."
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Priority -->
            <div class="mb-6">
              <label for="priority" class="block text-sm font-medium text-secondary-700">
                Prioridad <span class="text-red-500">*</span>
              </label>
              <select
                id="priority"
                v-model="form.priority"
                required
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.priority }"
              >
                <option value="">Selecciona una prioridad</option>
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">
                {{ form.errors.priority }}
              </p>

              <!-- Priority Info -->
              <div v-if="form.priority" class="mt-2 rounded-md border p-3" :class="getPriorityInfoClass(form.priority)">
                <p class="text-sm font-medium">{{ getPriorityInfo(form.priority) }}</p>
              </div>
            </div>

            <!-- Task Info -->
            <div v-if="task.board" class="mb-6 rounded-md border border-secondary-200 bg-secondary-50 p-4">
              <h3 class="mb-2 text-sm font-medium text-secondary-700">Información de la Tarea</h3>
              <div class="space-y-1 text-sm text-secondary-600">
                <p><strong>Tablero:</strong> {{ task.board.name }}</p>
                <p><strong>Estado actual:</strong> {{ task.status_label }}</p>
                <p><strong>Progreso:</strong> {{ task.progress }}%</p>
                <p><strong>Tiempo transcurrido:</strong> {{ task.hours_elapsed.toFixed(1) }}h / {{ task.target_hours }}h</p>
                <p><strong>Creada:</strong> {{ formatDate(task.created_at) }}</p>
              </div>
            </div>

            <!-- Warning Box -->
            <div class="mb-6 rounded-md border border-yellow-200 bg-yellow-50 p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    ></path>
                  </svg>
                </div>
                <div class="ml-3 flex-1">
                  <h3 class="text-sm font-medium text-yellow-800">Nota importante</h3>
                  <div class="mt-2 text-sm text-yellow-700">
                    <p>
                      Cambiar la prioridad recalculará el tiempo objetivo de la tarea.
                      El estado y las sub-tareas permanecerán sin cambios.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-secondary-200 pt-6">
              <Link
                :href="route('boards.show', task.board_id)"
                class="rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <svg
                  v-if="form.processing"
                  class="-ml-0.5 mr-2 h-4 w-4 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const page = usePage()

const props = defineProps({
  task: Object,
  priorities: Object,
})

const form = useForm({
  title: props.task.title,
  description: props.task.description,
  priority: props.task.priority,
})

const canDelete = computed(() => {
  const user = page.props.auth.user
  // Solo el propietario del tablero puede eliminar (los admins NO pueden eliminar tareas de otros)
  return user?.id === props.task.board?.user_id
})

const submit = () => {
  form.put(route('tasks.update', props.task.id))
}

const deleteTask = () => {
  if (confirm('¿Estás seguro de que deseas eliminar esta tarea? Esta acción no se puede deshacer y eliminará todas las sub-tareas asociadas.')) {
    router.delete(route('tasks.destroy', props.task.id))
  }
}

const getPriorityInfo = (priority) => {
  switch (priority) {
    case 'urgente':
      return 'Urgente: Debe completarse inmediatamente (0 horas)'
    case 'alta':
      return 'Alta: Tiempo objetivo ≤ 1 hora'
    case 'normal':
      return 'Normal: Tiempo objetivo ≤ 6 horas'
    case 'baja':
      return 'Baja: Tiempo objetivo ≤ 48 horas'
    default:
      return ''
  }
}

const getPriorityInfoClass = (priority) => {
  switch (priority) {
    case 'urgente':
      return 'border-red-300 bg-red-50 text-red-700'
    case 'alta':
      return 'border-orange-300 bg-orange-50 text-orange-700'
    case 'normal':
      return 'border-blue-300 bg-blue-50 text-blue-700'
    case 'baja':
      return 'border-gray-300 bg-gray-50 text-gray-700'
    default:
      return ''
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
</script>
