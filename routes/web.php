<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\EnvioPdfController;
use App\Http\Controllers\CompraPdfController;
use App\Http\Controllers\MovimientoInventarioPdfController;
use App\Http\Controllers\ProduccionPdfController;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/welcome', function () {
    return view('welcome');
});

// Ruta para generar PDF de envíos
Route::get('/envios/{id}/pdf', [EnvioPdfController::class, 'generarPdf'])->name('envios.pdf');

// Ruta para generar PDF de compras
Route::get('/compras/{id}/pdf', [CompraPdfController::class, 'generarPdf'])->name('compras.pdf');

// Ruta para generar PDF de movimientos de inventario (semanal)
Route::get('/movimientos/pdf', [MovimientoInventarioPdfController::class, 'generarPdfSemanal'])->name('movimientos.pdf');


// Ruta para generar PDF de producciones
Route::get('/producciones/{id}/pdf', [ProduccionPdfController::class, 'generarPdf'])->name('producciones.pdf');
