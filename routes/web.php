<?php
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rutas públicas
Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });

    // Cambiar estas rutas para usar funciones anónimas que retornen las vistas
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
});

// Rutas protegidas
Route::middleware(['check.jwt'])->group(function () {
    Route::get('/projects', function () {
        return view('projects.index');
    })->name('projects.index');

    Route::get('/projects/create', function () {
        return view('projects.create');
    })->name('projects.create');

    Route::get('/projects/{project}', function ($project) {
        return view('projects.show', ['projectId' => $project]);
    })->name('projects.show');

    Route::get('/projects/{project}/tasks/create', function ($project) {
        return view('tasks.create', ['projectId' => $project]);
    })->name('tasks.create');

    Route::get('/projects/{project}/tasks/{task}/edit', function ($project, $task) {
        return view('tasks.edit', [
            'projectId' => $project,
            'taskId' => $task
        ]);
    })->name('tasks.edit');
});

// Rutas de autenticación API
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/register', [AuthController::class, 'register']);
Route::middleware(['auth:api'])->group(function () {
    Route::post('/api/logout', [AuthController::class, 'logout'])->name('logout');
});
?>
