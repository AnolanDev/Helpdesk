<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-gradient-to-br from-secondary-50 to-primary-50">
        <!-- Lado izquierdo - Branding (oculto en móvil) -->
        <div class="hidden w-1/2 bg-gradient-to-br from-primary-600 to-primary-800 p-12 lg:flex lg:flex-col lg:justify-between">
            <div>
                <Link href="/" class="flex items-center gap-3 text-white">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold">Infra Manager</span>
                </Link>
            </div>

            <div class="space-y-6">
                <h2 class="text-4xl font-bold text-white">
                    Sistema de Helpdesk
                </h2>
                <p class="text-lg text-primary-100">
                    Gestiona tickets de soporte técnico, usuarios y equipos desde una plataforma moderna y eficiente.
                </p>
                <div class="flex gap-8 pt-8">
                    <div>
                        <div class="text-3xl font-bold text-white">24+</div>
                        <div class="text-sm text-primary-200">Tickets Activos</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">12+</div>
                        <div class="text-sm text-primary-200">Resueltos Hoy</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-white">18+</div>
                        <div class="text-sm text-primary-200">Usuarios</div>
                    </div>
                </div>
            </div>

            <div class="text-sm text-primary-200">
                © 2025 Infra Manager. Todos los derechos reservados.
            </div>
        </div>

        <!-- Lado derecho - Formulario -->
        <div class="flex w-full flex-1 items-center justify-center p-6 lg:w-1/2 lg:p-12">
            <div class="w-full max-w-md space-y-8">
                <!-- Logo para móvil -->
                <div class="lg:hidden">
                    <Link href="/" class="flex items-center justify-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary-600 to-primary-700">
                            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-secondary-900">Infra Manager</span>
                    </Link>
                </div>

                <!-- Contenido del formulario -->
                <div class="rounded-2xl bg-white p-8 shadow-soft-lg">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
