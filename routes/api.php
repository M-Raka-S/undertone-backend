<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryInstanceController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\InstanceParameterController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;

// Authentication
Route::any('/auth-fail', [AuthenticationController::class, 'fail'])->name('login');
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/remember_login', [AuthenticationController::class, 'rememberLogin']);

if (!function_exists('autoRouting')) {
    function autoRouting($controller, $path = null) {
        $path = $path ?? Str::lower(Str::plural(substr($controller, 21, -10)));
        Route::get("/{$path}/all", [$controller, 'all']);
        Route::get("/{$path}/page/{page}", [$controller, 'show']);
        Route::get("/{$path}/{id}", [$controller, 'pick']);
        Route::post("/{$path}", [$controller, 'make']);
        Route::patch("/{$path}/{id}", [$controller, 'edit']);
        Route::delete("/{$path}/{id}", [$controller, 'remove']);
    }
}

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/check-token', [AuthenticationController::class, 'check_token']);

    Route::delete('/logout', [AuthenticationController::class, 'logout']);

    // Users
    autoRouting(UserController::class);

    // Categories
    autoRouting(CategoryController::class);

    // Parameters
    autoRouting(ParameterController::class);

    // Instances
    autoRouting(CategoryInstanceController::class, 'instances');
    Route::post('/instances/search', [CategoryInstanceController::class, 'search']);
    Route::patch('/instances/summarise/{id}', [CategoryInstanceController::class, 'generateSummary']);

    // Instance Parameters
    autoRouting(InstanceParameterController::class, 'instanceParameters');

    // Projects
    autoRouting(ProjectController::class);
    Route::get('/projects/users/{id}', [ProjectController::class, 'getUsers']);
    Route::get('/projects/candidates/{id}', [ProjectController::class, 'getCandidates']);
    Route::get('/projects/getRole/{id}', [ProjectController::class, 'getRole']);
    Route::put('/projects/addUser/{id}/{user_id}', [ProjectController::class, 'addUser']);
    Route::put('/projects/removeUser/{id}/{user_id}', [ProjectController::class, 'removeUser']);
    Route::put('/projects/editRole/{id}/{user_id}', [ProjectController::class, 'editUserRole']);
    Route::put('/projects/{id}/hidden', [ProjectController::class, 'updateHiddenCategories']);

    // Media
    autoRouting(MediaController::class);

    // Chapters
    autoRouting(ChapterController::class);
    Route::post('/chapters/updateContent/{id}', [ChapterController::class, 'updateContent']);
    Route::patch('/chapters/generate/{id}', [ChapterController::class, 'generateContent']);

    // Comments
    autoRouting(CommentController::class);
});
