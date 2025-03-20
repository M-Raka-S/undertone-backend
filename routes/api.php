<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

// Authentication
Route::get('/auth-fail', [AuthenticationController::class, 'fail'])->name('login');
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);

if (!function_exists('autoRouting')) {
    function autoRouting($controller) {
        $path = Str::lower(Str::plural(substr($controller, 21, -10)));
        Route::get("/{$path}/page/{page}", [$controller, 'show']);
        Route::get("/{$path}/{id}", [$controller, 'pick']);
        Route::post("/{$path}", [$controller, 'make']);
        Route::patch("/{$path}/{id}", [$controller, 'edit']);
        Route::delete("/{$path}/{id}", [$controller, 'remove']);
    }
}

Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete('/logout', [AuthenticationController::class, 'logout']);

    // Users
    autoRouting(UserController::class);

    // Categories
    autoRouting(CategoryController::class);

    // Projects
    autoRouting(ProjectController::class);
    Route::put('/projects/{id}/hidden', [ProjectController::class, 'updateHiddenCategories']);
    Route::put('/projects/addUser/{id}/{user_id}', [ProjectController::class, 'addUser']);
    Route::put('/projects/removeUser/{id}/{user_id}', [ProjectController::class, 'removeUser']);
    Route::put('/projects/editRole/{id}/{user_id}', [ProjectController::class, 'editUserRole']);
});
