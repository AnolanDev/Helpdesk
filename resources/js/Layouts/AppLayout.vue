<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <Header @toggle-sidebar="sidebarOpen = !sidebarOpen" />

    <div class="flex">
      <!-- Sidebar -->
      <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />

      <!-- Contenido principal -->
      <main class="min-h-[calc(100vh-4rem)] w-full flex-1 lg:ml-0">
        <div class="mx-auto max-w-screen-2xl p-4 sm:p-6 lg:p-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Header from './Header.vue';
import Sidebar from './Sidebar.vue';

const sidebarOpen = ref(false);
</script>
