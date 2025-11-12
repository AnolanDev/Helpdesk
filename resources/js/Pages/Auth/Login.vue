<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-secondary-900">Iniciar Sesión</h2>
                <p class="mt-2 text-sm text-secondary-600">
                    Ingresa a tu cuenta para continuar
                </p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="rounded-lg bg-green-50 border border-green-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="ml-3 text-sm font-medium text-green-800">{{ status }}</p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel for="email" value="Correo Electrónico" />
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="tu@email.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Contraseña" />
                    <TextInput
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ml-2 text-sm text-secondary-600">Recordarme</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-primary-600 hover:text-primary-700 focus:outline-none focus:underline"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>

                <PrimaryButton
                    type="submit"
                    class="w-full justify-center"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
                </PrimaryButton>
            </form>

            <!-- Register Link -->
            <div class="text-center">
                <p class="text-sm text-secondary-600">
                    ¿No tienes una cuenta?
                    <Link
                        :href="route('register')"
                        class="font-medium text-primary-600 hover:text-primary-700 focus:outline-none focus:underline"
                    >
                        Regístrate aquí
                    </Link>
                </p>
            </div>
        </div>
    </GuestLayout>
</template>
