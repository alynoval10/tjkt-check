<?php

use App\Exports\SiswaTemplateExport;
use App\Http\Controllers\CekSiswaController;
use App\Http\Controllers\KartuKompetensiController;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/backup-download/{name}', \App\Http\Controllers\BackupDownloadController::class)
    ->middleware('auth')->name('backup.download');

// Kartu cetak hanya tersedia bagi pengguna yang sudah login.
Route::get('/siswa/{siswa}/kartu-kompetensi', KartuKompetensiController::class)
    ->middleware('auth')->name('siswa.kartu-kompetensi');

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

Route::get('/cek-siswa/{publicId}', [CekSiswaController::class, 'detail'])
    ->name('cek-siswa.detail');
