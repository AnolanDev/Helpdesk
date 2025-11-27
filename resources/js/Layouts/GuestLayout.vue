<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-gradient-to-br from-secondary-50 to-primary-50">
        <!-- Lado izquierdo - Branding (oculto en móvil) -->
        <div class="hidden w-1/2 bg-gradient-to-br from-primary-600 to-primary-800 p-12 lg:flex lg:flex-col lg:justify-between">
            <div>
                <Link href="/" class="flex items-center gap-3 text-white">
                    <img src="/images/logo.jpg" alt="Logo" class="h-16 w-16 rounded-xl object-cover shadow-lg" />
                    <span class="text-2xl font-bold">HelpDesk</span>
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
                © 2025 HelpDesk. Todos los derechos reservados.
            </div>
        </div>

        <!-- Lado derecho - Formulario -->
        <div class="flex w-full flex-1 items-center justify-center p-6 lg:w-1/2 lg:p-12">
            <div class="w-full max-w-md space-y-8">
                <!-- Logo para móvil -->
                <div class="lg:hidden">
                    <Link href="/" class="flex items-center justify-center gap-3">
                        <img src="/images/logo.jpg" alt="Logo" class="h-16 w-16 rounded-xl object-cover shadow-lg" />
                        <span class="text-2xl font-bold text-secondary-900">HelpDesk</span>
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
