<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\EnvioPdfController;
use App\Http\Controllers\CompraPdfController;

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
