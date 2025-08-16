<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\CategoryController;
use Inertia\Inertia;
// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// });
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [AboutController::class, 'index']);
// Route::get('/category', [CategoryController::class, 'index']);
// Route::resource('category', CategoryController::class);
// routes/web.php
Route::match(['get', 'post'], '/category', [CategoryController::class, 'index'])->name('category.index');
