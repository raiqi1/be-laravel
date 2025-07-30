<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterKaryawanController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/karyawans', [MasterKaryawanController::class, 'index']);
Route::post('/karyawans', [MasterKaryawanController::class, 'store']);

