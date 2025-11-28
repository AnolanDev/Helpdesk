<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <div class="flex items-center gap-3">
          <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Panel de Control</h1>
          <button
            @click="manualRefresh"
            :disabled="isRefreshing"
            class="rounded-lg p-2 text-secondary-600 transition-all hover:bg-secondary-100 hover:text-secondary-900 disabled:opacity-50"
            :class="{ 'animate-spin': isRefreshing }"
            title="Actualizar dashboard"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </button>
        </div>
        <p class="mt-2 text-sm text-secondary-600">
          Bienvenido al sistema de gestión de infraestructura
          <span v-if="lastRefresh" class="text-xs text-secondary-500">
            • Actualizado {{ lastRefresh }}
          </span>
        </p>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Link href="/tickets" class="block">
          <Card variant="elevated" hoverable>
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <p class="text-sm font-medium text-secondary-600">Tickets Abiertos</p>
                <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.open_tickets }}</p>
                <p class="mt-2 flex items-center text-sm min-h-[20px]" :class="stats.urgent_tickets > 0 ? 'text-orange-600' : 'text-secondary-600'">
                  <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span v-if="stats.urgent_tickets > 0">{{ stats.urgent_tickets }} urgente{{ stats.urgent_tickets > 1 ? 's' : '' }}</span>
                  <span v-else>Sin urgentes</span>
                </p>
              </div>
              <div class="rounded-full bg-orange-100 p-3">
                <svg class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
              </div>
            </div>
          </Card>
        </Link>

        <Link v-if="permissions.can_view_users" href="/users" class="block">
          <Card variant="elevated" hoverable>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600">Usuarios Activos</p>
                <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.total_users }}</p>
                <p class="mt-2 flex items-center text-sm text-secondary-600">
                  <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  En el sistema
                </p>
              </div>
              <div class="rounded-full bg-purple-100 p-3">
                <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
            </div>
          </Card>
        </Link>

        <Link href="/tickets?status=resuelto&show_closed=true" class="block">
          <Card variant="elevated" hoverable>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600">Resueltos este Mes</p>
                <p class="mt-2 text-3xl font-bold text-secondary-900">{{ stats.resolved_this_month }}</p>
                <p class="mt-2 flex items-center text-sm text-green-600">
                  <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ new Date().toLocaleDateString('es-ES', { month: 'long' }) }}
                </p>
              </div>
              <div class="rounded-full bg-green-100 p-3">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </Card>
        </Link>
      </div>

      <!-- Main Content Grid -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Recent Activity -->
        <Card variant="elevated" class="lg:col-span-2">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-secondary-900">Actividad Reciente</h2>
            <Link href="/tickets" class="text-sm font-medium text-primary-600 hover:text-primary-700">
              Ver todo
            </Link>
          </div>
          <div v-if="recentActivities && recentActivities.length > 0" class="space-y-4">
            <Link
              v-for="activity in recentActivities.slice(0, 5)"
              :key="activity.id"
              :href="`/tickets/${activity.ticket_id}`"
              class="flex items-start gap-4 border-b border-secondary-100 pb-4 last:border-0 last:pb-0 transition-all hover:bg-secondary-50 rounded-lg -mx-2 px-2"
            >
              <div :class="[
                'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full',
                `bg-${activity.color}-100`
              ]">
                <svg :class="['h-5 w-5', `text-${activity.color}-600`]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-secondary-900 truncate">
                  {{ activity.description }}
                </p>
                <p v-if="activity.ticket" class="mt-1 text-sm text-secondary-600 truncate">
                  {{ activity.ticket.ticket_number }} - {{ activity.ticket.title }}
                </p>
                <p class="mt-1 text-xs text-secondary-500">{{ activity.time_ago }}</p>
              </div>
              <div class="flex items-center text-secondary-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </Link>
          </div>
          <div v-else class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-sm text-secondary-500">No hay actividad reciente</p>
          </div>
        </Card>

        <!-- Quick Actions -->
        <Card variant="elevated">
          <h2 class="mb-4 text-lg font-semibold text-secondary-900">Acciones Rápidas</h2>
          <div class="space-y-3">
            <Link v-if="permissions.can_create_tickets" href="/tickets/create" class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-primary-100 p-2">
                <svg class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Nuevo Ticket</p>
                <p class="text-xs text-secondary-600">Crear ticket de soporte</p>
              </div>
            </Link>

            <Link href="/tickets" class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-blue-100 p-2">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Mis Tickets</p>
                <p class="text-xs text-secondary-600">Ver todos mis tickets</p>
              </div>
            </Link>

            <Link v-if="permissions.can_view_users" href="/users" class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-purple-100 p-2">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Gestionar Usuarios</p>
                <p class="text-xs text-secondary-600">Ver y administrar usuarios</p>
              </div>
            </Link>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  stats: Object,
  recentActivities: Array,
  permissions: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Auto-refresh state
const isRefreshing = ref(false);
const lastRefresh = ref('');
const refreshInterval = ref(null);
const REFRESH_INTERVAL = 60000; // 60 segundos para dashboard

const refreshDashboard = () => {
  if (isRefreshing.value) return;

  isRefreshing.value = true;

  router.reload({
    preserveState: true,
    preserveScroll: true,
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
  refreshDashboard();
};

const updateLastRefresh = () => {
  const now = new Date();
  lastRefresh.value = now.toLocaleTimeString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

const startAutoRefresh = () => {
  updateLastRefresh();

  refreshInterval.value = setInterval(() => {
    if (!document.hidden) {
      refreshDashboard();
    }
  }, REFRESH_INTERVAL);
};

const stopAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value);
    refreshInterval.value = null;
  }
};

// Lifecycle hooks
onMounted(() => {
  startAutoRefresh();

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
</script>
