<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books');

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/pdf', [\App\Http\Controllers\ReportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [\App\Http\Controllers\ReportController::class, 'excel'])->name('reports.export.excel');
    Route::resource('books', \App\Http\Controllers\BookController::class);
});
