<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\TagController;

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


Route::match(['get', 'post'], '/tags', [TagsController::class, 'index'])->name('tags');
Route::post('tags-store', [TagsController::class, 'store'])->name('store.tags');
Route::put('update-tag/{id}', [TagsController::class, 'update'])->name('tags.update');
Route::delete('delete-tag/{id}', [TagsController::class, 'destroy'])->name('tags.delete');




// Untuk render halaman utama pakai Inertia
Route::get('/tag', [TagController::class, 'index'])->name('tag');

// Endpoint JSON untuk datatable / axios (index data)
Route::get('/tag/data', [TagController::class, 'getData'])->name('tag.data');

// CRUD pakai axios
Route::post('/tag', [TagController::class, 'store'])->name('tag.store');
Route::put('/tag/{id}', [TagController::class, 'update'])->name('tag.update');
Route::delete('/tag/{id}', [TagController::class, 'destroy'])->name('tag.delete');
Route::post('/tag/delete-many', [TagController::class, 'deleteMany'])->name('tag.deleteMany');
Route::get('/tag/{id}', [TagController::class, 'show'])->name('tag.show');
// Route::get('/tag/export', [TagController::class, 'export'])->name('tag.export');
Route::get('/tags/export', [TagController::class, 'export']);