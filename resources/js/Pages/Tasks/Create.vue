<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <!-- Header -->
      <div>
        <div class="flex items-center gap-3">
          <Link
            :href="route('tasks.index')"
            class="rounded-lg p-2 text-secondary-600 transition-all hover:bg-secondary-100"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </Link>
          <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Nueva Tarea</h1>
        </div>
        <p class="mt-2 text-sm text-secondary-600">
          Crea una nueva tarea y asígnala a un miembro del equipo
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
                <option value="">Seleccionar...</option>
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

          <!-- Asignado a -->
          <div>
            <label for="assigned_to" class="block text-sm font-medium text-secondary-700">
              Asignar a
            </label>
            <select
              id="assigned_to"
              v-model="form.assigned_to"
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              :class="{ 'border-red-500': form.errors.assigned_to }"
            >
              <option value="">Sin asignar</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
            <p v-if="form.errors.assigned_to" class="mt-1 text-sm text-red-600">{{ form.errors.assigned_to }}</p>
          </div>

          <!-- Empresa, Sucursal, Departamento -->
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <label for="empresa" class="block text-sm font-medium text-secondary-700">
                Empresa
              </label>
              <select
                id="empresa"
                v-model="form.empresa"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              >
                <option value="">Seleccionar...</option>
                <option v-for="empresa in empresas" :key="empresa" :value="empresa">
                  {{ empresa }}
                </option>
              </select>
            </div>

            <div>
              <label for="sucursal" class="block text-sm font-medium text-secondary-700">
                Sucursal
              </label>
              <input
                id="sucursal"
                v-model="form.sucursal"
                type="text"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              />
            </div>

            <div>
              <label for="department" class="block text-sm font-medium text-secondary-700">
                Departamento
              </label>
              <input
                id="department"
                v-model="form.department"
                type="text"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
              />
            </div>
          </div>

          <!-- Botones -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-secondary-200">
            <Link
              :href="route('tasks.index')"
              class="rounded-lg border border-secondary-300 px-4 py-2 text-sm font-semibold text-secondary-700 transition-all hover:bg-secondary-50"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700 disabled:opacity-50"
            >
              <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Crear Tarea
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  priorities: Object,
  users: Array,
  empresas: Array,
})

const form = useForm({
  title: '',
  description: '',
  priority: 'normal',
  assigned_to: '',
  due_date: '',
  empresa: '',
  sucursal: '',
  department: '',
})

const submit = () => {
  form.post(route('tasks.store'))
}
</script>
