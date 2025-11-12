<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registro" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-secondary-900">Crear Cuenta</h2>
                <p class="mt-2 text-sm text-secondary-600">
                    Completa el formulario para registrarte
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel for="name" value="Nombre Completo" />
                    <TextInput
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Juan Pérez"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Correo Electrónico" />
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
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
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
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
                    {{ form.processing ? 'Registrando...' : 'Crear Cuenta' }}
                </PrimaryButton>
            </form>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-sm text-secondary-600">
                    ¿Ya tienes una cuenta?
                    <Link
                        :href="route('login')"
                        class="font-medium text-primary-600 hover:text-primary-700 focus:outline-none focus:underline"
                    >
                        Inicia sesión aquí
                    </Link>
                </p>
            </div>
        </div>
    </GuestLayout>
</template>
