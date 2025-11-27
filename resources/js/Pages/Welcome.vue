<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="HelpDesk - Sistema de Soporte Técnico" />

    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="fixed top-0 z-50 w-full border-b border-secondary-100 bg-white/80 backdrop-blur-md">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo.jpg" alt="Logo" class="h-12 w-12 rounded-xl object-cover shadow-sm" />
                        <span class="text-xl font-bold text-secondary-900">HelpDesk</span>
                    </div>

                    <div v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            :href="route('login')"
                            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-primary-700 hover:shadow-md"
                        >
                            Iniciar Sesión
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-16">
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-primary-50 via-white to-secondary-50"></div>
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-32">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
                    <div class="flex flex-col justify-center">
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-primary-100 px-4 py-2 text-sm font-medium text-primary-700 w-fit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Sistema de Gestión Moderna
                        </div>
                        <h1 class="text-4xl font-bold tracking-tight text-secondary-900 sm:text-5xl lg:text-6xl">
                            Soporte técnico
                            <span class="bg-gradient-to-r from-primary-600 to-primary-700 bg-clip-text text-transparent"> simplificado</span>
                        </h1>
                        <p class="mt-6 text-lg text-secondary-600">
                            Sistema de helpdesk completo para gestionar tickets, usuarios y equipos de soporte técnico.
                            Centraliza todas tus solicitudes y mejora la productividad.
                        </p>
                        <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-6 py-3 text-base font-medium text-white shadow-sm transition-all hover:bg-primary-700 hover:shadow-md"
                            >
                                Iniciar Sesión
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="relative rounded-2xl bg-gradient-to-br from-primary-100 to-primary-50 p-8 shadow-soft-lg">
                            <div class="space-y-4">
                                <div class="animate-slide-up rounded-xl bg-white p-6 shadow-soft" style="animation-delay: 0.1s">
                                    <div class="flex items-center gap-4">
                                        <div class="rounded-full bg-primary-100 p-3">
                                            <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-secondary-600">Tickets Activos</p>
                                            <p class="text-2xl font-bold text-secondary-900">24</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="animate-slide-up rounded-xl bg-white p-6 shadow-soft" style="animation-delay: 0.2s">
                                    <div class="flex items-center gap-4">
                                        <div class="rounded-full bg-green-100 p-3">
                                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-secondary-600">Resueltos Hoy</p>
                                            <p class="text-2xl font-bold text-secondary-900">12</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="animate-slide-up rounded-xl bg-white p-6 shadow-soft" style="animation-delay: 0.3s">
                                    <div class="flex items-center gap-4">
                                        <div class="rounded-full bg-purple-100 p-3">
                                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-secondary-600">Usuarios</p>
                                            <p class="text-2xl font-bold text-secondary-900">18</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 lg:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-secondary-900 sm:text-4xl">
                        Todo lo que necesitas en un solo lugar
                    </h2>
                    <p class="mt-4 text-lg text-secondary-600">
                        Funcionalidades diseñadas para optimizar tu gestión diaria
                    </p>
                </div>

                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="group rounded-2xl border border-secondary-200 bg-white p-8 shadow-soft transition-all hover:shadow-soft-lg">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 transition-colors group-hover:bg-primary-200">
                            <svg class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary-900">Gestión de Tickets</h3>
                        <p class="mt-2 text-secondary-600">
                            Crea, asigna y da seguimiento a solicitudes de soporte con un sistema completo de tickets.
                        </p>
                    </div>

                    <div class="group rounded-2xl border border-secondary-200 bg-white p-8 shadow-soft transition-all hover:shadow-soft-lg">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 transition-colors group-hover:bg-green-200">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary-900">Seguimiento SLA</h3>
                        <p class="mt-2 text-secondary-600">
                            Monitorea tiempos de respuesta y resolución con alertas de vencimiento automáticas.
                        </p>
                    </div>

                    <div class="group rounded-2xl border border-secondary-200 bg-white p-8 shadow-soft transition-all hover:shadow-soft-lg">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 transition-colors group-hover:bg-purple-200">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary-900">Gestión de Equipos</h3>
                        <p class="mt-2 text-secondary-600">
                            Administra roles, permisos y colaboración entre los miembros de tu equipo.
                        </p>
                    </div>

                    <div class="group rounded-2xl border border-secondary-200 bg-white p-8 shadow-soft transition-all hover:shadow-soft-lg">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 transition-colors group-hover:bg-orange-200">
                            <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary-900">Bitácora Completa</h3>
                        <p class="mt-2 text-secondary-600">
                            Historial detallado de todas las acciones realizadas en cada ticket con exportación a PDF y Excel.
                        </p>
                    </div>

                    <div class="group rounded-2xl border border-secondary-200 bg-white p-8 shadow-soft transition-all hover:shadow-soft-lg">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 transition-colors group-hover:bg-blue-200">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary-900">Acceso Móvil</h3>
                        <p class="mt-2 text-secondary-600">
                            Diseño responsive que funciona perfectamente en cualquier dispositivo.
                        </p>
                    </div>

                    <div class="group rounded-2xl border border-secondary-200 bg-white p-8 shadow-soft transition-all hover:shadow-soft-lg">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 transition-colors group-hover:bg-red-200">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary-900">Notificaciones</h3>
                        <p class="mt-2 text-secondary-600">
                            Alertas en tiempo real para asignaciones, cambios de estado y actualizaciones de tickets.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-primary-600 to-primary-800 py-20">
            <div class="absolute inset-0 bg-grid-white/10"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">
                        ¿Listo para comenzar?
                    </h2>
                    <p class="mt-4 text-lg text-primary-100">
                        Únete a HelpDesk y lleva tu gestión al siguiente nivel
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-6 py-3 text-base font-medium text-primary-600 shadow-sm transition-all hover:bg-primary-50"
                        >
                            Crear cuenta gratis
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-secondary-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="flex items-center justify-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-primary-600 to-primary-700">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-secondary-900">HelpDesk</span>
                    </div>
                    <p class="mt-4 text-sm text-secondary-600">
                        © 2025 HelpDesk. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
