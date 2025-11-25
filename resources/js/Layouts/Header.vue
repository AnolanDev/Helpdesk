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
          <!-- Quick Actions -->
          <div class="hidden items-center gap-2 md:flex">
            <!-- Crear Ticket -->
            <Link
              :href="route('tickets.create')"
              class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-primary-700"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span>Nuevo Ticket</span>
            </Link>
          </div>

          <!-- Notifications Bell -->
          <div class="relative" ref="notificationDropdown">
            <button
              @click="toggleNotifications"
              class="relative rounded-lg border border-secondary-300 bg-white p-2.5 text-secondary-700 shadow-sm transition-all duration-200 hover:bg-secondary-50"
              title="Notificaciones"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white shadow-sm"
              >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </button>

            <!-- Notifications Dropdown -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="isNotificationsOpen"
                class="absolute right-0 mt-2 w-80 sm:w-96 origin-top-right rounded-lg border border-secondary-200 bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none"
              >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-secondary-200 px-4 py-3">
                  <h3 class="text-sm font-semibold text-secondary-900">Notificaciones</h3>
                  <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-primary-600 hover:text-primary-700 font-medium"
                  >
                    Marcar todas como leídas
                  </button>
                </div>

                <!-- Notifications List -->
                <div class="max-h-96 overflow-y-auto">
                  <div v-if="notifications.length === 0" class="px-4 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-secondary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="mt-2 text-sm text-secondary-500">No tienes notificaciones</p>
                  </div>

                  <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    @click="handleNotificationClick(notification)"
                    class="border-b border-secondary-100 px-4 py-3 transition-colors hover:bg-secondary-50 cursor-pointer"
                    :class="{ 'bg-primary-50/30': !notification.read }"
                  >
                    <div class="flex gap-3">
                      <div
                        class="flex-shrink-0 rounded-full p-2"
                        :class="getNotificationIconClass(notification.color)"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification.icon)" />
                        </svg>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-secondary-900">
                          {{ notification.title }}
                          <span v-if="!notification.read" class="ml-2 inline-block h-2 w-2 rounded-full bg-primary-600"></span>
                        </p>
                        <p class="mt-1 text-sm text-secondary-600 line-clamp-2">
                          {{ notification.message }}
                        </p>
                        <p class="mt-1 text-xs text-secondary-500">
                          {{ notification.time_ago }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-secondary-200 px-4 py-2">
                  <button
                    @click="clearReadNotifications"
                    class="w-full text-center text-xs text-secondary-600 hover:text-secondary-900 font-medium py-1"
                  >
                    Limpiar notificaciones leídas
                  </button>
                </div>
              </div>
            </Transition>
          </div>

          <!-- User Menu Dropdown -->
          <div class="relative" ref="dropdown">
            <button
              @click="toggleDropdown"
              class="flex items-center gap-2 rounded-lg border border-secondary-300 bg-white px-3 py-2 text-sm font-medium text-secondary-700 shadow-sm transition-all duration-200 hover:bg-secondary-50"
            >
              <div class="hidden items-center gap-3 sm:flex">
                <div class="text-right">
                  <p class="text-sm font-medium text-secondary-900">{{ user?.name || 'Usuario' }}</p>
                  <p class="text-xs text-secondary-500">{{ userTypeLabel }}</p>
                </div>
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-sm font-medium text-white shadow-sm">
                  {{ initials }}
                </div>
              </div>
              <svg
                class="h-4 w-4 transition-transform duration-200"
                :class="{ 'rotate-180': isDropdownOpen }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="isDropdownOpen"
                class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg border border-secondary-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
              >
                <div class="py-1">
                  <!-- User Info (Mobile) -->
                  <div class="border-b border-secondary-100 px-4 py-3 sm:hidden">
                    <p class="text-sm font-medium text-secondary-900">{{ user?.name }}</p>
                    <p class="text-xs text-secondary-500">{{ user?.email }}</p>
                    <p class="mt-1 text-xs font-medium text-primary-600">{{ userTypeLabel }}</p>
                  </div>

                  <!-- Dashboard -->
                  <Link
                    :href="route('dashboard')"
                    @click="closeDropdown"
                    class="group flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 transition-colors hover:bg-secondary-50"
                  >
                    <svg class="h-5 w-5 text-secondary-400 group-hover:text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                  </Link>

                  <!-- Mis Tickets -->
                  <Link
                    :href="route('tickets.index', { assigned_to: 'me' })"
                    @click="closeDropdown"
                    class="group flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 transition-colors hover:bg-secondary-50"
                  >
                    <svg class="h-5 w-5 text-secondary-400 group-hover:text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Mis Tickets</span>
                  </Link>

                  <!-- Divider -->
                  <div class="border-t border-secondary-100 my-1"></div>

                  <!-- Perfil -->
                  <Link
                    :href="route('profile.edit')"
                    @click="closeDropdown"
                    class="group flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 transition-colors hover:bg-secondary-50"
                  >
                    <svg class="h-5 w-5 text-secondary-400 group-hover:text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Mi Perfil</span>
                  </Link>

                  <!-- Usuarios (Solo Admin) -->
                  <Link
                    v-if="isAdmin"
                    :href="route('users.index')"
                    @click="closeDropdown"
                    class="group flex items-center gap-3 px-4 py-2 text-sm text-secondary-700 transition-colors hover:bg-secondary-50"
                  >
                    <svg class="h-5 w-5 text-secondary-400 group-hover:text-secondary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Usuarios</span>
                  </Link>

                  <!-- Divider -->
                  <div class="border-t border-secondary-100 my-1"></div>

                  <!-- Logout -->
                  <button
                    @click="logout"
                    class="group flex w-full items-center gap-3 px-4 py-2 text-sm text-red-600 transition-colors hover:bg-red-50"
                  >
                    <svg class="h-5 w-5 text-red-500 group-hover:text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Cerrar Sesión</span>
                  </button>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import axios from 'axios';

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

const userTypeLabel = computed(() => {
  const tipo = user.value?.tipo_usuario;
  const labels = {
    'admin': 'Administrador',
    'tech': 'Técnico',
    'usuario_final': 'Usuario'
  };
  return labels[tipo] || 'Usuario';
});

const isAdmin = computed(() => {
  return user.value?.tipo_usuario === 'admin';
});

// Dropdown state
const isDropdownOpen = ref(false);
const dropdown = ref(null);

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
  if (isDropdownOpen.value) {
    closeNotifications();
  }
};

const closeDropdown = () => {
  isDropdownOpen.value = false;
};

// Notifications state
const isNotificationsOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const notificationDropdown = ref(null);
const notificationInterval = ref(null);
const NOTIFICATION_REFRESH_INTERVAL = 30000; // 30 segundos

const toggleNotifications = async () => {
  isNotificationsOpen.value = !isNotificationsOpen.value;
  if (isNotificationsOpen.value) {
    closeDropdown();
    await fetchNotifications();
  }
};

const closeNotifications = () => {
  isNotificationsOpen.value = false;
};

const fetchNotifications = async () => {
  try {
    const response = await axios.get(route('notifications.index'));
    notifications.value = response.data.notifications;
    unreadCount.value = response.data.unread_count;
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};

const fetchUnreadCount = async () => {
  try {
    const response = await axios.get(route('notifications.unread-count'));
    unreadCount.value = response.data.count;
  } catch (error) {
    console.error('Error fetching unread count:', error);
  }
};

const handleNotificationClick = async (notification) => {
  // Marcar como leída
  if (!notification.read) {
    try {
      await axios.patch(route('notifications.read', notification.id));
      notification.read = true;
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  }

  // Redirigir a la URL del ticket
  if (notification.url) {
    closeNotifications();
    router.visit(notification.url);
  }
};

const markAllAsRead = async () => {
  try {
    await axios.post(route('notifications.mark-all-read'));
    notifications.value.forEach(n => {
      n.read = true;
    });
    unreadCount.value = 0;
  } catch (error) {
    console.error('Error marking all as read:', error);
  }
};

const clearReadNotifications = async () => {
  try {
    await axios.delete(route('notifications.clear-read'));
    notifications.value = notifications.value.filter(n => !n.read);
  } catch (error) {
    console.error('Error clearing read notifications:', error);
  }
};

const getNotificationIcon = (icon) => {
  const icons = {
    'user-plus': 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    'refresh': 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
    'clipboard-list': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    'chat': 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    'check-circle': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'x-circle': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    'arrow-circle-up': 'M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z',
    'bell': 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
  };
  return icons[icon] || icons.bell;
};

const getNotificationIconClass = (color) => {
  const classes = {
    blue: 'bg-blue-100 text-blue-600',
    purple: 'bg-purple-100 text-purple-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    cyan: 'bg-cyan-100 text-cyan-600',
    green: 'bg-green-100 text-green-600',
    gray: 'bg-gray-100 text-gray-600',
    orange: 'bg-orange-100 text-orange-600',
  };
  return classes[color] || classes.gray;
};

const startNotificationRefresh = () => {
  // Cargar notificaciones iniciales
  fetchUnreadCount();

  // Actualizar contador cada 30 segundos
  notificationInterval.value = setInterval(() => {
    if (!document.hidden) {
      fetchUnreadCount();
    }
  }, NOTIFICATION_REFRESH_INTERVAL);
};

const stopNotificationRefresh = () => {
  if (notificationInterval.value) {
    clearInterval(notificationInterval.value);
    notificationInterval.value = null;
  }
};

const handleClickOutside = (event) => {
  if (dropdown.value && !dropdown.value.contains(event.target)) {
    closeDropdown();
  }
  if (notificationDropdown.value && !notificationDropdown.value.contains(event.target)) {
    closeNotifications();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  startNotificationRefresh();

  // Pausar/reanudar cuando cambia la visibilidad de la página
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      stopNotificationRefresh();
    } else {
      startNotificationRefresh();
    }
  });
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  stopNotificationRefresh();
});

const logout = () => {
  closeDropdown();
  router.post(route('logout'));
};
</script>
