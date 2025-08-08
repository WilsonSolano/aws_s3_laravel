<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchivoController extends Controller
{
    public function cargarArchivos()
    {
        return view('archivos.cargar-archivo');
    }

    public function procesarCargaArchivos(Request $request)
    {
        // Validar los archivos subidos
        $request->validate([
            'archivos' => 'required|array',
            'archivos.*' => 'required|file|mimes:json,pdf|max:10240',
        ], [
            'archivos.required' => 'Debe seleccionar al menos un archivo.',
            'archivos.*.mimes' => 'Solo se permiten archivos JSON y PDF.',
            'archivos.*.max' => 'El archivo no debe superar los 10MB.',
        ]);

        $archivosSubidos = [];
        $errores = [];

        foreach ($request->file('archivos') as $archivo) {
            try {
                // Generar nombre único para el archivo
                $extension = $archivo->getClientOriginalExtension();
                $nombreArchivo = time() . '_' . uniqid() . '.' . $extension;

                // Determinar la carpeta según el tipo de archivo dentro de archivos-6
                $carpeta = $extension === 'json' ? 'archivos-6/json' : 'archivos-6/pdf';

                // Crear la carpeta si no existe
                if (!Storage::disk('s3')->exists($carpeta)) {
                    // Crear un archivo temporal para forzar la creación del directorio
                    Storage::disk('s3')->put($carpeta . '/.gitkeep', '');
                }

                // Subir archivo a S3
                $rutaArchivo = Storage::disk('s3')->put($carpeta, $archivo);
                $urlArchivo = Storage::disk('s3')->url($rutaArchivo);

                // Información del archivo procesado
                $infoArchivo = [
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'nombre_almacenado' => $nombreArchivo,
                    'ruta_s3' => $rutaArchivo,
                    'url_publica' => $urlArchivo,
                    'tamaño' => $archivo->getSize(),
                    'tipo_mime' => $archivo->getMimeType(),
                    'extension' => $extension,
                    'fecha_subida' => now(),
                ];

                $archivosSubidos[] = $infoArchivo;

                Archivo::create([
                    'nombre_original' => $infoArchivo['nombre_original'],
                    'ruta_s3' => $infoArchivo['ruta_s3'],
                    'url_publica' => $infoArchivo['url_publica'],
                    'tamaño' => $infoArchivo['tamaño'],
                    'tipo_archivo' => $infoArchivo['extension'],
                    'usuario_id' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                $errores[] = [
                    'archivo' => $archivo->getClientOriginalName(),
                    'error' => 'Error al subir el archivo: ' . $e->getMessage()
                ];
            }
        }

        // Preparar respuesta
        $mensaje = '';
        $tipoMensaje = 'success';

        if (count($archivosSubidos) > 0) {
            $totalSubidos = count($archivosSubidos);
            $mensaje = "Se han subido exitosamente {$totalSubidos} archivo(s).";

            if (count($errores) > 0) {
                $mensaje .= " Sin embargo, algunos archivos no se pudieron procesar.";
                $tipoMensaje = 'warning';
            }
        } else {
            $mensaje = "No se pudieron subir los archivos.";
            $tipoMensaje = 'error';
        }

        return back()
            ->with($tipoMensaje, $mensaje)
            ->with('archivos_subidos', $archivosSubidos)
            ->with('errores', $errores);
    }

    public function eliminarArchivo($rutaArchivo)
    {
        try {
            // Eliminar archivo de S3
            Storage::disk('s3')->delete($rutaArchivo);

            Archivo::where('ruta_s3', $rutaArchivo)->delete();

            return response()->json([
                'success' => true,
                'mensaje' => 'Archivo eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function descargarArchivo($rutaArchivo)
    {
        try {
            if (!Storage::disk('s3')->exists($rutaArchivo)) {
                abort(404, 'Archivo no encontrado.');
            }

            $nombreArchivo = basename($rutaArchivo);

            return Storage::disk('s3')->download($rutaArchivo, $nombreArchivo);
        } catch (\Exception $e) {
            abort(500, 'Error al descargar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Método auxiliar para asegurar que existe la carpeta base
     */
    private function crearCarpetaSiNoExiste($carpeta)
    {
        try {
            if (!Storage::disk('s3')->exists($carpeta)) {
                // Crear un archivo .gitkeep para asegurar que la carpeta existe
                Storage::disk('s3')->put($carpeta . '/.gitkeep', '# Carpeta creada automáticamente');
            }
        } catch (\Exception $e) {
            \Log::warning("No se pudo crear la carpeta {$carpeta}: " . $e->getMessage());
        }
    }
}
