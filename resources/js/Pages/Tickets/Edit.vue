<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('tickets.show', ticket.id)"
          class="rounded-lg p-2 text-secondary-600 transition-colors hover:bg-secondary-100 hover:text-secondary-900"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-secondary-900">Editar Ticket</h1>
          <p class="mt-1 text-sm text-secondary-600">
            {{ ticket.ticket_number }}
          </p>
        </div>
      </div>

      <!-- Form -->
      <Card variant="elevated">
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
              placeholder="Describe brevemente el problema"
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
              :class="{ 'border-red-500': form.errors.title }"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
              {{ form.errors.title }}
            </p>
          </div>

          <!-- Descripción -->
          <div>
            <label for="description" class="block text-sm font-medium text-secondary-700">
              Descripción <span class="text-red-500">*</span>
            </label>
            <textarea
              id="description"
              v-model="form.description"
              required
              rows="6"
              placeholder="Proporciona todos los detalles relevantes del problema..."
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
              :class="{ 'border-red-500': form.errors.description }"
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
              {{ form.errors.description }}
            </p>
          </div>

          <!-- Grid de Campos -->
          <div class="grid gap-6 sm:grid-cols-2">
            <!-- Prioridad -->
            <div>
              <label for="priority" class="block text-sm font-medium text-secondary-700">
                Prioridad <span class="text-red-500">*</span>
              </label>
              <select
                id="priority"
                v-model="form.priority"
                required
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
                :class="{ 'border-red-500': form.errors.priority }"
              >
                <option value="">Seleccionar...</option>
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">
                {{ form.errors.priority }}
              </p>
            </div>

            <!-- Categoría -->
            <div>
              <label for="category" class="block text-sm font-medium text-secondary-700">
                Categoría <span class="text-red-500">*</span>
              </label>
              <select
                id="category"
                v-model="form.category"
                required
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
                :class="{ 'border-red-500': form.errors.category }"
              >
                <option value="">Seleccionar...</option>
                <option v-for="(label, value) in categories" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">
                {{ form.errors.category }}
              </p>
            </div>

            <!-- Ubicación -->
            <div>
              <label for="location" class="block text-sm font-medium text-secondary-700">
                Ubicación
              </label>
              <input
                id="location"
                v-model="form.location"
                type="text"
                placeholder="Ej: Piso 3, Oficina 301"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
                :class="{ 'border-red-500': form.errors.location }"
              />
              <p v-if="form.errors.location" class="mt-1 text-sm text-red-600">
                {{ form.errors.location }}
              </p>
            </div>

            <!-- Departamento -->
            <div>
              <label for="department" class="block text-sm font-medium text-secondary-700">
                Departamento
              </label>
              <input
                id="department"
                v-model="form.department"
                type="text"
                placeholder="Ej: IT, RRHH, Ventas"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
                :class="{ 'border-red-500': form.errors.department }"
              />
              <p v-if="form.errors.department" class="mt-1 text-sm text-red-600">
                {{ form.errors.department }}
              </p>
            </div>

            <!-- Fecha de vencimiento -->
            <div class="sm:col-span-2">
              <label for="due_date" class="block text-sm font-medium text-secondary-700">
                Fecha de vencimiento
              </label>
              <input
                id="due_date"
                v-model="form.due_date"
                type="datetime-local"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
                :class="{ 'border-red-500': form.errors.due_date }"
              />
              <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.due_date }}
              </p>
            </div>
          </div>

          <!-- Info Box -->
          <div class="rounded-lg bg-blue-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-blue-700">
                  Para cambiar el estado o asignar el ticket, utiliza las opciones en la vista de detalle del ticket.
                </p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between border-t border-secondary-200 pt-6">
            <button
              type="button"
              @click="confirmDelete"
              class="rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
              Eliminar Ticket
            </button>

            <div class="flex items-center gap-3">
              <Link
                :href="route('tickets.show', ticket.id)"
                class="rounded-lg border border-secondary-300 bg-white px-4 py-2.5 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50"
              >
                <svg
                  v-if="form.processing"
                  class="h-4 w-4 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  ticket: Object,
  priorities: Object,
  categories: Object,
  users: Array,
});

// Helper function - debe estar antes de ser usada
const formatDateForInput = (date) => {
  // Convertir fecha a formato datetime-local (YYYY-MM-DDTHH:mm)
  const d = new Date(date);
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const form = useForm({
  title: props.ticket.title,
  description: props.ticket.description,
  priority: props.ticket.priority,
  category: props.ticket.category,
  location: props.ticket.location || '',
  department: props.ticket.department || '',
  due_date: props.ticket.due_date ? formatDateForInput(props.ticket.due_date) : '',
});

const submit = () => {
  form.patch(route('tickets.update', props.ticket.id), {
    onSuccess: () => {
      // El controlador redirige automáticamente
    },
  });
};

const confirmDelete = () => {
  if (confirm('¿Estás seguro de que deseas eliminar este ticket? Esta acción no se puede deshacer.')) {
    router.delete(route('tickets.destroy', props.ticket.id));
  }
};
</script>
