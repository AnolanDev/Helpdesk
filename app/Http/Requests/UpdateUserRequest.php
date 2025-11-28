<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        if (!$user) {
            return false;
        }

        // Verificar si puede actualizar el usuario
        if (!$this->user()->can('update', $user)) {
            return false;
        }

        // Verificar si está intentando cambiar el tipo de usuario
        if ($this->has('tipo_usuario') && $this->tipo_usuario !== $user->tipo_usuario) {
            return $this->user()->can('changeTipoUsuario', $user);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'mobile' => ['nullable', 'string', 'max:255'],
            'sucursal' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'tipo_usuario' => ['required', 'in:' . implode(',', array_keys(User::getTiposUsuario()))],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'mobile' => 'teléfono móvil',
            'sucursal' => 'sucursal',
            'empresa' => 'empresa',
            'tipo_usuario' => 'tipo de usuario',
            'is_active' => 'estado activo',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'tipo_usuario.required' => 'El tipo de usuario es obligatorio.',
            'tipo_usuario.in' => 'El tipo de usuario seleccionado no es válido.',
        ];
    }
}
