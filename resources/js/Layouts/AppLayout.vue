<template>
  <div class="min-h-screen bg-gradient-to-br from-secondary-50 via-white to-secondary-50/30">
    <!-- Loading Indicator -->
    <LoadingIndicator />

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

    <!-- Flash Messages -->
    <FlashMessages />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Header from './Header.vue';
import Sidebar from './Sidebar.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import LoadingIndicator from '@/Components/LoadingIndicator.vue';

const sidebarOpen = ref(false);
</script>
