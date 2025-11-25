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
        <Link href="/tickets?status=abierto,en_progreso" class="block">
          <Card variant="elevated" hoverable>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600">Tickets Abiertos</p>
                <p class="mt-2 text-3xl font-bold text-secondary-900">12</p>
                <p class="mt-2 flex items-center text-sm text-orange-600">
                  <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  5 urgentes
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

        <Link href="/users" class="block">
          <Card variant="elevated" hoverable>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600">Usuarios Activos</p>
                <p class="mt-2 text-3xl font-bold text-secondary-900">18</p>
                <p class="mt-2 flex items-center text-sm text-secondary-600">
                  <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                  Sin cambios
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

        <Link href="/tickets?status=resuelto" class="block">
          <Card variant="elevated" hoverable>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600">Tickets Resueltos</p>
                <p class="mt-2 text-3xl font-bold text-secondary-900">45</p>
                <p class="mt-2 flex items-center text-sm text-green-600">
                  <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                  </svg>
                  Este mes
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
            <button class="text-sm font-medium text-primary-600 hover:text-primary-700">
              Ver todo
            </button>
          </div>
          <div class="space-y-4">
            <Link v-for="i in 5" :key="i" :href="`/tickets/${i}`" class="flex items-start gap-4 border-b border-secondary-100 pb-4 last:border-0 last:pb-0 transition-all hover:bg-secondary-50 rounded-lg -mx-2 px-2">
              <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary-100">
                <svg class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-secondary-900">
                  Nuevo ticket creado
                </p>
                <p class="mt-1 text-sm text-secondary-600">
                  Problema de red en sucursal {{ i }}
                </p>
                <p class="mt-1 text-xs text-secondary-500">Hace {{ i }} horas</p>
              </div>
              <div class="flex items-center text-secondary-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </Link>
          </div>
        </Card>

        <!-- Quick Actions -->
        <Card variant="elevated">
          <h2 class="mb-4 text-lg font-semibold text-secondary-900">Acciones Rápidas</h2>
          <div class="space-y-3">
            <a href="/tickets/create" class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-primary-100 p-2">
                <svg class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Nuevo Ticket</p>
                <p class="text-xs text-secondary-600">Crear ticket de soporte</p>
              </div>
            </a>

            <button class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-purple-100 p-2">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Invitar Usuario</p>
                <p class="text-xs text-secondary-600">Agregar miembro</p>
              </div>
            </button>

            <button class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-orange-100 p-2">
                <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Generar Reporte</p>
                <p class="text-xs text-secondary-600">Exportar datos</p>
              </div>
            </button>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

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
