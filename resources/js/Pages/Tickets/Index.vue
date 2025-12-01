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

      <!-- Stats Grid (Clickeable para filtrar) -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <button
          @click="filterByStatus(null)"
          class="text-left transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg"
        >
          <Card variant="elevated" hoverable>
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
        </button>

        <button
          @click="filterByStatus('en_progreso')"
          class="text-left transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg"
        >
          <Card variant="elevated" hoverable>
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
        </button>

        <button
          @click="filterByStatus('pendiente')"
          class="text-left transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg"
        >
          <Card variant="elevated" hoverable>
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
        </button>

        <button
          @click="filterOverdue"
          class="relative text-left transition-all hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg"
        >
          <div v-if="stats.overdue > 0" class="absolute -top-2 -right-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white animate-pulse">
            !
          </div>
          <Card variant="elevated" hoverable :class="stats.overdue > 0 ? 'ring-2 ring-red-500' : ''">
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
        </button>
      </div>

      <!-- Filters and Search -->
      <Card variant="elevated">
        <form @submit.prevent="applyFilters" class="space-y-4">
          <!-- Buscador principal con búsqueda en tiempo real -->
          <div>
            <label for="search" class="block text-sm font-medium text-secondary-700">Buscar</label>
            <input
              id="search"
              v-model="form.search"
              @input="debouncedSearch"
              type="text"
              placeholder="Número, título, descripción..."
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
                  <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-xs font-medium text-white">
                    {{ getInitials(ticket.user_name) }}
                  </div>
                  <span class="truncate">{{ ticket.user_name }}</span>
                </div>
                <p class="text-sm font-medium text-primary-600 truncate">
                  {{ ticket.ticket_number }}
                </p>
              </div>
              <!-- Indicador de vencimiento -->
              <div v-if="ticket.is_overdue" class="flex-shrink-0 ml-2">
                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">
                  <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                  </svg>
                  Vencido
                </span>
              </div>
              <div v-else-if="isNearDue(ticket.due_date)" class="flex-shrink-0 ml-2">
                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">
                  <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                  </svg>
                  Próximo
                </span>
              </div>
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

            <!-- Info adicional -->
            <div class="space-y-2 text-xs text-secondary-600">
              <!-- Asignado -->
              <div class="flex items-center gap-2">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span class="truncate">{{ ticket.assigned_name || 'Sin asignar' }}</span>
              </div>
              <!-- Empresa/Sucursal -->
              <div class="flex items-center gap-2">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="truncate">{{ ticket.empresa }} - {{ ticket.sucursal }}</span>
              </div>
              <!-- Fecha -->
              <div class="flex items-center gap-2">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ formatDate(ticket.created_at) }}</span>
              </div>
            </div>
          </Link>
        </div>

        <!-- Vista de Tabla (Desktop) - MEJORADA -->
        <div v-if="tickets.data.length > 0" class="hidden overflow-x-auto md:block">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <!-- Columna Usuario -->
                <th
                  @click="sortBy('user_name')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Usuario
                    <svg v-if="form.sort_by === 'user_name'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Ticket -->
                <th
                  @click="sortBy('ticket_number')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Ticket
                    <svg v-if="form.sort_by === 'ticket_number'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Título -->
                <th
                  @click="sortBy('title')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Título
                    <svg v-if="form.sort_by === 'title'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Estado -->
                <th
                  @click="sortBy('status')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Estado
                    <svg v-if="form.sort_by === 'status'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Prioridad -->
                <th
                  @click="sortBy('priority')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Prioridad
                    <svg v-if="form.sort_by === 'priority'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Empresa -->
                <th
                  @click="sortBy('empresa')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Empresa
                    <svg v-if="form.sort_by === 'empresa'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Sucursal -->
                <th
                  @click="sortBy('sucursal')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Sucursal
                    <svg v-if="form.sort_by === 'sucursal'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Asignado -->
                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Asignado a
                </th>

                <!-- Columna Creado -->
                <th
                  @click="sortBy('created_at')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Creado
                    <svg v-if="form.sort_by === 'created_at'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Actualizado -->
                <th
                  @click="sortBy('updated_at')"
                  class="cursor-pointer px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-secondary-500 hover:bg-secondary-100 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Actualizado
                    <svg v-if="form.sort_by === 'updated_at'" class="h-4 w-4" :class="form.sort_dir === 'asc' ? '' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </div>
                </th>

                <!-- Columna Acciones -->
                <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-200 bg-white">
              <tr
                v-for="(ticket, index) in tickets.data"
                :key="ticket.id"
                :class="index % 2 === 0 ? 'bg-white' : 'bg-secondary-50'"
                class="transition-all hover:bg-primary-50"
              >
                <!-- Usuario con avatar -->
                <td class="whitespace-nowrap px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-medium text-white"
                      :style="{ backgroundColor: getAvatarColor(ticket.user_name) }"
                      :title="ticket.user_name"
                    >
                      {{ getInitials(ticket.user_name) }}
                    </div>
                    <div class="text-sm text-secondary-900 max-w-[150px] truncate">
                      {{ ticket.user_name }}
                    </div>
                  </div>
                </td>

                <!-- Número de ticket con indicador de SLA -->
                <td class="whitespace-nowrap px-6 py-4">
                  <div class="flex items-center gap-2">
                    <Link
                      :href="route('tickets.show', ticket.id)"
                      class="text-sm font-medium text-primary-600 hover:text-primary-700"
                    >
                      {{ ticket.ticket_number }}
                    </Link>
                    <!-- Indicador de vencimiento -->
                    <span v-if="ticket.is_overdue" class="inline-flex items-center" title="Ticket vencido">
                      <svg class="h-4 w-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span v-else-if="isNearDue(ticket.due_date)" class="inline-flex items-center" title="Próximo a vencer">
                      <svg class="h-4 w-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                      </svg>
                    </span>
                  </div>
                </td>

                <!-- Título -->
                <td class="px-6 py-4">
                  <div class="max-w-xs">
                    <Link
                      :href="route('tickets.show', ticket.id)"
                      class="text-sm font-medium text-secondary-900 hover:text-primary-600 line-clamp-2"
                    >
                      {{ ticket.title }}
                    </Link>
                  </div>
                </td>

                <!-- Estado -->
                <td class="whitespace-nowrap px-6 py-4">
                  <span
                    :class="getStatusBadgeClass(ticket.status_color)"
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  >
                    {{ ticket.status_label }}
                  </span>
                </td>

                <!-- Prioridad -->
                <td class="whitespace-nowrap px-6 py-4">
                  <span
                    :class="getPriorityBadgeClass(ticket.priority_color)"
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  >
                    {{ ticket.priority_label }}
                  </span>
                </td>

                <!-- Empresa -->
                <td class="whitespace-nowrap px-6 py-4 text-sm text-secondary-900">
                  {{ ticket.empresa }}
                </td>

                <!-- Sucursal -->
                <td class="whitespace-nowrap px-6 py-4 text-sm text-secondary-900">
                  {{ ticket.sucursal }}
                </td>

                <!-- Asignado con avatar -->
                <td class="whitespace-nowrap px-6 py-4">
                  <div v-if="ticket.assigned_name" class="flex items-center gap-2">
                    <div
                      class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-xs font-medium text-white"
                      :style="{ backgroundColor: getAvatarColor(ticket.assigned_name) }"
                      :title="ticket.assigned_name"
                    >
                      {{ getInitials(ticket.assigned_name) }}
                    </div>
                    <span class="text-sm text-secondary-900 max-w-[120px] truncate">
                      {{ ticket.assigned_name }}
                    </span>
                  </div>
                  <span v-else class="text-sm text-secondary-500 italic">Sin asignar</span>
                </td>

                <!-- Fecha de creación -->
                <td class="whitespace-nowrap px-6 py-4 text-sm text-secondary-900">
                  {{ formatDate(ticket.created_at) }}
                </td>

                <!-- Última actualización -->
                <td class="whitespace-nowrap px-6 py-4 text-sm text-secondary-900">
                  {{ formatDate(ticket.updated_at) }}
                </td>

                <!-- Acciones con dropdown -->
                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                  <div class="relative inline-block text-left">
                    <div class="flex items-center justify-end gap-2">
                      <!-- Botón Ver -->
                      <Link
                        :href="route('tickets.show', ticket.id)"
                        class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition-all hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ver
                      </Link>

                      <!-- Botón de menú -->
                      <button
                        @click.stop="toggleDropdown(ticket.id)"
                        class="inline-flex items-center rounded-lg border border-secondary-300 bg-white p-1.5 text-secondary-700 shadow-sm transition-all hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                      >
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                      </button>
                    </div>

                    <!-- Dropdown menu -->
                    <div
                      v-if="openDropdown === ticket.id"
                      @click.stop
                      class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-lg border border-secondary-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    >
                      <div class="py-1">
                        <Link
                          :href="route('tickets.edit', ticket.id)"
                          class="group flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 transition-colors hover:bg-secondary-50"
                        >
                          <svg class="h-4 w-4 text-secondary-400 group-hover:text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                          Editar
                        </Link>

                        <button
                          @click="changeStatus(ticket.id)"
                          class="group flex w-full items-center gap-3 px-4 py-2 text-sm text-secondary-700 transition-colors hover:bg-secondary-50"
                        >
                          <svg class="h-4 w-4 text-secondary-400 group-hover:text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                          </svg>
                          Cambiar Estado
                        </button>

                        <div class="border-t border-secondary-100 my-1"></div>

                        <button
                          @click="confirmDelete(ticket.id)"
                          class="group flex w-full items-center gap-3 px-4 py-2 text-sm text-red-600 transition-colors hover:bg-red-50"
                        >
                          <svg class="h-4 w-4 text-red-500 group-hover:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          Eliminar
                        </button>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="tickets.data.length > 0" class="mt-6 border-t border-secondary-200 pt-4">
          <div class="mb-3 text-center text-sm text-secondary-700 md:mb-0 md:text-left">
            Mostrando <span class="font-medium">{{ tickets.from }}</span> a
            <span class="font-medium">{{ tickets.to }}</span> de
            <span class="font-medium">{{ tickets.total }}</span> resultados
          </div>
          <div class="flex flex-wrap justify-center gap-2 mt-4 md:justify-end">
            <Link
              v-for="link in tickets.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                link.active
                  ? 'bg-primary-600 text-white border-primary-600'
                  : 'bg-white text-secondary-700 hover:bg-secondary-50 border-secondary-300',
                !link.url ? 'cursor-not-allowed opacity-50' : 'hover:shadow-sm',
                'rounded-lg border px-4 py-2 text-sm font-medium min-w-[40px] flex items-center justify-center transition-all',
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
  slaWarningHours: {
    type: Number,
    default: 24
  },
});

const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
  category: props.filters.category || '',
  assigned_to: props.filters.assigned_to || '',
  show_closed: props.filters.show_closed || false,
  show_overdue: props.filters.show_overdue || false,
  sort_by: props.filters.sort_by || 'created_at',
  sort_dir: props.filters.sort_dir || 'desc',
});

// Auto-refresh state
const isRefreshing = ref(false);
const lastRefresh = ref('');
const refreshInterval = ref(null);
const REFRESH_INTERVAL = 45000; // 45 segundos

// Dropdown state
const openDropdown = ref(null);

// Debounce para búsqueda en tiempo real
let searchTimeout = null;
const debouncedSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

// Filtro rápido desde cards
const filterByStatus = (status) => {
  // Limpiar filtros que puedan interferir
  form.show_overdue = false;

  // Si es null, limpiar el filtro de estado (para mostrar todos los abiertos)
  if (status === null) {
    form.status = '';
    form.show_closed = false; // Asegurar que no muestra cerrados
  } else {
    // Filtrar por estado específico
    form.status = status;
    form.show_closed = false; // Asegurar que no muestra cerrados
  }
  applyFilters();
};

// Filtro de tickets vencidos
const filterOverdue = () => {
  // Limpiar otros filtros y aplicar solo el filtro de vencidos
  form.status = '';
  form.priority = '';
  form.category = '';
  form.assigned_to = '';
  form.show_closed = false;
  form.show_overdue = true;
  applyFilters();
};

// Ordenamiento
const sortBy = (column) => {
  if (form.sort_by === column) {
    form.sort_dir = form.sort_dir === 'asc' ? 'desc' : 'asc';
  } else {
    form.sort_by = column;
    form.sort_dir = 'asc';
  }
  applyFilters();
};

const applyFilters = () => {
  router.get(route('tickets.index'), form, {
    preserveState: true,
    preserveScroll: true,
    only: ['tickets', 'filters', 'stats'],
  });
};

const resetFilters = () => {
  form.search = '';
  form.status = '';
  form.priority = '';
  form.category = '';
  form.assigned_to = '';
  form.show_closed = false;
  form.show_overdue = false;
  form.sort_by = 'created_at';
  form.sort_dir = 'desc';
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
  updateLastRefresh();
  refreshInterval.value = setInterval(() => {
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

// Dropdown de acciones
const toggleDropdown = (ticketId) => {
  openDropdown.value = openDropdown.value === ticketId ? null : ticketId;
};

const changeStatus = (ticketId) => {
  openDropdown.value = null;
  router.visit(route('tickets.show', ticketId));
};

const confirmDelete = (ticketId) => {
  if (confirm('¿Estás seguro de que deseas eliminar este ticket?')) {
    router.delete(route('tickets.destroy', ticketId));
  }
  openDropdown.value = null;
};

// Cerrar dropdown al hacer clic fuera
onMounted(() => {
  startAutoRefresh();

  document.addEventListener('click', () => {
    openDropdown.value = null;
  });

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
  if (searchTimeout) clearTimeout(searchTimeout);
});

// Helper functions
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
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Generar iniciales para avatares
const getInitials = (name) => {
  if (!name) return '?';
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

// Generar color de avatar basado en el nombre
const getAvatarColor = (name) => {
  if (!name) return '#94a3b8'; // gray-400

  const colors = [
    '#3b82f6', // blue-500
    '#8b5cf6', // violet-500
    '#ec4899', // pink-500
    '#f59e0b', // amber-500
    '#10b981', // emerald-500
    '#06b6d4', // cyan-500
    '#6366f1', // indigo-500
    '#14b8a6', // teal-500
  ];

  // Generar un índice basado en el hash del nombre
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }

  const index = Math.abs(hash) % colors.length;
  return colors[index];
};

// Verificar si un ticket está próximo a vencer (menos de 24 horas)
const isNearDue = (dueDate) => {
  if (!dueDate) return false;

  const due = new Date(dueDate);
  const now = new Date();
  const hoursUntilDue = (due - now) / (1000 * 60 * 60);

  return hoursUntilDue > 0 && hoursUntilDue < props.slaWarningHours;
};
</script>
