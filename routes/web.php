<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchivoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('archivos')->group(function () {
    Route::get('/', [ArchivoController::class, 'cargarArchivos'])->name('cargar');
    
    Route::post('/procesar-carga', [ArchivoController::class, 'procesarCargaArchivos'])->name('archivos.procesar-carga');
    
    Route::delete('/eliminar/{rutaArchivo}', [ArchivoController::class, 'eliminarArchivo'])->name('eliminar')->where('rutaArchivo', '.*'); // Permite caracteres especiales en la ruta
    
    Route::get('/descargar/{rutaArchivo}', [ArchivoController::class, 'descargarArchivo'])->name('descargar')->where('rutaArchivo', '.*'); // Permite caracteres especiales en la ruta
});