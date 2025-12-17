<template>
  <Head title="Crear Tablero" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-4">
        <Link
          :href="route('boards.index')"
          class="text-secondary-600 hover:text-secondary-900 dark:text-secondary-400 dark:hover:text-secondary-100"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </Link>
        <h2 class="text-xl font-semibold leading-tight text-secondary-800 dark:text-secondary-100">Crear Nuevo Tablero</h2>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-secondary-800 dark:shadow-lg">
          <form @submit.prevent="submit" class="p-6">
            <!-- Name -->
            <div class="mb-6">
              <label for="name" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">
                Nombre del Tablero <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100 dark:placeholder-secondary-400"
                :class="{ 'border-red-500': form.errors.name }"
                placeholder="Ej: Proyecto Frontend, Infraestructura, etc."
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Description -->
            <div class="mb-6">
              <label for="description" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">
                Descripción
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100 dark:placeholder-secondary-400"
                :class="{ 'border-red-500': form.errors.description }"
                placeholder="Describe el propósito de este tablero..."
              ></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.description }}
              </p>
              <p class="mt-1 text-xs text-secondary-500 dark:text-secondary-400">
                Una breve descripción ayudará a otros a entender el propósito del tablero.
              </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-secondary-200 pt-6 dark:border-secondary-700">
              <Link
                :href="route('boards.index')"
                class="rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-200 dark:hover:bg-secondary-700"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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
                {{ form.processing ? 'Creando...' : 'Crear Tablero' }}
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

const form = useForm({
  name: '',
  description: '',
})

const submit = () => {
  form.post(route('boards.store'))
}
</script>
