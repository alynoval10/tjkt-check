<?php

use App\Exports\SiswaTemplateExport;
use App\Http\Controllers\CekSiswaController;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', [CekSiswaController::class, 'index'])
    ->name('home');

Route::get('/siswa/template', function () {
    return Excel::download(
        new SiswaTemplateExport(),
        'template-siswa.xlsx'
    );
})->name('siswa.template');

Route::get('/cek-siswa', [CekSiswaController::class, 'index'])
    ->name('cek-siswa.index');

Route::get('/cek-siswa/search', [CekSiswaController::class, 'search'])
    ->name('cek-siswa.search');

Route::get('/cek-siswa/{id}', [CekSiswaController::class, 'detail'])
    ->whereNumber('id')
    ->name('cek-siswa.detail');