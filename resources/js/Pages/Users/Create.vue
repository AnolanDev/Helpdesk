<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('users.index')"
          class="rounded-lg p-2 text-secondary-600 transition-colors hover:bg-secondary-100 hover:text-secondary-900"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-secondary-900">Crear Nuevo Usuario</h1>
          <p class="mt-1 text-sm text-secondary-600">
            Completa el formulario para agregar un nuevo usuario al sistema
          </p>
        </div>
      </div>

      <!-- Form -->
      <Card variant="elevated">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Nombre -->
          <div>
            <label for="name" class="block text-sm font-medium text-secondary-700">
              Nombre Completo <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              placeholder="Nombre completo del usuario"
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              :class="{ 'border-red-500': form.errors.name }"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Grid de 2 columnas -->
          <div class="grid gap-6 sm:grid-cols-2">
            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-secondary-700">
                Email <span class="text-red-500">*</span>
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                placeholder="usuario@ejemplo.com"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.email }"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- Celular -->
            <div>
              <label for="mobile" class="block text-sm font-medium text-secondary-700">
                Celular
              </label>
              <input
                id="mobile"
                v-model="form.mobile"
                type="text"
                placeholder="3001234567"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.mobile }"
              />
              <p v-if="form.errors.mobile" class="mt-1 text-sm text-red-600">
                {{ form.errors.mobile }}
              </p>
            </div>

            <!-- Empresa -->
            <div>
              <label for="empresa" class="block text-sm font-medium text-secondary-700">
                Empresa
              </label>
              <input
                id="empresa"
                v-model="form.empresa"
                type="text"
                placeholder="Nombre de la empresa"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.empresa }"
              />
              <p v-if="form.errors.empresa" class="mt-1 text-sm text-red-600">
                {{ form.errors.empresa }}
              </p>
            </div>

            <!-- Sucursal -->
            <div>
              <label for="sucursal" class="block text-sm font-medium text-secondary-700">
                Sucursal
              </label>
              <input
                id="sucursal"
                v-model="form.sucursal"
                type="text"
                placeholder="Nombre de la sucursal"
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.sucursal }"
              />
              <p v-if="form.errors.sucursal" class="mt-1 text-sm text-red-600">
                {{ form.errors.sucursal }}
              </p>
            </div>

            <!-- Tipo de Usuario -->
            <div>
              <label for="tipo_usuario" class="block text-sm font-medium text-secondary-700">
                Tipo de Usuario <span class="text-red-500">*</span>
              </label>
              <select
                id="tipo_usuario"
                v-model="form.tipo_usuario"
                required
                class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                :class="{ 'border-red-500': form.errors.tipo_usuario }"
              >
                <option value="">Seleccionar...</option>
                <option v-for="(label, value) in tiposUsuario" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="form.errors.tipo_usuario" class="mt-1 text-sm text-red-600">
                {{ form.errors.tipo_usuario }}
              </p>
            </div>

            <!-- Estado Activo -->
            <div class="flex items-center pt-7">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
              />
              <label for="is_active" class="ml-2 block text-sm text-secondary-700">
                Usuario activo
              </label>
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-secondary-700">
              Contraseña <span class="text-red-500">*</span>
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              placeholder="Mínimo 8 caracteres"
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              :class="{ 'border-red-500': form.errors.password }"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-secondary-700">
              Confirmar Contraseña <span class="text-red-500">*</span>
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              placeholder="Repite la contraseña"
              class="mt-1 block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            />
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 border-t border-secondary-200 pt-6">
            <Link
              :href="route('users.index')"
              class="rounded-lg border border-secondary-300 bg-white px-4 py-2.5 text-sm font-semibold text-secondary-700 shadow-sm hover:bg-secondary-50"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
            >
              <svg
                v-if="form.processing"
                class="h-4 w-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ form.processing ? 'Creando...' : 'Crear Usuario' }}
            </button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
  tiposUsuario: Object,
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  mobile: '',
  sucursal: '',
  empresa: '',
  tipo_usuario: 'usuario_final',
  is_active: true,
});

const submit = () => {
  form.post(route('users.store'));
};
</script>
