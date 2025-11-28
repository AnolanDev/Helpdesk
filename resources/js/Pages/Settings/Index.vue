<template>
  <AppLayout title="Configuración del Sistema">
    <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-secondary-900">Configuración del Sistema</h1>
        <p class="mt-2 text-sm text-secondary-600">
          Gestiona los parámetros del sistema de tickets y tiempos de SLA
        </p>
      </div>

      <!-- Settings Groups -->
      <div class="space-y-6">
        <!-- SLA Settings -->
        <Card v-if="settings.sla" variant="elevated">
          <template #header>
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-xl font-semibold text-secondary-900">Configuración de SLA</h2>
                <p class="mt-1 text-sm text-secondary-600">
                  Define los tiempos máximos de resolución según la prioridad del ticket
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="resetGroup('sla')"
                  class="rounded-lg border border-secondary-300 bg-white px-4 py-2 text-sm font-medium text-secondary-700 hover:bg-secondary-50 transition-colors"
                >
                  Restablecer
                </button>
              </div>
            </div>
          </template>

          <form @submit.prevent="saveSettings" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div v-for="setting in settings.sla" :key="setting.id">
                <label :for="setting.key" class="block text-sm font-medium text-secondary-700">
                  {{ setting.label }}
                </label>
                <div class="mt-1 relative">
                  <input
                    :id="setting.key"
                    v-model="form[setting.key]"
                    :type="getInputType(setting.type)"
                    class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    :required="true"
                  />
                  <span class="absolute right-3 top-2 text-sm text-secondary-500">
                    {{ getUnit(setting.key) }}
                  </span>
                </div>
                <p v-if="setting.description" class="mt-1 text-sm text-secondary-500">
                  {{ setting.description }}
                </p>
                <!-- Mostrar equivalencia en días -->
                <p v-if="setting.type === 'integer' && setting.key.includes('hours')" class="mt-1 text-xs text-secondary-400">
                  Equivalente: {{ formatHoursToDays(form[setting.key]) }}
                </p>
              </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-secondary-200 pt-4">
              <button
                type="button"
                @click="cancelChanges"
                class="rounded-lg border border-secondary-300 bg-white px-4 py-2 text-sm font-medium text-secondary-700 hover:bg-secondary-50 transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="!hasChanges"
                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Guardar Cambios
              </button>
            </div>
          </form>
        </Card>

        <!-- Preview/Info Card -->
        <Card variant="elevated">
          <template #header>
            <h2 class="text-xl font-semibold text-secondary-900">Vista Previa de Tiempos SLA</h2>
          </template>

          <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <!-- Urgente -->
              <div class="rounded-lg border-2 border-red-200 bg-red-50 p-4">
                <div class="flex items-center gap-2">
                  <div class="rounded-full bg-red-600 p-2">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-red-900">Urgente</p>
                    <p class="text-2xl font-bold text-red-600">{{ form.sla_urgent_hours || 0 }}h</p>
                    <p class="text-xs text-red-700">{{ formatHoursToDays(form.sla_urgent_hours) }}</p>
                  </div>
                </div>
              </div>

              <!-- Alta -->
              <div class="rounded-lg border-2 border-orange-200 bg-orange-50 p-4">
                <div class="flex items-center gap-2">
                  <div class="rounded-full bg-orange-600 p-2">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-orange-900">Alta</p>
                    <p class="text-2xl font-bold text-orange-600">{{ form.sla_high_hours || 0 }}h</p>
                    <p class="text-xs text-orange-700">{{ formatHoursToDays(form.sla_high_hours) }}</p>
                  </div>
                </div>
              </div>

              <!-- Normal -->
              <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-4">
                <div class="flex items-center gap-2">
                  <div class="rounded-full bg-blue-600 p-2">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-blue-900">Normal</p>
                    <p class="text-2xl font-bold text-blue-600">{{ form.sla_normal_hours || 0 }}h</p>
                    <p class="text-xs text-blue-700">{{ formatHoursToDays(form.sla_normal_hours) }}</p>
                  </div>
                </div>
              </div>

              <!-- Baja -->
              <div class="rounded-lg border-2 border-gray-200 bg-gray-50 p-4">
                <div class="flex items-center gap-2">
                  <div class="rounded-full bg-gray-600 p-2">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">Baja</p>
                    <p class="text-2xl font-bold text-gray-600">{{ form.sla_low_hours || 0 }}h</p>
                    <p class="text-xs text-gray-700">{{ formatHoursToDays(form.sla_low_hours) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Warning hours -->
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
              <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                  <p class="text-sm font-medium text-yellow-900">Advertencia de Vencimiento</p>
                  <p class="text-xs text-yellow-700">Se mostrará un badge amarillo cuando falten {{ form.sla_warning_hours || 24 }} horas o menos para el vencimiento</p>
                </div>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  settings: Object,
});

// Form reactive
const form = reactive({});
const originalForm = ref({});

// Inicializar form con valores actuales
onMounted(() => {
  if (props.settings.sla) {
    props.settings.sla.forEach(setting => {
      form[setting.key] = setting.value;
    });
  }
  // Guardar copia para detectar cambios
  originalForm.value = { ...form };
});

// Computed
const hasChanges = computed(() => {
  return JSON.stringify(form) !== JSON.stringify(originalForm.value);
});

// Methods
const getInputType = (type) => {
  return type === 'integer' || type === 'float' ? 'number' : 'text';
};

const getUnit = (key) => {
  if (key.includes('hours')) return 'horas';
  return '';
};

const formatHoursToDays = (hours) => {
  const h = parseInt(hours) || 0;
  if (h < 24) {
    return `${h} horas`;
  }
  const days = Math.floor(h / 24);
  const remainingHours = h % 24;
  if (remainingHours === 0) {
    return `${days} ${days === 1 ? 'día' : 'días'}`;
  }
  return `${days} ${days === 1 ? 'día' : 'días'} y ${remainingHours}h`;
};

const saveSettings = () => {
  const settingsArray = Object.keys(form).map(key => ({
    key,
    value: form[key]
  }));

  router.post(route('settings.update'), {
    settings: settingsArray
  }, {
    preserveScroll: true,
    onSuccess: () => {
      originalForm.value = { ...form };
    }
  });
};

const cancelChanges = () => {
  Object.assign(form, originalForm.value);
};

const resetGroup = (group) => {
  if (confirm('¿Estás seguro de que deseas restablecer las configuraciones de SLA a sus valores por defecto?')) {
    router.post(route('settings.reset'), {
      group
    }, {
      preserveScroll: true,
      onSuccess: () => {
        // Recargar página para obtener valores actualizados
        window.location.reload();
      }
    });
  }
};
</script>
