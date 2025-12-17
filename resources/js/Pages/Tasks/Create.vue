<template>
  <Head title="Crear Tarea" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          :href="route('boards.show', board.id)"
          class="text-secondary-600 hover:text-secondary-900"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </Link>
        <div>
          <h2 class="text-xl font-semibold leading-tight text-secondary-800">Crear Nueva Tarea</h2>
          <p class="text-sm text-secondary-600">en {{ board.name }}</p>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6">
            <!-- Board Info (Read-only) -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-secondary-700">
                Tablero
              </label>
              <div class="mt-1 rounded-md border border-secondary-300 bg-secondary-50 px-3 py-2">
                <div class="flex items-center justify-between">
                  <span class="font-medium text-secondary-900">{{ board.name }}</span>
                  <span class="text-xs text-secondary-500">{{ board.user.name }}</span>
                </div>
                <p v-if="board.description" class="mt-1 text-xs text-secondary-600">
                  {{ board.description }}
                </p>
              </div>
            </div>

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

            <!-- Info Box -->
            <div class="mb-6 rounded-md border border-blue-200 bg-blue-50 p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                  </svg>
                </div>
                <div class="ml-3 flex-1">
                  <h3 class="text-sm font-medium text-blue-800">Información importante</h3>
                  <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-inside list-disc space-y-1">
                      <li>La tarea se creará con estado inicial "Creada"</li>
                      <li>El tiempo objetivo se establece automáticamente según la prioridad</li>
                      <li>Podrás agregar sub-tareas después de crear la tarea</li>
                      <li>El progreso se calculará automáticamente basado en las sub-tareas</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-secondary-200 pt-6">
              <Link
                :href="route('boards.show', board.id)"
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
                {{ form.processing ? 'Creando...' : 'Crear Tarea' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  priorities: Object,
  board: Object,
})

const form = useForm({
  board_id: props.board.id,
  title: '',
  description: '',
  priority: '',
})

const submit = () => {
  form.post(route('tasks.store'), {
    onSuccess: () => {
      // Redirigir al tablero después de crear la tarea
    }
  })
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
</script>
