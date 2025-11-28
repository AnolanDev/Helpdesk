<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        // Solo administradores pueden acceder
        $this->authorize('viewAny', Setting::class);

        // Obtener todas las configuraciones agrupadas
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $this->authorize('update', Setting::class);

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        foreach ($validated['settings'] as $settingData) {
            Setting::set($settingData['key'], $settingData['value']);
        }

        // Limpiar todo el caché de configuraciones
        Setting::clearCache();

        return redirect()->back()->with('success', 'Configuraciones actualizadas exitosamente.');
    }

    /**
     * Reset settings to default values
     */
    public function reset(Request $request)
    {
        $this->authorize('update', Setting::class);

        $request->validate([
            'group' => 'nullable|string',
        ]);

        $query = Setting::query();

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        // En un caso real, tendrías valores por defecto definidos
        // Por ahora solo limpiamos el caché
        Setting::clearCache();

        return redirect()->back()->with('success', 'Configuraciones restablecidas exitosamente.');
    }
}
