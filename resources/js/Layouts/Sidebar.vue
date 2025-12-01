<template>
  <!-- Overlay para móvil -->
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isOpen"
      @click="$emit('close')"
      class="fixed inset-0 z-40 bg-secondary-900/50 backdrop-blur-sm lg:hidden"
    ></div>
  </Transition>

  <!-- Sidebar -->
  <Transition
    enter-active-class="transition-transform duration-300 ease-out"
    enter-from-class="-translate-x-full"
    enter-to-class="translate-x-0"
    leave-active-class="transition-transform duration-300 ease-in"
    leave-from-class="translate-x-0"
    leave-to-class="-translate-x-full"
  >
    <aside
      v-if="isOpen || !isMobile"
      class="fixed left-0 top-16 z-50 h-[calc(100vh-4rem)] w-64 overflow-y-auto border-r border-secondary-200 bg-gradient-to-b from-white to-secondary-50/30 shadow-lg lg:sticky lg:z-0 lg:shadow-none"
    >
      <nav class="space-y-1 p-3">
        <!-- Dashboard - Destacado -->
        <Link
          :href="menuItems.dashboard.href"
          @click="$emit('close')"
          :class="[
            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
            isActive(menuItems.dashboard.href)
              ? 'bg-gradient-to-r from-primary-600 to-primary-500 text-white shadow-md shadow-primary-600/30'
              : 'text-secondary-700 hover:bg-white hover:shadow-sm'
          ]"
        >
          <svg
            :class="[
              'h-5 w-5 transition-all duration-200',
              isActive(menuItems.dashboard.href)
                ? 'text-white'
                : 'text-secondary-400 group-hover:text-primary-500 group-hover:scale-110'
            ]"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span>{{ menuItems.dashboard.name }}</span>
        </Link>

        <!-- Separador -->
        <div class="relative py-2">
          <div class="absolute inset-0 flex items-center px-2">
            <div class="w-full border-t border-secondary-200"></div>
          </div>
        </div>

        <!-- Módulo: Soporte Técnico -->
        <div class="pb-2">
          <button
            @click="toggleSection('soporte')"
            class="group flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-secondary-500 transition-colors hover:bg-secondary-100 hover:text-secondary-700"
          >
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <span>Soporte Técnico</span>
            </div>
            <svg
              :class="[
                'h-4 w-4 transition-transform duration-200',
                expandedSections.soporte ? 'rotate-180' : ''
              ]"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-96 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="max-h-96 opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <div v-if="expandedSections.soporte" class="mt-1 space-y-0.5 overflow-hidden pl-2">
              <Link
                v-for="item in menuItems.soporte"
                :key="item.name"
                :href="item.href"
                @click="$emit('close')"
                :class="[
                  'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 relative',
                  isActive(item.href)
                    ? 'bg-primary-50 text-primary-700 shadow-sm'
                    : 'text-secondary-600 hover:bg-white hover:text-secondary-900 hover:shadow-sm'
                ]"
              >
                <div
                  v-if="isActive(item.href)"
                  class="absolute left-0 h-8 w-1 rounded-r-full bg-primary-600"
                ></div>
                <svg
                  :class="[
                    'h-4 w-4 transition-all duration-200',
                    isActive(item.href)
                      ? 'text-primary-600'
                      : 'text-secondary-400 group-hover:text-secondary-600 group-hover:scale-110'
                  ]"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath" />
                  <path v-if="item.iconPath2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath2" />
                </svg>
                <span class="flex-1">{{ item.name }}</span>
                <span
                  v-if="item.badge"
                  class="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white shadow-sm"
                >
                  {{ item.badge }}
                </span>
              </Link>
            </div>
          </Transition>
        </div>

        <!-- Módulo: Gestión de Proyectos -->
        <div v-if="menuItems.gestion.length > 0" class="pb-2">
          <button
            @click="toggleSection('gestion')"
            class="group flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-secondary-500 transition-colors hover:bg-secondary-100 hover:text-secondary-700"
          >
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
              <span>Gestión</span>
            </div>
            <svg
              :class="[
                'h-4 w-4 transition-transform duration-200',
                expandedSections.gestion ? 'rotate-180' : ''
              ]"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-96 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="max-h-96 opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <div v-if="expandedSections.gestion" class="mt-1 space-y-0.5 overflow-hidden pl-2">
              <Link
                v-for="item in menuItems.gestion"
                :key="item.name"
                :href="item.href"
                @click="$emit('close')"
                :class="[
                  'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 relative',
                  isActive(item.href)
                    ? 'bg-primary-50 text-primary-700 shadow-sm'
                    : 'text-secondary-600 hover:bg-white hover:text-secondary-900 hover:shadow-sm'
                ]"
              >
                <div
                  v-if="isActive(item.href)"
                  class="absolute left-0 h-8 w-1 rounded-r-full bg-primary-600"
                ></div>
                <svg
                  :class="[
                    'h-4 w-4 transition-all duration-200',
                    isActive(item.href)
                      ? 'text-primary-600'
                      : 'text-secondary-400 group-hover:text-secondary-600 group-hover:scale-110'
                  ]"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath" />
                </svg>
                <span class="flex-1">{{ item.name }}</span>
              </Link>
            </div>
          </Transition>
        </div>

        <!-- Separador antes de Admin -->
        <div v-if="hasAdminMenu" class="relative py-2">
          <div class="absolute inset-0 flex items-center px-2">
            <div class="w-full border-t border-secondary-200"></div>
          </div>
        </div>

        <!-- Módulo: Administración (Solo Admins) -->
        <div v-if="hasAdminMenu" class="pb-2">
          <button
            @click="toggleSection('administracion')"
            class="group flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wider text-secondary-500 transition-colors hover:bg-secondary-100 hover:text-secondary-700"
          >
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
              <span>Administración</span>
            </div>
            <svg
              :class="[
                'h-4 w-4 transition-transform duration-200',
                expandedSections.administracion ? 'rotate-180' : ''
              ]"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-96 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="max-h-96 opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <div v-if="expandedSections.administracion" class="mt-1 space-y-0.5 overflow-hidden pl-2">
              <Link
                v-for="item in visibleAdminItems"
                :key="item.name"
                :href="item.href"
                @click="$emit('close')"
                :class="[
                  'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200 relative',
                  isActive(item.href)
                    ? 'bg-primary-50 text-primary-700 shadow-sm'
                    : 'text-secondary-600 hover:bg-white hover:text-secondary-900 hover:shadow-sm'
                ]"
              >
                <div
                  v-if="isActive(item.href)"
                  class="absolute left-0 h-8 w-1 rounded-r-full bg-primary-600"
                ></div>
                <svg
                  :class="[
                    'h-4 w-4 transition-all duration-200',
                    isActive(item.href)
                      ? 'text-primary-600'
                      : 'text-secondary-400 group-hover:text-secondary-600 group-hover:scale-110'
                  ]"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath" />
                  <path v-if="item.iconPath2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath2" />
                </svg>
                <span class="flex-1">{{ item.name }}</span>
              </Link>
            </div>
          </Transition>
        </div>

        <!-- Footer del sidebar con información útil -->
        <div class="mt-6 px-3 py-4">
          <div class="rounded-lg bg-gradient-to-br from-primary-50 to-primary-100/50 p-3 shadow-sm">
            <div class="flex items-center gap-2 text-xs font-medium text-primary-900">
              <svg class="h-4 w-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>HelpTech v1.0</span>
            </div>
          </div>
        </div>
      </nav>
    </aside>
  </Transition>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
});

defineEmits(['close']);

const page = usePage();
const isMobile = ref(window.innerWidth < 1024);

// Estado de secciones expandidas
const expandedSections = ref({
  soporte: true,
  gestion: true,
  administracion: true,
});

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section];
};

// Obtener datos del usuario autenticado
const user = computed(() => page.props.auth?.user);

// Verificar si el usuario es administrador
const isAdmin = computed(() => {
  return user.value?.tipo_usuario === 'admin';
});

// Verificar si el usuario es técnico
const isTech = computed(() => {
  return user.value?.tipo_usuario === 'tech';
});

const menuItems = {
  dashboard: {
    name: 'Dashboard',
    href: '/dashboard'
  },
  soporte: [
    {
      name: 'Todos los Tickets',
      href: '/tickets',
      iconPath: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'
    },
    {
      name: 'Mis Tickets',
      href: '/tickets?assigned_to=me',
      iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
    },
    {
      name: 'Mis Tareas',
      href: '/tasks',
      iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
    },
    {
      name: 'Tablero Kanban',
      href: '/tasks/board',
      iconPath: 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'
    }
  ],
  gestion: [],
  administracion: [
    {
      name: 'Usuarios',
      href: '/users',
      iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
      adminOnly: true
    },
    {
      name: 'Importar Usuarios',
      href: '/users-import',
      iconPath: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
      adminOnly: true
    },
    {
      name: 'Configuración',
      href: '/settings',
      iconPath: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
      iconPath2: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
      adminOnly: true
    }
  ]
};

// Filtrar items de administración según permisos
const visibleAdminItems = computed(() => {
  return menuItems.administracion.filter(item => {
    if (item.adminOnly) {
      return isAdmin.value;
    }
    return true;
  });
});

// Determinar si mostrar la sección de administración
const hasAdminMenu = computed(() => {
  return visibleAdminItems.value.length > 0;
});

const isActive = (href) => {
  return page.url === href || page.url.startsWith(href + '/');
};

const handleResize = () => {
  isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});
</script>
