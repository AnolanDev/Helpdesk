<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="closeOnBackdrop && $emit('close')"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="show"
              class="relative w-full max-w-lg transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all dark:bg-secondary-800"
              @click.stop
            >
              <!-- Icon -->
              <div class="flex items-center justify-center pt-8">
                <div class="rounded-full bg-red-100 p-4 dark:bg-red-900/30">
                  <svg
                    class="h-8 w-8 text-red-600 dark:text-red-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    ></path>
                  </svg>
                </div>
              </div>

              <!-- Content -->
              <div class="px-6 py-6 text-center">
                <h3 class="mb-2 text-xl font-semibold text-secondary-900 dark:text-secondary-100">
                  {{ title }}
                </h3>
                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                  {{ message }}
                </p>
                <p v-if="warningMessage" class="mt-3 text-sm font-medium text-red-600 dark:text-red-400">
                  {{ warningMessage }}
                </p>
              </div>

              <!-- Actions -->
              <div class="flex gap-3 bg-secondary-50 px-6 py-4 dark:bg-secondary-900/50">
                <button
                  type="button"
                  @click="$emit('close')"
                  class="flex-1 rounded-lg border border-secondary-300 bg-white px-4 py-2.5 text-sm font-semibold text-secondary-700 shadow-sm transition-colors hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-200 dark:hover:bg-secondary-700"
                >
                  {{ cancelText }}
                </button>
                <button
                  type="button"
                  @click="$emit('confirm')"
                  :disabled="processing"
                  class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-red-500 dark:hover:bg-red-600"
                >
                  <span v-if="processing" class="flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      ></path>
                    </svg>
                    Eliminando...
                  </span>
                  <span v-else>{{ confirmText }}</span>
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '¿Estás seguro?',
  },
  message: {
    type: String,
    default: 'Esta acción no se puede deshacer.',
  },
  warningMessage: {
    type: String,
    default: null,
  },
  confirmText: {
    type: String,
    default: 'Eliminar',
  },
  cancelText: {
    type: String,
    default: 'Cancelar',
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true,
  },
  processing: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close', 'confirm'])
</script>
