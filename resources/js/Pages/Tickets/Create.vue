<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('tickets.index')"
          class="rounded-lg p-2 text-secondary-600 transition-colors hover:bg-secondary-100 hover:text-secondary-900"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-secondary-900">Crear Nuevo Ticket</h1>
          <p class="mt-1 text-sm text-secondary-600">
            Reporta un problema o solicita asistencia técnica
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
            <!-- Empresa -->
            <div>
              <label for="empresa" class="block text-sm font-medium text-secondary-700">
                Empresa <span class="text-red-500">*</span>
              </label>
              <select
                id="empresa"
                v-model="form.empresa"
                required
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
                :class="{ 'border-red-500': form.errors.empresa }"
              >
                <option value="">Seleccionar empresa...</option>
                <option v-for="empresa in empresas" :key="empresa" :value="empresa">
                  {{ empresa }}
                </option>
              </select>
              <p v-if="form.errors.empresa" class="mt-1 text-sm text-red-600">
                {{ form.errors.empresa }}
              </p>
            </div>

            <!-- Sucursal -->
            <div>
              <label for="sucursal" class="block text-sm font-medium text-secondary-700">
                Sucursal <span class="text-red-500">*</span>
              </label>
              <select
                id="sucursal"
                v-model="form.sucursal"
                required
                :disabled="!form.empresa"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm disabled:bg-secondary-100 disabled:cursor-not-allowed"
                :class="{ 'border-red-500': form.errors.sucursal }"
              >
                <option value="">{{ form.empresa ? 'Seleccionar sucursal...' : 'Primero seleccione una empresa' }}</option>
                <option v-for="sucursal in availableSucursales" :key="sucursal" :value="sucursal">
                  {{ sucursal }}
                </option>
              </select>
              <p v-if="form.errors.sucursal" class="mt-1 text-sm text-red-600">
                {{ form.errors.sucursal }}
              </p>
            </div>

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

            <!-- Asignar a -->
            <div class="sm:col-span-2">
              <label for="assigned_to" class="block text-sm font-medium text-secondary-700">
                Asignar a (opcional)
              </label>
              <select
                id="assigned_to"
                v-model="form.assigned_to"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
                :class="{ 'border-red-500': form.errors.assigned_to }"
              >
                <option value="">Sin asignar</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
              <p v-if="form.errors.assigned_to" class="mt-1 text-sm text-red-600">
                {{ form.errors.assigned_to }}
              </p>
            </div>
          </div>

          <!-- Help Text -->
          <div class="rounded-lg bg-primary-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-primary-800">Consejos para crear un buen ticket</h3>
                <div class="mt-2 text-sm text-primary-700">
                  <ul class="list-disc space-y-1 pl-5">
                    <li>Sé específico en el título</li>
                    <li>Describe el problema con el mayor detalle posible</li>
                    <li>Incluye pasos para reproducir el problema si aplica</li>
                    <li>Menciona cualquier mensaje de error que hayas recibido</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 border-t border-secondary-200 pt-6">
            <Link
              :href="route('tickets.index')"
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
              {{ form.processing ? 'Creando...' : 'Crear Ticket' }}
            </button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  priorities: Object,
  categories: Object,
  users: Array,
  empresas: Array,
  sucursalesByEmpresa: Object,
});

const form = useForm({
  title: '',
  description: '',
  priority: 'normal',
  category: '',
  location: '',
  department: '',
  empresa: '',
  sucursal: '',
  assigned_to: '',
});

// Computed para obtener las sucursales disponibles según la empresa seleccionada
const availableSucursales = computed(() => {
  if (!form.empresa) {
    return [];
  }
  return props.sucursalesByEmpresa[form.empresa] || [];
});

// Watch para resetear sucursal cuando cambie la empresa
watch(() => form.empresa, (newEmpresa, oldEmpresa) => {
  if (newEmpresa !== oldEmpresa) {
    form.sucursal = '';
  }
});

const submit = () => {
  form.post(route('tickets.store'), {
    onSuccess: () => {
      // El controlador redirige automáticamente
    },
  });
};
</script>
