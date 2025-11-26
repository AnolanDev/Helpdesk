<template>
  <div class="space-y-4">
    <!-- Timeline Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <h3 class="text-lg font-medium text-secondary-900">Bitácora de Actividad</h3>
      <div class="flex flex-col gap-2 sm:flex-row">
        <button
          @click="exportToExcel"
          class="inline-flex items-center justify-center gap-2 rounded-lg border border-secondary-300 bg-white px-4 py-2.5 text-sm font-medium text-secondary-700 shadow-sm hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 min-h-[44px]"
          :disabled="exporting"
        >
          <svg v-if="!exporting" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <svg v-else class="h-4 w-4 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Exportar a Excel</span>
        </button>
        <button
          @click="exportToPDF"
          class="inline-flex items-center justify-center gap-2 rounded-lg border border-secondary-300 bg-white px-4 py-2.5 text-sm font-medium text-secondary-700 shadow-sm hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 min-h-[44px]"
          :disabled="exporting"
        >
          <svg v-if="!exporting" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <svg v-else class="h-4 w-4 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Exportar a PDF</span>
        </button>
      </div>
    </div>

    <!-- Timeline -->
    <div v-if="activities && activities.length > 0" class="flow-root">
      <ul role="list" class="-mb-8">
        <li v-for="(activity, activityIdx) in activities" :key="activity.id">
          <div class="relative pb-8">
            <!-- Vertical line -->
            <span
              v-if="activityIdx !== activities.length - 1"
              class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-secondary-200"
              aria-hidden="true"
            />

            <div class="relative flex items-start space-x-3">
              <!-- Icon -->
              <div :class="[
                'relative px-1 flex items-center justify-center h-10 w-10 rounded-full ring-8 ring-white',
                getActivityBgColor(activity.color)
              ]">
                <svg :class="['h-5 w-5', getActivityTextColor(activity.color)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIconPath(activity.icon)" />
                </svg>
              </div>

              <!-- Content -->
              <div class="min-w-0 flex-1">
                <div>
                  <div class="text-sm">
                    <span class="font-medium text-secondary-900">{{ activity.description }}</span>
                  </div>
                  <p class="mt-0.5 text-sm text-secondary-500">
                    {{ activity.time_ago }}
                    <span v-if="activity.created_at" class="text-secondary-400">
                      ({{ formatDate(activity.created_at) }})
                    </span>
                  </p>
                </div>

                <!-- Additional details -->
                <div v-if="activity.old_value || activity.new_value" class="mt-2 text-sm text-secondary-700">
                  <div v-if="activity.old_value" class="inline-flex items-center">
                    <span class="text-secondary-500">De:</span>
                    <span class="ml-1 px-2 py-0.5 rounded bg-red-100 text-red-800">{{ activity.old_value }}</span>
                  </div>
                  <div v-if="activity.new_value" class="inline-flex items-center" :class="{ 'ml-3': activity.old_value }">
                    <span class="text-secondary-500">A:</span>
                    <span class="ml-1 px-2 py-0.5 rounded bg-green-100 text-green-800">{{ activity.new_value }}</span>
                  </div>
                </div>

                <!-- Changes JSON details -->
                <div v-if="activity.changes && Object.keys(activity.changes).length > 0" class="mt-2">
                  <details class="text-sm">
                    <summary class="cursor-pointer text-secondary-600 hover:text-secondary-900">Ver detalles</summary>
                    <div class="mt-2 pl-4 border-l-2 border-secondary-200">
                      <dl class="space-y-1">
                        <div v-for="(value, key) in activity.changes" :key="key">
                          <dt class="inline font-medium text-secondary-700">{{ key }}:</dt>
                          <dd class="inline ml-2 text-secondary-600">{{ value || '(vacío)' }}</dd>
                        </div>
                      </dl>
                    </div>
                  </details>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Empty state -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-secondary-900">No hay actividades</h3>
      <p class="mt-1 text-sm text-secondary-500">Las actividades del ticket aparecerán aquí.</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  activities: {
    type: Array,
    default: () => []
  },
  ticketId: {
    type: Number,
    required: true
  }
});

const exporting = ref(false);

const getActivityIconPath = (iconName) => {
  const iconPaths = {
    'plus-circle': 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
    'user-plus': 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    'refresh': 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
    'clipboard-list': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    'flag': 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9',
    'tag': 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    'chat': 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    'check-circle': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'x-circle': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    'arrow-circle-up': 'M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z',
    'edit': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    'clock': 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  };
  return iconPaths[iconName] || iconPaths['clock'];
};

const getActivityBgColor = (color) => {
  const colorMap = {
    'blue': 'bg-blue-500',
    'purple': 'bg-purple-500',
    'yellow': 'bg-yellow-500',
    'orange': 'bg-orange-500',
    'indigo': 'bg-indigo-500',
    'cyan': 'bg-cyan-500',
    'green': 'bg-green-500',
    'gray': 'bg-secondary-500',
  };
  return colorMap[color] || 'bg-secondary-500';
};

const getActivityTextColor = (color) => {
  return 'text-white';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const exportToExcel = async () => {
  exporting.value = true;
  try {
    const response = await axios.get(route('tickets.export-activities', { ticket: props.ticketId, format: 'excel' }), {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `ticket-${props.ticketId}-activities.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting to Excel:', error);
    alert('Error al exportar a Excel');
  } finally {
    exporting.value = false;
  }
};

const exportToPDF = async () => {
  exporting.value = true;
  try {
    const response = await axios.get(route('tickets.export-activities', { ticket: props.ticketId, format: 'pdf' }), {
      responseType: 'blob'
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `ticket-${props.ticketId}-activities.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error exporting to PDF:', error);
    alert('Error al exportar a PDF');
  } finally {
    exporting.value = false;
  }
};
</script>
