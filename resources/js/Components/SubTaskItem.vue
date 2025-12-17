<template>
  <div
    class="group flex items-center gap-3 rounded-md border border-secondary-200 bg-white p-3 transition-all hover:border-secondary-300 hover:shadow-sm dark:border-secondary-700 dark:bg-secondary-800 dark:hover:border-secondary-600 dark:hover:shadow-lg"
    :class="{ 'opacity-60': subTask.status === 'completada' }"
  >
    <!-- Drag Handle -->
    <div
      class="cursor-move text-secondary-400 opacity-0 transition-opacity group-hover:opacity-100 dark:text-secondary-500"
      :class="{ 'cursor-not-allowed opacity-0': !canReorder }"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 8h16M4 16h16"
        ></path>
      </svg>
    </div>

    <!-- Checkbox -->
    <input
      type="checkbox"
      :checked="subTask.status === 'completada'"
      @change="toggleStatus"
      class="h-4 w-4 rounded border-secondary-300 text-primary-600 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-secondary-600 dark:bg-secondary-700 dark:focus:ring-offset-secondary-800"
      :disabled="!canEdit"
    />

    <!-- Title (Editable) -->
    <div class="flex-1">
      <input
        v-if="isEditing"
        ref="titleInput"
        v-model="editedTitle"
        type="text"
        class="w-full rounded border-secondary-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-secondary-600 dark:bg-secondary-700 dark:text-secondary-100"
        @blur="saveTitle"
        @keydown.enter="saveTitle"
        @keydown.esc="cancelEdit"
      />
      <p
        v-else
        class="text-sm text-secondary-900 dark:text-secondary-100"
        :class="{ 'line-through': subTask.status === 'completada' }"
        @dblclick="startEdit"
      >
        {{ subTask.title }}
      </p>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-2 opacity-0 transition-opacity group-hover:opacity-100">
      <!-- Edit Button -->
      <button
        v-if="!isEditing && canEdit"
        @click="startEdit"
        type="button"
        class="rounded p-1 text-secondary-500 hover:bg-secondary-100 hover:text-secondary-700 dark:text-secondary-400 dark:hover:bg-secondary-700 dark:hover:text-secondary-200"
        title="Editar"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
          ></path>
        </svg>
      </button>

      <!-- Delete Button -->
      <button
        v-if="canDelete"
        @click="deleteSubTask"
        type="button"
        class="rounded p-1 text-red-500 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300"
        title="Eliminar"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
          ></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  subTask: {
    type: Object,
    required: true,
  },
  canEdit: {
    type: Boolean,
    default: true,
  },
  canDelete: {
    type: Boolean,
    default: true,
  },
  canReorder: {
    type: Boolean,
    default: true,
  },
})

const isEditing = ref(false)
const editedTitle = ref(props.subTask.title)
const titleInput = ref(null)

const toggleStatus = () => {
  if (!props.canEdit) return

  router.patch(
    route('sub-tasks.toggle', props.subTask.id),
    {},
    {
      preserveScroll: true,
      onError: (errors) => {
        console.error('Error al cambiar estado:', errors)
      },
    }
  )
}

const startEdit = () => {
  if (!props.canEdit) return

  isEditing.value = true
  editedTitle.value = props.subTask.title
  nextTick(() => {
    titleInput.value?.focus()
    titleInput.value?.select()
  })
}

const saveTitle = () => {
  if (!editedTitle.value.trim()) {
    cancelEdit()
    return
  }

  if (editedTitle.value === props.subTask.title) {
    isEditing.value = false
    return
  }

  router.patch(
    route('sub-tasks.update', props.subTask.id),
    { title: editedTitle.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        isEditing.value = false
      },
      onError: (errors) => {
        console.error('Error al actualizar:', errors)
      },
    }
  )
}

const cancelEdit = () => {
  isEditing.value = false
  editedTitle.value = props.subTask.title
}

const deleteSubTask = () => {
  if (!props.canDelete) return

  if (confirm('¿Estás seguro de que deseas eliminar esta sub-tarea?')) {
    router.delete(route('sub-tasks.destroy', props.subTask.id), {
      preserveScroll: true,
      onError: (errors) => {
        console.error('Error al eliminar:', errors)
      },
    })
  }
}
</script>
