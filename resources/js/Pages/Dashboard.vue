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

      <!-- Task Metrics Section -->
      <div v-if="taskMetrics" class="space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-secondary-900 dark:text-secondary-100">Métricas de Tareas</h2>
          <Link href="/tasks" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
            Ver todas →
          </Link>
        </div>

        <!-- Key Performance Indicators -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Tasa de Cumplimiento -->
          <Card variant="elevated">
            <div class="relative overflow-hidden">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Tasa de Cumplimiento</p>
                  <p class="mt-2 text-3xl font-bold text-primary-600 dark:text-primary-400">{{ taskMetrics.completion_rate }}%</p>
                  <p class="mt-1 text-xs text-secondary-500 dark:text-secondary-500">
                    {{ taskMetrics.completed_tasks }} de {{ taskMetrics.total_tasks }} completadas
                  </p>
                </div>
                <div class="rounded-full bg-primary-100 p-3 dark:bg-primary-900/30">
                  <svg class="h-8 w-8 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
                <div
                  class="h-full rounded-full bg-primary-600 transition-all dark:bg-primary-500"
                  :style="{ width: `${taskMetrics.completion_rate}%` }"
                ></div>
              </div>
            </div>
          </Card>

          <!-- Eficiencia General -->
          <Card variant="elevated">
            <div class="relative overflow-hidden">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Eficiencia General</p>
                  <p class="mt-2 text-3xl font-bold" :class="getEfficiencyColor(taskMetrics.avg_efficiency)">
                    {{ taskMetrics.avg_efficiency }}%
                  </p>
                  <p class="mt-1 text-xs text-secondary-500 dark:text-secondary-500">
                    {{ taskMetrics.avg_time_variance > 0 ? '+' : '' }}{{ taskMetrics.avg_time_variance }}h respecto al objetivo
                  </p>
                </div>
                <div class="rounded-full p-3" :class="getEfficiencyBgColor(taskMetrics.avg_efficiency)">
                  <svg class="h-8 w-8" :class="getEfficiencyIconColor(taskMetrics.avg_efficiency)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  </svg>
                </div>
              </div>
              <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
                <div
                  class="h-full rounded-full transition-all"
                  :class="getEfficiencyBarColor(taskMetrics.avg_efficiency)"
                  :style="{ width: `${Math.min(taskMetrics.avg_efficiency, 150)}%` }"
                ></div>
              </div>
            </div>
          </Card>

          <!-- Tareas Eficientes -->
          <Card variant="elevated">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Muy Eficientes</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ taskMetrics.efficiency_rate }}%</p>
                <p class="mt-1 text-xs text-secondary-500 dark:text-secondary-500">
                  {{ taskMetrics.efficient_tasks }} con &lt;80% del tiempo
                </p>
              </div>
              <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/30">
                <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </Card>

          <!-- Tareas Retrasadas -->
          <Card variant="elevated">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Con Retraso</p>
                <p class="mt-2 text-3xl font-bold" :class="taskMetrics.delayed_rate > 0 ? 'text-red-600 dark:text-red-400' : 'text-secondary-400 dark:text-secondary-500'">
                  {{ taskMetrics.delayed_rate }}%
                </p>
                <p class="mt-1 text-xs text-secondary-500 dark:text-secondary-500">
                  {{ taskMetrics.delayed_tasks }} excedieron el tiempo
                </p>
              </div>
              <div class="rounded-full p-3" :class="taskMetrics.delayed_rate > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-secondary-100 dark:bg-secondary-800'">
                <svg class="h-8 w-8" :class="taskMetrics.delayed_rate > 0 ? 'text-red-600 dark:text-red-400' : 'text-secondary-400 dark:text-secondary-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </Card>
        </div>

        <!-- Charts Section -->
        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Tendencia Últimos 7 Días -->
          <Card variant="elevated">
            <h3 class="mb-4 text-lg font-semibold text-secondary-900 dark:text-secondary-100">Tareas Completadas (Últimos 7 días)</h3>
            <div class="flex h-48 items-end justify-between gap-2">
              <div
                v-for="day in taskMetrics.last_7_days"
                :key="day.date"
                class="group relative flex flex-1 flex-col items-center"
              >
                <div class="relative w-full">
                  <div
                    class="w-full rounded-t-lg bg-primary-500 transition-all hover:bg-primary-600 dark:bg-primary-600 dark:hover:bg-primary-500"
                    :style="{ height: `${Math.max((day.count / Math.max(...taskMetrics.last_7_days.map(d => d.count), 1)) * 160, 8)}px` }"
                  ></div>
                  <div class="absolute -top-6 left-1/2 -translate-x-1/2 rounded bg-secondary-900 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 dark:bg-secondary-100 dark:text-secondary-900">
                    {{ day.count }}
                  </div>
                </div>
                <p class="mt-2 text-xs text-secondary-600 dark:text-secondary-400">{{ day.label }}</p>
              </div>
            </div>
          </Card>

          <!-- Distribución por Estado -->
          <Card variant="elevated">
            <h3 class="mb-4 text-lg font-semibold text-secondary-900 dark:text-secondary-100">Distribución por Estado</h3>
            <div class="space-y-3">
              <div v-for="(count, status) in taskMetrics.by_status" :key="status" class="flex items-center">
                <div class="w-32 text-sm font-medium capitalize text-secondary-700 dark:text-secondary-300">
                  {{ statusLabels[status] }}
                </div>
                <div class="flex-1">
                  <div class="relative h-8 overflow-hidden rounded-full bg-secondary-200 dark:bg-secondary-700">
                    <div
                      class="flex h-full items-center justify-end pr-2 text-xs font-semibold text-white transition-all"
                      :class="getStatusBarClass(status)"
                      :style="{ width: `${taskMetrics.total_tasks > 0 ? (count / taskMetrics.total_tasks) * 100 : 0}%` }"
                    >
                      <span v-if="count > 0">{{ count }}</span>
                    </div>
                  </div>
                </div>
                <div class="ml-3 w-12 text-right text-sm font-medium text-secondary-600 dark:text-secondary-400">
                  {{ taskMetrics.total_tasks > 0 ? Math.round((count / taskMetrics.total_tasks) * 100) : 0 }}%
                </div>
              </div>
            </div>
          </Card>
        </div>

        <!-- Análisis de Eficiencia -->
        <div v-if="taskMetrics.completed_tasks > 0" class="grid gap-6 lg:grid-cols-3">
          <Card variant="elevated" class="lg:col-span-3">
            <h3 class="mb-4 text-lg font-semibold text-secondary-900 dark:text-secondary-100">
              Análisis de Eficiencia (Tiempo Real vs Objetivo)
            </h3>
            <div class="grid gap-6 md:grid-cols-3">
              <!-- Muy Eficientes -->
              <div class="rounded-lg border-2 border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">⚡ Muy Eficientes</h4>
                  <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-xs font-bold text-white dark:bg-emerald-500">
                    {{ taskMetrics.efficiency_rate }}%
                  </span>
                </div>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mb-1">
                  {{ taskMetrics.efficient_tasks }}
                </p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400">
                  Completadas con &lt;80% del tiempo objetivo
                </p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-emerald-200 dark:bg-emerald-900">
                  <div
                    class="h-full rounded-full bg-emerald-600 dark:bg-emerald-500"
                    :style="{ width: `${taskMetrics.efficiency_rate}%` }"
                  ></div>
                </div>
              </div>

              <!-- A Tiempo -->
              <div class="rounded-lg border-2 border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-semibold text-green-900 dark:text-green-100">✓ A Tiempo</h4>
                  <span class="rounded-full bg-green-600 px-2 py-0.5 text-xs font-bold text-white dark:bg-green-500">
                    {{ taskMetrics.on_time_rate }}%
                  </span>
                </div>
                <p class="text-2xl font-bold text-green-700 dark:text-green-300 mb-1">
                  {{ taskMetrics.on_time_tasks }}
                </p>
                <p class="text-xs text-green-600 dark:text-green-400">
                  Completadas dentro del tiempo objetivo
                </p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-green-200 dark:bg-green-900">
                  <div
                    class="h-full rounded-full bg-green-600 dark:bg-green-500"
                    :style="{ width: `${taskMetrics.on_time_rate}%` }"
                  ></div>
                </div>
              </div>

              <!-- Con Retraso -->
              <div class="rounded-lg border-2 border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-semibold text-red-900 dark:text-red-100">⚠ Con Retraso</h4>
                  <span class="rounded-full bg-red-600 px-2 py-0.5 text-xs font-bold text-white dark:bg-red-500">
                    {{ taskMetrics.delayed_rate }}%
                  </span>
                </div>
                <p class="text-2xl font-bold text-red-700 dark:text-red-300 mb-1">
                  {{ taskMetrics.delayed_tasks }}
                </p>
                <p class="text-xs text-red-600 dark:text-red-400">
                  Excedieron el tiempo objetivo
                </p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-red-200 dark:bg-red-900">
                  <div
                    class="h-full rounded-full bg-red-600 dark:bg-red-500"
                    :style="{ width: `${taskMetrics.delayed_rate}%` }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Resumen de eficiencia -->
            <div class="mt-6 rounded-lg bg-secondary-100 p-4 dark:bg-secondary-900/50">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-secondary-700 dark:text-secondary-300">
                    Promedio de Eficiencia General
                  </p>
                  <p class="mt-1 text-xs text-secondary-600 dark:text-secondary-400">
                    100% = perfecto, &lt;100% = eficiente, &gt;100% = con retraso
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-3xl font-bold" :class="getEfficiencyColor(taskMetrics.avg_efficiency)">
                    {{ taskMetrics.avg_efficiency }}%
                  </p>
                  <p class="mt-1 text-sm font-medium text-secondary-600 dark:text-secondary-400">
                    {{ taskMetrics.avg_time_variance > 0 ? '+' : '' }}{{ taskMetrics.avg_time_variance }}h del objetivo
                  </p>
                </div>
              </div>
            </div>
          </Card>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Recent Activity -->
        <Card variant="elevated" class="lg:col-span-2">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100">Actividad Reciente</h2>
            <Link href="/tickets" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
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
          <h2 class="mb-4 text-lg font-semibold text-secondary-900 dark:text-secondary-100">Acciones Rápidas</h2>
          <div class="space-y-3">
            <Link v-if="permissions.can_create_tickets" href="/tickets/create" class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50 dark:border-secondary-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20">
              <div class="rounded-lg bg-primary-100 p-2 dark:bg-primary-900/30">
                <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900 dark:text-secondary-100">Nuevo Ticket</p>
                <p class="text-xs text-secondary-600 dark:text-secondary-400">Crear ticket de soporte</p>
              </div>
            </Link>

            <Link href="/tickets" class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50 dark:border-secondary-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20">
              <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900 dark:text-secondary-100">Mis Tickets</p>
                <p class="text-xs text-secondary-600 dark:text-secondary-400">Ver todos mis tickets</p>
              </div>
            </Link>

            <Link v-if="permissions.can_view_users" href="/users" class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50 dark:border-secondary-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20">
              <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900 dark:text-secondary-100">Gestionar Usuarios</p>
                <p class="text-xs text-secondary-600 dark:text-secondary-400">Ver y administrar usuarios</p>
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
  taskMetrics: Object,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Debug: Log taskMetrics
console.log('taskMetrics recibidas:', props.taskMetrics);

// Status labels for tasks
const statusLabels = {
  creada: 'Creada',
  analizada: 'Analizada',
  programada: 'Programada',
  en_progreso: 'En Progreso',
  finalizada: 'Finalizada',
  cancelada: 'Cancelada',
};

// Function to get status bar colors
const getStatusBarClass = (status) => {
  const classes = {
    creada: 'bg-gray-500',
    analizada: 'bg-blue-500',
    programada: 'bg-purple-500',
    en_progreso: 'bg-yellow-500',
    finalizada: 'bg-green-500',
    cancelada: 'bg-red-500',
  };
  return classes[status] || 'bg-secondary-500';
};

// Functions for efficiency colors
// Eficiencia < 80% = Excelente (verde oscuro)
// Eficiencia 80-100% = Muy bueno (verde)
// Eficiencia 100-120% = Aceptable (amarillo)
// Eficiencia > 120% = Necesita mejorar (rojo)
const getEfficiencyColor = (efficiency) => {
  if (efficiency < 80) return 'text-emerald-600 dark:text-emerald-400';
  if (efficiency <= 100) return 'text-green-600 dark:text-green-400';
  if (efficiency <= 120) return 'text-yellow-600 dark:text-yellow-400';
  return 'text-red-600 dark:text-red-400';
};

const getEfficiencyBgColor = (efficiency) => {
  if (efficiency < 80) return 'bg-emerald-100 dark:bg-emerald-900/30';
  if (efficiency <= 100) return 'bg-green-100 dark:bg-green-900/30';
  if (efficiency <= 120) return 'bg-yellow-100 dark:bg-yellow-900/30';
  return 'bg-red-100 dark:bg-red-900/30';
};

const getEfficiencyIconColor = (efficiency) => {
  if (efficiency < 80) return 'text-emerald-600 dark:text-emerald-400';
  if (efficiency <= 100) return 'text-green-600 dark:text-green-400';
  if (efficiency <= 120) return 'text-yellow-600 dark:text-yellow-400';
  return 'text-red-600 dark:text-red-400';
};

const getEfficiencyBarColor = (efficiency) => {
  if (efficiency < 80) return 'bg-emerald-500 dark:bg-emerald-400';
  if (efficiency <= 100) return 'bg-green-500 dark:bg-green-400';
  if (efficiency <= 120) return 'bg-yellow-500 dark:bg-yellow-400';
  return 'bg-red-500 dark:bg-red-400';
};

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
