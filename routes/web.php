<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\suratSakitController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/suratSakit', function () {
    return view('surat.suratSakit');
});

Route::get('/surat-sakit/{no_rw}', [SuratSakitController::class, 'SuratSakit']);
Route::get('/surat-ket-ranap/{no_rw}', [SuratSakitController::class, 'SuratKetRanap']);
