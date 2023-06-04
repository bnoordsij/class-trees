<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('projects/{project}/tree', [ProjectController::class, 'tree'])->name('projects.tree');

//Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
//Route::get('projects/create', [ProjectController::class, 'form'])->name('projects.create');
//Route::get('projects/{project}/tree', [ProjectController::class, 'tree'])->name('projects.tree');
//Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
//Route::get('projects/{project}/edit', [ProjectController::class, 'form'])->name('projects.edit');
//Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
//Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
//Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.delete');
//Route::delete('projects/{project}/clear', [ProjectController::class, 'clear'])->name('projects.clear');
