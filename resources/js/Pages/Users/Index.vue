<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-secondary-900">Gestión de Usuarios</h1>
          <p class="mt-1 text-sm text-secondary-600">
            Administra los usuarios del sistema
          </p>
        </div>
        <Link
          :href="route('users.create')"
          class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nuevo Usuario
        </Link>
      </div>

      <!-- Filters -->
      <Card variant="elevated">
        <div class="space-y-4">
          <div class="grid gap-4 sm:grid-cols-3">
            <!-- Search -->
            <div>
              <label for="search" class="block text-sm font-medium text-secondary-700">
                Buscar
              </label>
              <input
                id="search"
                v-model="filters.search"
                type="text"
                placeholder="Nombre, email, celular..."
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              />
            </div>

            <!-- Tipo Usuario -->
            <div>
              <label for="tipo_usuario" class="block text-sm font-medium text-secondary-700">
                Tipo de Usuario
              </label>
              <select
                id="tipo_usuario"
                v-model="filters.tipo_usuario"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              >
                <option value="">Todos</option>
                <option v-for="(label, value) in tiposUsuario" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Estado -->
            <div>
              <label for="is_active" class="block text-sm font-medium text-secondary-700">
                Estado
              </label>
              <select
                id="is_active"
                v-model="filters.is_active"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              >
                <option value="">Todos</option>
                <option value="true">Activos</option>
                <option value="false">Inactivos</option>
              </select>
            </div>
          </div>

          <div class="flex gap-2">
            <button
              type="button"
              @click="clearFilters"
              class="rounded-lg border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 hover:bg-secondary-50"
            >
              Limpiar Filtros
            </button>
          </div>
        </div>
      </Card>

      <!-- Users Table -->
      <Card variant="elevated">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700">
                  Usuario
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700">
                  Email / Celular
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700">
                  Empresa / Sucursal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-700">
                  Estado
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-secondary-700">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-200 bg-white">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-secondary-50">
                <td class="whitespace-nowrap px-6 py-4">
                  <div class="text-sm font-medium text-secondary-900">{{ user.name }}</div>
                  <div v-if="user.username" class="text-sm text-secondary-500">@{{ user.username }}</div>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                  <div class="text-sm text-secondary-900">{{ user.email }}</div>
                  <div v-if="user.mobile" class="text-sm text-secondary-500">{{ user.mobile }}</div>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                  <div v-if="user.empresa" class="text-sm text-secondary-900">{{ user.empresa }}</div>
                  <div v-if="user.sucursal" class="text-sm text-secondary-500">{{ user.sucursal }}</div>
                  <span v-if="!user.empresa && !user.sucursal" class="text-sm text-secondary-400">-</span>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                    :class="{
                      'bg-purple-100 text-purple-800': user.tipo_usuario === 'admin',
                      'bg-blue-100 text-blue-800': user.tipo_usuario === 'tech',
                      'bg-gray-100 text-gray-800': user.tipo_usuario === 'usuario_final',
                    }"
                  >
                    {{ tiposUsuario[user.tipo_usuario] }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                    :class="{
                      'bg-green-100 text-green-800': user.is_active,
                      'bg-red-100 text-red-800': !user.is_active,
                    }"
                  >
                    {{ user.is_active ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('users.edit', user.id)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      Editar
                    </Link>
                    <button
                      @click="deleteUser(user)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.links.length > 3" class="border-t border-secondary-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="text-sm text-secondary-700">
              Mostrando {{ users.from }} - {{ users.to }} de {{ users.total }} usuarios
            </div>
            <div class="flex gap-1">
              <component
                v-for="link in users.links"
                :key="link.label"
                :is="link.url ? Link : 'span'"
                :href="link.url"
                :class="[
                  'px-3 py-1 text-sm rounded',
                  link.active
                    ? 'bg-primary-600 text-white'
                    : 'bg-white text-secondary-700 border border-secondary-300',
                  link.url ? 'hover:bg-secondary-50' : 'opacity-50 cursor-not-allowed',
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  users: Object,
  filters: Object,
  tiposUsuario: Object,
});

const filters = reactive({
  search: props.filters.search || '',
  tipo_usuario: props.filters.tipo_usuario || '',
  is_active: props.filters.is_active || '',
});

let searchTimeout = null;

// Watch para aplicar filtros automáticamente
watch(filters, (newFilters) => {
  // Debounce para el campo de búsqueda (espera 500ms después de que el usuario deje de escribir)
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = setTimeout(() => {
    router.get(route('users.index'), newFilters, {
      preserveState: true,
      preserveScroll: true,
      only: ['users'],
    });
  }, 500);
});

const clearFilters = () => {
  filters.search = '';
  filters.tipo_usuario = '';
  filters.is_active = '';
};

const deleteUser = (user) => {
  if (confirm(`¿Estás seguro de eliminar al usuario "${user.name}"?`)) {
    router.delete(route('users.destroy', user.id));
  }
};
</script>
