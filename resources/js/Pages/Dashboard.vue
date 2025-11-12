<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-secondary-900 sm:text-3xl">Panel de Control</h1>
        <p class="mt-2 text-sm text-secondary-600">
          Bienvenido al sistema de gestión de infraestructura
        </p>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card variant="elevated" hoverable>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Total Proyectos</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">24</p>
              <p class="mt-2 flex items-center text-sm text-green-600">
                <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                12% desde el mes pasado
              </p>
            </div>
            <div class="rounded-full bg-primary-100 p-3">
              <svg class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
          </div>
        </Card>

        <Card variant="elevated" hoverable>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Propiedades</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">142</p>
              <p class="mt-2 flex items-center text-sm text-green-600">
                <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                8% desde el mes pasado
              </p>
            </div>
            <div class="rounded-full bg-green-100 p-3">
              <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
          </div>
        </Card>

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

        <Card variant="elevated" hoverable>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-secondary-600">Tareas Pendientes</p>
              <p class="mt-2 text-3xl font-bold text-secondary-900">7</p>
              <p class="mt-2 flex items-center text-sm text-red-600">
                <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                3 urgentes
              </p>
            </div>
            <div class="rounded-full bg-orange-100 p-3">
              <svg class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
            </div>
          </div>
        </Card>
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
            <div v-for="i in 5" :key="i" class="flex items-start gap-4 border-b border-secondary-100 pb-4 last:border-0 last:pb-0">
              <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary-100">
                <svg class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-secondary-900">
                  Nuevo proyecto agregado
                </p>
                <p class="mt-1 text-sm text-secondary-600">
                  Torre Residencial Norte - Fase {{ i }}
                </p>
                <p class="mt-1 text-xs text-secondary-500">Hace {{ i }} horas</p>
              </div>
            </div>
          </div>
        </Card>

        <!-- Quick Actions -->
        <Card variant="elevated">
          <h2 class="mb-4 text-lg font-semibold text-secondary-900">Acciones Rápidas</h2>
          <div class="space-y-3">
            <button class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-primary-100 p-2">
                <svg class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Nuevo Proyecto</p>
                <p class="text-xs text-secondary-600">Crear un proyecto</p>
              </div>
            </button>

            <button class="flex w-full items-center gap-3 rounded-lg border border-secondary-200 p-3 text-left transition-all hover:border-primary-300 hover:bg-primary-50">
              <div class="rounded-lg bg-green-100 p-2">
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-secondary-900">Nueva Propiedad</p>
                <p class="text-xs text-secondary-600">Registrar propiedad</p>
              </div>
            </button>

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
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
</script>
