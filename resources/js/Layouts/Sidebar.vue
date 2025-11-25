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
      class="fixed left-0 top-16 z-50 h-[calc(100vh-4rem)] w-64 overflow-y-auto border-r border-secondary-200 bg-white shadow-lg lg:sticky lg:z-0 lg:shadow-none"
    >
      <nav class="space-y-6 p-4">
        <!-- Dashboard -->
        <div>
          <Link
            :href="menuItems.dashboard.href"
            @click="$emit('close')"
            :class="[
              'group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200',
              isActive(menuItems.dashboard.href)
                ? 'bg-primary-50 text-primary-700'
                : 'text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900'
            ]"
          >
            <svg
              :class="[
                'h-5 w-5 transition-colors',
                isActive(menuItems.dashboard.href)
                  ? 'text-primary-600'
                  : 'text-secondary-400 group-hover:text-secondary-600'
              ]"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>{{ menuItems.dashboard.name }}</span>
            <div
              v-if="isActive(menuItems.dashboard.href)"
              class="ml-auto h-1.5 w-1.5 rounded-full bg-primary-600"
            ></div>
          </Link>
        </div>

        <!-- Soporte -->
        <div>
          <div class="mb-2 px-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-secondary-500">
              Soporte
            </h3>
          </div>
          <div class="space-y-1">
            <Link
              v-for="item in menuItems.soporte"
              :key="item.name"
              :href="item.href"
              @click="$emit('close')"
              :class="[
                'group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200',
                isActive(item.href)
                  ? 'bg-primary-50 text-primary-700'
                  : 'text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900'
              ]"
            >
              <svg
                :class="[
                  'h-5 w-5 transition-colors',
                  isActive(item.href)
                    ? 'text-primary-600'
                    : 'text-secondary-400 group-hover:text-secondary-600'
                ]"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath" />
                <path v-if="item.iconPath2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath2" />
              </svg>
              <span>{{ item.name }}</span>
              <span
                v-if="item.badge"
                class="ml-auto rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700"
              >
                {{ item.badge }}
              </span>
              <div
                v-if="isActive(item.href)"
                class="ml-auto h-1.5 w-1.5 rounded-full bg-primary-600"
              ></div>
            </Link>
          </div>
        </div>

        <!-- Gestión -->
        <div>
          <div class="mb-2 px-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-secondary-500">
              Gestión
            </h3>
          </div>
          <div class="space-y-1">
            <Link
              v-for="item in menuItems.gestion"
              :key="item.name"
              :href="item.href"
              @click="$emit('close')"
              :class="[
                'group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200',
                isActive(item.href)
                  ? 'bg-primary-50 text-primary-700'
                  : 'text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900'
              ]"
            >
              <svg
                :class="[
                  'h-5 w-5 transition-colors',
                  isActive(item.href)
                    ? 'text-primary-600'
                    : 'text-secondary-400 group-hover:text-secondary-600'
                ]"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath" />
              </svg>
              <span>{{ item.name }}</span>
              <div
                v-if="isActive(item.href)"
                class="ml-auto h-1.5 w-1.5 rounded-full bg-primary-600"
              ></div>
            </Link>
          </div>
        </div>

        <!-- Administración (Solo para Admins) -->
        <div v-if="hasAdminMenu">
          <div class="mb-2 px-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-secondary-500">
              Administración
            </h3>
          </div>
          <div class="space-y-1">
            <Link
              v-for="item in visibleAdminItems"
              :key="item.name"
              :href="item.href"
              @click="$emit('close')"
              :class="[
                'group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200',
                isActive(item.href)
                  ? 'bg-primary-50 text-primary-700'
                  : 'text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900'
              ]"
            >
              <svg
                :class="[
                  'h-5 w-5 transition-colors',
                  isActive(item.href)
                    ? 'text-primary-600'
                    : 'text-secondary-400 group-hover:text-secondary-600'
                ]"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath" />
                <path v-if="item.iconPath2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath2" />
              </svg>
              <span>{{ item.name }}</span>
              <div
                v-if="isActive(item.href)"
                class="ml-auto h-1.5 w-1.5 rounded-full bg-primary-600"
              ></div>
            </Link>
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
      name: 'Tickets',
      href: '/tickets',
      iconPath: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'
    },
    {
      name: 'Mis Tickets',
      href: '/tickets?assigned_to=me',
      iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
    }
  ],
  administracion: [
    {
      name: 'Usuarios',
      href: '/users',
      iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
      adminOnly: true  // Solo para administradores
    },
    {
      name: 'Configuración',
      href: '/configuracion',
      iconPath: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
      iconPath2: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
      adminOnly: true  // Solo para administradores
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
