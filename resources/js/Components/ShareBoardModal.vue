<template>
  <Modal :show="show" @close="closeModal" max-width="2xl">
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-secondary-900 dark:text-secondary-100">
          Compartir Tablero
        </h2>
        <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
          Comparte este tablero con otros usuarios para colaborar
        </p>
      </div>

      <!-- Usuarios ya compartidos -->
      <div v-if="board.shared_with && board.shared_with.length > 0" class="mb-6">
        <h3 class="mb-3 text-sm font-medium text-secondary-700 dark:text-secondary-300">
          Usuarios con acceso
        </h3>
        <div class="space-y-2">
          <div
            v-for="sharedUser in board.shared_with"
            :key="sharedUser.id"
            class="flex items-center justify-between rounded-lg border border-secondary-200 bg-secondary-50 p-3 dark:border-secondary-700 dark:bg-secondary-800"
          >
            <div class="flex-1">
              <p class="font-medium text-secondary-900 dark:text-secondary-100">
                {{ sharedUser.name }}
              </p>
              <p class="text-sm text-secondary-600 dark:text-secondary-400">
                {{ sharedUser.email }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <select
                :value="sharedUser.pivot.permission"
                @change="updatePermission(sharedUser.id, $event.target.value)"
                class="rounded-md border-secondary-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
              >
                <option value="read">Solo lectura</option>
                <option value="write">Escritura completa</option>
              </select>
              <button
                @click="removeAccess(sharedUser.id)"
                class="rounded-md p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                title="Remover acceso"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Compartir con nuevo usuario -->
      <div class="mb-6">
        <h3 class="mb-3 text-sm font-medium text-secondary-700 dark:text-secondary-300">
          Compartir con nuevo usuario
        </h3>
        <form @submit.prevent="shareWithUser" class="space-y-4">
          <div>
            <label for="user_id" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">
              Seleccionar usuario
            </label>
            <select
              id="user_id"
              v-model="shareForm.user_id"
              required
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
              :class="{ 'border-red-500': shareForm.errors.user_id }"
            >
              <option value="">Selecciona un usuario...</option>
              <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                {{ user.name }} ({{ user.email }})
              </option>
            </select>
            <p v-if="shareForm.errors.user_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ shareForm.errors.user_id }}
            </p>
          </div>

          <div>
            <label for="permission" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300">
              Nivel de permiso
            </label>
            <select
              id="permission"
              v-model="shareForm.permission"
              required
              class="mt-1 block w-full rounded-md border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
            >
              <option value="read">Solo lectura - Puede ver el tablero y las tareas</option>
              <option value="write">Escritura completa - Puede editar, crear y eliminar</option>
            </select>
            <p v-if="shareForm.errors.permission" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ shareForm.errors.permission }}
            </p>
          </div>

          <div class="flex items-center justify-end gap-3">
            <button
              type="button"
              @click="closeModal"
              class="rounded-md border border-secondary-300 bg-white px-4 py-2 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50 dark:border-secondary-600 dark:bg-secondary-800 dark:text-secondary-200 dark:hover:bg-secondary-700"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="shareForm.processing || !shareForm.user_id"
              class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <svg
                v-if="shareForm.processing"
                class="-ml-0.5 mr-2 h-4 w-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              {{ shareForm.processing ? 'Compartiendo...' : 'Compartir' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Info sobre permisos -->
      <div class="rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
          </div>
          <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
              Sobre los permisos
            </h3>
            <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
              <ul class="list-disc space-y-1 pl-5">
                <li><strong>Solo lectura:</strong> El usuario puede ver el tablero y todas las tareas, pero no puede hacer cambios.</li>
                <li><strong>Escritura completa:</strong> El usuario puede crear, editar y eliminar tareas y subtareas.</li>
                <li>Solo el propietario del tablero puede compartirlo con otros usuarios o eliminarlo.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  board: Object,
})

const emit = defineEmits(['close'])

const availableUsers = ref([])

const shareForm = useForm({
  user_id: '',
  permission: 'read',
})

// Cargar usuarios disponibles cuando se abre el modal
watch(() => props.show, (newValue) => {
  if (newValue) {
    loadAvailableUsers()
    shareForm.reset()
  }
})

const loadAvailableUsers = async () => {
  try {
    const response = await axios.get(route('boards.available-users', props.board.id))
    availableUsers.value = response.data.users
  } catch (error) {
    console.error('Error loading available users:', error)
  }
}

const shareWithUser = () => {
  shareForm.post(route('boards.share', props.board.id), {
    preserveScroll: true,
    onSuccess: () => {
      shareForm.reset()
      loadAvailableUsers()
    },
  })
}

const updatePermission = (userId, permission) => {
  router.patch(
    route('boards.update-share', { board: props.board.id, sharedUser: userId }),
    { permission },
    {
      preserveScroll: true,
    }
  )
}

const removeAccess = (userId) => {
  if (confirm('¿Estás seguro de que deseas remover el acceso a este usuario?')) {
    router.delete(
      route('boards.unshare', { board: props.board.id, sharedUser: userId }),
      {
        preserveScroll: true,
        onSuccess: () => {
          loadAvailableUsers()
        },
      }
    )
  }
}

const closeModal = () => {
  shareForm.reset()
  emit('close')
}
</script>
