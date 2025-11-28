<template>
  <AppLayout title="Importar Usuarios">
    <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-secondary-900">Importación Masiva de Usuarios</h1>
        <p class="mt-2 text-sm text-secondary-600">
          Carga usuarios desde un archivo Excel o CSV
        </p>
      </div>

      <!-- Botón de descargar plantilla -->
      <div class="mb-6">
        <a
          :href="route('users.import.template')"
          class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-green-700"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Descargar Plantilla Excel
        </a>
      </div>

      <!-- Área de carga de archivo -->
      <div class="mb-8 rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-secondary-900">Cargar Archivo</h2>

        <!-- Drag & Drop Zone -->
        <div
          @drop.prevent="handleDrop"
          @dragover.prevent="isDragging = true"
          @dragleave="isDragging = false"
          :class="[
            'border-2 border-dashed rounded-lg p-8 text-center transition-all',
            isDragging
              ? 'border-primary-500 bg-primary-50'
              : 'border-secondary-300 hover:border-primary-400'
          ]"
        >
          <input
            ref="fileInput"
            type="file"
            @change="handleFileSelect"
            accept=".xlsx,.xls,.csv"
            class="hidden"
          />

          <div v-if="!selectedFile" class="flex flex-col items-center">
            <svg class="mb-4 h-16 w-16 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p class="mb-2 text-sm font-medium text-secondary-900">
              Arrastra y suelta tu archivo aquí
            </p>
            <p class="mb-4 text-xs text-secondary-500">
              o haz clic para seleccionar
            </p>
            <button
              @click="$refs.fileInput.click()"
              type="button"
              class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-700"
            >
              Seleccionar Archivo
            </button>
            <p class="mt-2 text-xs text-secondary-400">
              Formatos: XLSX, XLS, CSV (máx. 5MB)
            </p>
          </div>

          <div v-else class="flex flex-col items-center">
            <svg class="mb-4 h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mb-2 text-sm font-semibold text-secondary-900">{{ selectedFile.name }}</p>
            <p class="mb-4 text-xs text-secondary-500">{{ formatFileSize(selectedFile.size) }}</p>
            <div class="flex gap-2">
              <button
                @click="clearFile"
                type="button"
                class="rounded-lg bg-secondary-100 px-4 py-2 text-sm font-semibold text-secondary-700 transition-colors hover:bg-secondary-200"
              >
                Cambiar Archivo
              </button>
              <button
                @click="submitImport"
                :disabled="isUploading"
                type="button"
                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-700 disabled:opacity-50"
              >
                {{ isUploading ? 'Importando...' : 'Importar Usuarios' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Barra de progreso -->
        <div v-if="isUploading" class="mt-4">
          <div class="overflow-hidden rounded-full bg-secondary-200">
            <div
              class="h-2 bg-primary-600 transition-all duration-300"
              :style="{ width: uploadProgress + '%' }"
            ></div>
          </div>
          <p class="mt-2 text-center text-sm text-secondary-600">
            Procesando archivo... {{ uploadProgress }}%
          </p>
        </div>
      </div>

      <!-- Historial de importaciones -->
      <div class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-secondary-900">Historial de Importaciones</h2>

        <div v-if="imports.data.length === 0" class="text-center py-8">
          <p class="text-secondary-500">No hay importaciones registradas</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Archivo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Fecha
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Total
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Exitosos
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Errores
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-secondary-500">
                  Estado
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-200 bg-white">
              <tr
                v-for="import_record in imports.data"
                :key="import_record.id"
                class="transition-colors hover:bg-secondary-50"
              >
                <td class="whitespace-nowrap px-6 py-4 text-sm text-secondary-900">
                  {{ import_record.original_filename }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-secondary-600">
                  {{ formatDate(import_record.created_at) }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-secondary-900">
                  {{ import_record.total_rows }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-green-600">
                  {{ import_record.successful_rows }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-red-600">
                  {{ import_record.failed_rows }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm">
                  <span
                    :class="getStatusBadgeClass(import_record.status)"
                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                  >
                    {{ getStatusLabel(import_record.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="imports.links.length > 3" class="mt-6 flex items-center justify-center gap-2">
          <Link
            v-for="link in imports.links"
            :key="link.label"
            :href="link.url || '#'"
            :class="[
              link.active
                ? 'bg-primary-600 text-white border-primary-600'
                : 'bg-white text-secondary-700 border-secondary-300 hover:bg-secondary-50',
              !link.url && 'opacity-50 cursor-not-allowed',
              'px-4 py-2 text-sm font-medium border rounded-lg transition-colors'
            ]"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  imports: Object,
});

const fileInput = ref(null);
const selectedFile = ref(null);
const isDragging = ref(false);
const isUploading = ref(false);
const uploadProgress = ref(0);

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    validateAndSetFile(file);
  }
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (file) {
    validateAndSetFile(file);
  }
};

const validateAndSetFile = (file) => {
  const allowedExtensions = ['xlsx', 'xls', 'csv'];
  const extension = file.name.split('.').pop().toLowerCase();
  const maxSize = 5 * 1024 * 1024; // 5MB

  if (!allowedExtensions.includes(extension)) {
    alert('Formato de archivo no válido. Solo se permiten archivos XLSX, XLS o CSV.');
    return;
  }

  if (file.size > maxSize) {
    alert('El archivo es demasiado grande. El tamaño máximo es 5MB.');
    return;
  }

  selectedFile.value = file;
};

const clearFile = () => {
  selectedFile.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const submitImport = () => {
  if (!selectedFile.value) return;

  const formData = new FormData();
  formData.append('file', selectedFile.value);

  isUploading.value = true;
  uploadProgress.value = 0;

  // Simular progreso
  const progressInterval = setInterval(() => {
    if (uploadProgress.value < 90) {
      uploadProgress.value += 10;
    }
  }, 200);

  router.post(route('users.import.store'), formData, {
    onSuccess: () => {
      clearInterval(progressInterval);
      uploadProgress.value = 100;
      setTimeout(() => {
        isUploading.value = false;
        uploadProgress.value = 0;
        clearFile();
      }, 500);
    },
    onError: (errors) => {
      clearInterval(progressInterval);
      isUploading.value = false;
      uploadProgress.value = 0;
      console.error(errors);
    },
  });
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getStatusLabel = (status) => {
  const labels = {
    processing: 'Procesando',
    completed: 'Completado',
    completed_with_errors: 'Con Errores',
    failed: 'Fallido',
  };
  return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    completed_with_errors: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-secondary-100 text-secondary-800';
};
</script>
