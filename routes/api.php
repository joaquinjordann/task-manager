<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

Route::group(['middleware' => ['api']], function () {
    // Rutas públicas
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Rutas protegidas
    Route::group(['middleware' => ['jwt.auth']], function () {
        Route::post('projects/{project}/tasks', [TaskController::class, 'store']);
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('projects.tasks', TaskController::class)->except('store');
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
?>
