<?php

namespace App\Http\Controllers;

use App\Exports\UsersTemplateExport;
use App\Imports\UsersImport;
use App\Models\UserImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class UserImportController extends Controller
{
    /**
     * Mostrar la página de importación
     */
    public function index()
    {
        $this->authorize('viewAny', \App\Models\User::class);

        $imports = UserImport::with('user')
            ->where('user_id', auth()->id())
            ->orWhereHas('user', function ($query) {
                $query->where('tipo_usuario', 'admin');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Users/Import', [
            'imports' => $imports,
        ]);
    }

    /**
     * Descargar plantilla de Excel
     */
    public function downloadTemplate()
    {
        return Excel::download(new UsersTemplateExport(), 'plantilla_usuarios.xlsx');
    }

    /**
     * Importar usuarios desde archivo
     */
    public function import(Request $request)
    {
        $this->authorize('create', \App\Models\User::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ]);

        try {
            // Guardar archivo temporalmente
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('imports', $filename, 'local');

            // Crear registro de importación
            $userImport = UserImport::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Procesar importación
            $import = new UsersImport($userImport);
            Excel::import($import, storage_path('app/' . $path));

            // Actualizar estado
            if ($userImport->failed_rows > 0) {
                $userImport->update(['status' => 'completed_with_errors']);
            } else {
                $userImport->markAsCompleted();
            }

            // Eliminar archivo temporal
            Storage::disk('local')->delete($path);

            $message = $userImport->failed_rows > 0
                ? "Importación completada: {$userImport->successful_rows} usuarios importados correctamente, {$userImport->failed_rows} con errores."
                : "Importación completada exitosamente: {$userImport->successful_rows} usuarios importados.";

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            if (isset($userImport)) {
                $userImport->markAsFailed([
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return redirect()->back()->with('error', 'Error al importar usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalles de una importación
     */
    public function show(UserImport $userImport)
    {
        $this->authorize('view', $userImport->user);

        return Inertia::render('Users/ImportDetails', [
            'import' => $userImport->load('user'),
        ]);
    }

    /**
     * Eliminar registro de importación
     */
    public function destroy(UserImport $userImport)
    {
        $this->authorize('delete', $userImport->user);

        $userImport->delete();

        return redirect()->route('users.import.index')
            ->with('success', 'Registro de importación eliminado exitosamente.');
    }

    /**
     * Validar archivo antes de importar (preview)
     */
    public function validate(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('temp', 'local');

            // Leer primeras filas para preview
            $rows = Excel::toCollection(new UsersImport(new UserImport()), storage_path('app/' . $path))->first();

            // Limitar a primeras 10 filas
            $preview = $rows->take(10)->map(function ($row) {
                return [
                    'nombre' => $row['nombre'] ?? $row['name'] ?? '',
                    'email' => $row['email'] ?? $row['correo'] ?? '',
                    'tipo' => $row['tipo'] ?? $row['tipo_usuario'] ?? 'user',
                    'empresa' => $row['empresa'] ?? '',
                    'sucursal' => $row['sucursal'] ?? '',
                ];
            });

            // Eliminar archivo temporal
            Storage::disk('local')->delete($path);

            return response()->json([
                'success' => true,
                'total_rows' => $rows->count(),
                'preview' => $preview,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
