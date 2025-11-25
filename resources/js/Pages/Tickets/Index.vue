<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Tickets de Soporte</h1>
            <button
              @click="manualRefresh"
              :disabled="isRefreshing"
              class="rounded-lg p-2 text-secondary-600 transition-all hover:bg-secondary-100 hover:text-secondary-900 disabled:opacity-50"
              :class="{ 'animate-spin': isRefreshing }"
              title="Actualizar listado"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
          <p class="mt-2 text-sm text-secondary-600">
            Gestiona y da seguimiento a los tickets del helpdesk
            <span v-if="lastRefresh" class="text-xs text-secondary-500">
              • Actualizado {{ lastRefresh }}
            </span>
          </p>
        </div>
        <Link
          :href="route('tickets.create')"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Nuevo Ticket
        </Link>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Abiertos</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.open }}</p>
            </div>
            <div class="rounded-full bg-blue-100 p-3">
              <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </Card>

        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">En Progreso</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.in_progress }}</p>
            </div>
            <div class="rounded-full bg-yellow-100 p-3">
              <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
        </Card>

        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Pendientes</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.pending }}</p>
            </div>
            <div class="rounded-full bg-orange-100 p-3">
              <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </Card>

        <Card variant="elevated">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Vencidos</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.overdue }}</p>
            </div>
            <div class="rounded-full bg-red-100 p-3">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
        </Card>
      </div>

      <!-- Filters and Search -->
      <Card variant="elevated">
        <form @submit.prevent="applyFilters" class="space-y-4">
          <!-- Buscador principal (siempre visible) -->
          <div>
            <label for="search" class="block text-sm font-medium text-secondary-700">Buscar</label>
            <input
              id="search"
              v-model="form.search"
              type="text"
              placeholder="Número, título..."
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
            />
          </div>

          <!-- Filtros adicionales -->
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
              <label for="status" class="block text-sm font-medium text-secondary-700">Estado</label>
              <select
                id="status"
                v-model="form.status"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
              >
                <option value="">Todos</option>
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label for="priority" class="block text-sm font-medium text-secondary-700">Prioridad</label>
              <select
                id="priority"
                v-model="form.priority"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
              >
                <option value="">Todas</option>
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label for="category" class="block text-sm font-medium text-secondary-700">Categoría</label>
              <select
                id="category"
                v-model="form.category"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base md:text-sm"
              >
                <option value="">Todas</option>
                <option v-for="(label, value) in categories" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
          </div>

          <!-- Checkbox y botones -->
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label class="flex items-center gap-2">
              <input
                v-model="form.show_closed"
                type="checkbox"
                class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500 h-5 w-5"
                @change="applyFilters"
              />
              <span class="text-sm text-secondary-700">Mostrar tickets cerrados</span>
            </label>

            <div class="flex gap-2">
              <button
                type="submit"
                class="flex-1 sm:flex-initial rounded-lg bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
              >
                Filtrar
              </button>
              <button
                type="button"
                @click="resetFilters"
                class="flex-1 sm:flex-initial rounded-lg border border-secondary-300 bg-white px-6 py-2.5 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2"
              >
                Limpiar
              </button>
            </div>
          </div>
        </form>
      </Card>

      <!-- Tickets List -->
      <Card variant="elevated">
        <div v-if="tickets.data.length === 0" class="py-12 text-center">
          <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-semibold text-secondary-900">No hay tickets</h3>
          <p class="mt-1 text-sm text-secondary-500">Comienza creando un nuevo ticket de soporte.</p>
        </div>

        <!-- Vista de Cards (Mobile) -->
        <div v-else class="space-y-4 md:hidden">
          <Link
            v-for="ticket in tickets.data"
            :key="ticket.id"
            :href="route('tickets.show', ticket.id)"
            class="block rounded-lg border border-secondary-200 p-4 transition-all hover:border-primary-300 hover:shadow-md"
          >
            <!-- Header con Usuario y Número -->
            <div class="mb-3 flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 text-xs text-secondary-600 mb-1">
                  <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="truncate">{{ ticket.user_name }}</span>
                </div>
                <p class="text-sm font-medium text-primary-600 truncate">
                  {{ ticket.ticket_number }}
                </p>
              </div>
              <svg class="h-5 w-5 flex-shrink-0 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>

            <!-- Título -->
            <h3 class="mb-3 text-sm font-semibold text-secondary-900 line-clamp-2">
              {{ ticket.title }}
            </h3>

            <!-- Badges -->
            <div class="flex flex-wrap gap-2 mb-3">
              <span
                :class="getStatusBadgeClass(ticket.status_color)"
                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
              >
                {{ ticket.status_label }}
              </span>
              <span
                :class="getPriorityBadgeClass(ticket.priority_color)"
                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
              >
                {{ ticket.priority_label }}
              </span>
            </div>

            <!-- Asignado -->
            <div class="flex items-center gap-2 text-xs text-secondary-600">
              <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
              <span class="truncate">{{ ticket.assigned_name || 'Sin asignar' }}</span>
            </div>
          </Link>
        </div>

        <!-- Vista de Tabla (Desktop) -->
        <div v-if="tickets.data.length > 0" class="hidden overflow-x-auto md:block">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Usuario
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Ticket
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Título
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Estado
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Prioridad
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Asignado a
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-200 bg-white">
              <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-secondary-50">
                <td class="whitespace-nowrap px-4 py-4">
                  <div class="text-sm text-secondary-900">
                    {{ ticket.user_name }}
                  </div>
                </td>
                <td class="whitespace-nowrap px-4 py-4">
                  <Link
                    :href="route('tickets.show', ticket.id)"
                    class="text-sm font-medium text-primary-600 hover:text-primary-700"
                  >
                    {{ ticket.ticket_number }}
                  </Link>
                </td>
                <td class="px-4 py-4">
                  <div class="max-w-xs">
                    <Link
                      :href="route('tickets.show', ticket.id)"
                      class="text-sm font-medium text-secondary-900 hover:text-primary-600"
                    >
                      {{ ticket.title }}
                    </Link>
                  </div>
                </td>
                <td class="whitespace-nowrap px-4 py-4">
                  <span
                    :class="getStatusBadgeClass(ticket.status_color)"
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                  >
                    {{ ticket.status_label }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-4 py-4">
                  <span
                    :class="getPriorityBadgeClass(ticket.priority_color)"
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                  >
                    {{ ticket.priority_label }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-4 py-4 text-sm text-secondary-900">
                  {{ ticket.assigned_name || 'Sin asignar' }}
                </td>
                <td class="whitespace-nowrap px-4 py-4 text-right text-sm">
                  <Link
                    :href="route('tickets.show', ticket.id)"
                    class="text-primary-600 hover:text-primary-700"
                  >
                    Ver
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="tickets.data.length > 0" class="mt-4 border-t border-secondary-200 pt-4">
          <div class="mb-3 text-center text-sm text-secondary-700 md:mb-0 md:text-left">
            Mostrando <span class="font-medium">{{ tickets.from }}</span> a
            <span class="font-medium">{{ tickets.to }}</span> de
            <span class="font-medium">{{ tickets.total }}</span> resultados
          </div>
          <div class="flex flex-wrap justify-center gap-2 md:justify-end">
            <Link
              v-for="link in tickets.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                link.active
                  ? 'bg-primary-600 text-white'
                  : 'bg-white text-secondary-700 hover:bg-secondary-50',
                !link.url ? 'cursor-not-allowed opacity-50' : '',
                'rounded-lg border border-secondary-300 px-3 py-2 text-sm font-medium min-w-[40px] flex items-center justify-center',
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  tickets: Object,
  filters: Object,
  statuses: Object,
  priorities: Object,
  categories: Object,
  users: Array,
  stats: Object,
});

const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
  category: props.filters.category || '',
  assigned_to: props.filters.assigned_to || '',
  show_closed: props.filters.show_closed || false,
});

// Auto-refresh state
const isRefreshing = ref(false);
const lastRefresh = ref('');
const refreshInterval = ref(null);
const REFRESH_INTERVAL = 45000; // 45 segundos

const applyFilters = () => {
  router.get(route('tickets.index'), form, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  form.search = '';
  form.status = '';
  form.priority = '';
  form.category = '';
  form.assigned_to = '';
  form.show_closed = false;
  applyFilters();
};

const refreshTickets = () => {
  if (isRefreshing.value) return;

  isRefreshing.value = true;

  router.reload({
    preserveState: true,
    preserveScroll: true,
    only: ['tickets', 'stats'],
    onSuccess: () => {
      updateLastRefresh();
      isRefreshing.value = false;
    },
    onError: () => {
      isRefreshing.value = false;
    },
  });
};

const manualRefresh = () => {
  refreshTickets();
};

const updateLastRefresh = () => {
  const now = new Date();
  lastRefresh.value = now.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

const startAutoRefresh = () => {
  // Actualizar inmediatamente
  updateLastRefresh();

  // Iniciar intervalo de actualización
  refreshInterval.value = setInterval(() => {
    // Solo refrescar si la página está visible
    if (!document.hidden) {
      refreshTickets();
    }
  }, REFRESH_INTERVAL);
};

const stopAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value);
    refreshInterval.value = null;
  }
};

// Pausar auto-refresh cuando el usuario está escribiendo
const handleFocus = () => {
  stopAutoRefresh();
};

const handleBlur = () => {
  startAutoRefresh();
};

// Lifecycle hooks
onMounted(() => {
  startAutoRefresh();

  // Pausar/reanudar cuando cambia la visibilidad de la página
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopAutoRefresh();
    } else {
      startAutoRefresh();
    }
  });
});

onUnmounted(() => {
  stopAutoRefresh();
});

const getStatusBadgeClass = (color) => {
  const classes = {
    blue: 'bg-blue-100 text-blue-800',
    cyan: 'bg-cyan-100 text-cyan-800',
    yellow: 'bg-yellow-100 text-yellow-800',
    orange: 'bg-orange-100 text-orange-800',
    green: 'bg-green-100 text-green-800',
    gray: 'bg-gray-100 text-gray-800',
    red: 'bg-red-100 text-red-800',
  };
  return classes[color] || classes.gray;
};

const getPriorityBadgeClass = (color) => {
  const classes = {
    gray: 'bg-gray-100 text-gray-800',
    blue: 'bg-blue-100 text-blue-800',
    orange: 'bg-orange-100 text-orange-800',
    red: 'bg-red-100 text-red-800',
  };
  return classes[color] || classes.gray;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
