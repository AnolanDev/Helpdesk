<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <!-- Header -->
      <div>
        <div class="flex items-center gap-3">
          <Link
            :href="route('tasks.show', task.id)"
            class="rounded-lg p-2 text-secondary-600 transition-all hover:bg-secondary-100"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Editar Tarea</h1>
        </div>
        <p class="mt-2 text-sm text-secondary-600">
          {{ task.task_number }}
        </p>
      </div>

      <!-- Form -->
      <div class="rounded-lg border border-secondary-200 bg-white p-6 shadow-sm">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Título -->
          <div>
            <label for="title" class="block text-sm font-medium text-secondary-700">
              Título <span class="text-red-500">*</span>
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              :class="{ 'border-red-500': form.errors.title }"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
          </div>

          <!-- Descripción -->
          <div>
            <label for="description" class="block text-sm font-medium text-secondary-700">
              Descripción
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              :class="{ 'border-red-500': form.errors.description }"
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <!-- Prioridad y Fecha de Vencimiento -->
          <div class="grid gap-6 sm:grid-cols-2">
            <div>
              <label for="priority" class="block text-sm font-medium text-secondary-700">
                Prioridad <span class="text-red-500">*</span>
              </label>
              <select
                id="priority"
                v-model="form.priority"
                required
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                :class="{ 'border-red-500': form.errors.priority }"
              >
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</p>
            </div>

            <div>
              <label for="due_date" class="block text-sm font-medium text-secondary-700">
                Fecha de Vencimiento
              </label>
              <input
                id="due_date"
                v-model="form.due_date"
                type="datetime-local"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                :class="{ 'border-red-500': form.errors.due_date }"
              />
              <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">{{ form.errors.due_date }}</p>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex items-center justify-between gap-3 pt-4 border-t border-secondary-200">
            <button
              type="button"
              @click="deleteTask"
              class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 transition-all hover:bg-red-50"
            >
              Eliminar
            </button>
            <div class="flex gap-3">
              <Link
                :href="route('tasks.show', task.id)"
                class="rounded-lg border border-secondary-300 px-4 py-2 text-sm font-semibold text-secondary-700 transition-all hover:bg-secondary-50"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700 disabled:opacity-50"
              >
                Guardar Cambios
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  task: Object,
  priorities: Object,
  users: Array,
})

const form = useForm({
  title: props.task.title,
  description: props.task.description,
  priority: props.task.priority,
  due_date: props.task.due_date ? props.task.due_date.substring(0, 16) : '',
})

const submit = () => {
  form.put(route('tasks.update', props.task.id))
}

const deleteTask = () => {
  if (confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
    router.delete(route('tasks.destroy', props.task.id))
  }
}
</script>
