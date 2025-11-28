<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 left-4 sm:left-auto z-50 flex flex-col gap-2 w-full sm:w-auto sm:max-w-sm">
      <TransitionGroup
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-x-2"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-2"
      >
        <div
          v-for="message in messages"
          :key="message.id"
          :class="[
            'pointer-events-auto w-full overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5',
            getBackgroundClass(message.type)
          ]"
        >
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <svg
                  v-if="message.type === 'success'"
                  class="h-6 w-6 text-green-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg
                  v-else-if="message.type === 'error'"
                  class="h-6 w-6 text-red-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg
                  v-else-if="message.type === 'info'"
                  class="h-6 w-6 text-blue-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg
                  v-else
                  class="h-6 w-6 text-yellow-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="ml-3 flex-1 pt-0.5 min-w-0">
                <p :class="['text-sm font-medium break-words', getTextClass(message.type)]">
                  {{ message.text }}
                </p>
              </div>
              <div class="ml-4 flex flex-shrink-0">
                <button
                  @click="removeMessage(message.id)"
                  :class="[
                    'inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
                    getButtonClass(message.type)
                  ]"
                >
                  <span class="sr-only">Cerrar</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const messages = ref([]);
let messageId = 0;

const addMessage = (text, type = 'info') => {
  const id = ++messageId;
  messages.value.push({ id, text, type });

  // Auto-remove after 5 seconds
  setTimeout(() => {
    removeMessage(id);
  }, 5000);
};

const removeMessage = (id) => {
  const index = messages.value.findIndex(m => m.id === id);
  if (index > -1) {
    messages.value.splice(index, 1);
  }
};

const getBackgroundClass = (type) => {
  const classes = {
    success: 'bg-green-50',
    error: 'bg-red-50',
    info: 'bg-blue-50',
    warning: 'bg-yellow-50',
  };
  return classes[type] || classes.info;
};

const getTextClass = (type) => {
  const classes = {
    success: 'text-green-800',
    error: 'text-red-800',
    info: 'text-blue-800',
    warning: 'text-yellow-800',
  };
  return classes[type] || classes.info;
};

const getButtonClass = (type) => {
  const classes = {
    success: 'text-green-400 hover:text-green-500 focus:ring-green-500',
    error: 'text-red-400 hover:text-red-500 focus:ring-red-500',
    info: 'text-blue-400 hover:text-blue-500 focus:ring-blue-500',
    warning: 'text-yellow-400 hover:text-yellow-500 focus:ring-yellow-500',
  };
  return classes[type] || classes.info;
};

// Watch for flash messages from Laravel
watch(() => page.props, (newProps) => {
  if (newProps.flash?.success) {
    addMessage(newProps.flash.success, 'success');
  }
  if (newProps.flash?.error) {
    addMessage(newProps.flash.error, 'error');
  }
  if (newProps.flash?.info) {
    addMessage(newProps.flash.info, 'info');
  }
  if (newProps.flash?.warning) {
    addMessage(newProps.flash.warning, 'warning');
  }
}, { deep: true, immediate: true });

onMounted(() => {
  // Check for initial flash messages
  const { flash } = page.props;
  if (flash) {
    if (flash.success) addMessage(flash.success, 'success');
    if (flash.error) addMessage(flash.error, 'error');
    if (flash.info) addMessage(flash.info, 'info');
    if (flash.warning) addMessage(flash.warning, 'warning');
  }
});
</script>
