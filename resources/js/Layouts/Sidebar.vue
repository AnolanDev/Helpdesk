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
      <nav class="space-y-1 p-4">
        <Link
          v-for="item in menuItems"
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
          <component
            :is="item.icon"
            :class="[
              'h-5 w-5 transition-colors',
              isActive(item.href)
                ? 'text-primary-600'
                : 'text-secondary-400 group-hover:text-secondary-600'
            ]"
          />
          <span>{{ item.name }}</span>
          <div
            v-if="isActive(item.href)"
            class="ml-auto h-1.5 w-1.5 rounded-full bg-primary-600"
          ></div>
        </Link>
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

const menuItems = [
  {
    name: 'Dashboard',
    href: '/dashboard',
    icon: {
      template: `
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
      `
    }
  },
  {
    name: 'Proyectos',
    href: '/proyectos',
    icon: {
      template: `
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
      `
    }
  },
  {
    name: 'Propiedades',
    href: '/propiedades',
    icon: {
      template: `
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
      `
    }
  },
  {
    name: 'Usuarios',
    href: '/usuarios',
    icon: {
      template: `
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
      `
    }
  },
  {
    name: 'Configuración',
    href: '/configuracion',
    icon: {
      template: `
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
      `
    }
  }
];

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
