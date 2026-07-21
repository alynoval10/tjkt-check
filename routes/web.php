<?php

use Illuminate\Support\Facades\Route;
use App\Exports\SiswaTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\CekSiswaController;

/*
Route::get('/cek-siswa',
    [CekSiswaController::class,'index']
)->name('cek-siswa');


Route::get('/cek-siswa/{id}',
    [CekSiswaController::class,'detail']
)->name('cek-siswa.detail');
*/
Route::get('/siswa/template', function () {
    return Excel::download(
        new SiswaTemplateExport(),
        'template-siswa.xlsx'
    );
})->name('siswa.template');


Route::get('/cek-siswa', [CekSiswaController::class, 'index'])
    ->name('cek-siswa');

Route::get('/cek-siswa/search', [CekSiswaController::class, 'search'])
    ->name('cek-siswa.search');

Route::get('/cek-siswa/{id}', [CekSiswaController::class, 'detail'])
    ->name('cek-siswa.detail');