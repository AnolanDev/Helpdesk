<template>
  <header class="sticky top-0 z-50 w-full border-b border-secondary-200 bg-white/95 backdrop-blur-sm shadow-sm">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo y botón menú -->
        <div class="flex items-center gap-4">
          <button
            @click="$emit('toggle-sidebar')"
            class="inline-flex items-center justify-center rounded-lg p-2 text-secondary-700 transition-colors hover:bg-secondary-100 lg:hidden"
            aria-label="Toggle menu"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-primary-600 to-primary-700">
              <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h1 class="text-lg font-semibold text-secondary-900 sm:text-xl">Infra Manager</h1>
          </div>
        </div>

        <!-- Usuario y acciones -->
        <div class="flex items-center gap-3">
          <div class="hidden items-center gap-3 sm:flex">
            <div class="text-right">
              <p class="text-sm font-medium text-secondary-900">{{ user?.name || 'Usuario' }}</p>
              <p class="text-xs text-secondary-500">{{ user?.email || '' }}</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-sm font-medium text-white shadow-sm">
              {{ initials }}
            </div>
          </div>

          <!-- Botón de logout -->
          <button
            @click="logout"
            class="inline-flex items-center gap-2 rounded-lg border border-secondary-300 bg-white px-3 py-2 text-sm font-medium text-secondary-700 shadow-sm transition-all duration-200 hover:bg-secondary-50 sm:px-4"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span class="hidden sm:inline">Salir</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

defineEmits(['toggle-sidebar']);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const initials = computed(() => {
  const name = user.value?.name || 'U';
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
});

const logout = () => {
  router.post(route('logout'));
};
</script>
