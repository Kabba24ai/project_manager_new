<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskListController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// SSO route (accessible without guest middleware)
Route::get('/auth/sso', [AuthController::class, 'sso'])->name('auth.sso');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/api/user', [AuthController::class, 'getCurrentUser']);

    // Dashboard
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Task Lists
    Route::get('/projects/{project}/task-lists', [TaskListController::class, 'index'])->name('task-lists.index');
    Route::get('/projects/{project}/task-lists/create', [TaskListController::class, 'create'])->name('task-lists.create');
    Route::post('/projects/{project}/task-lists', [TaskListController::class, 'store'])->name('task-lists.store');
    Route::get('/projects/{project}/task-lists/{taskList}', [TaskListController::class, 'show'])->name('task-lists.show');
    Route::get('/projects/{project}/task-lists/{taskList}/edit', [TaskListController::class, 'edit'])->name('task-lists.edit');
    Route::put('/projects/{project}/task-lists/{taskList}', [TaskListController::class, 'update'])->name('task-lists.update');
    Route::delete('/projects/{project}/task-lists/{taskList}', [TaskListController::class, 'destroy'])->name('task-lists.destroy');

    // Tasks
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/task-lists/{taskList}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::patch('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Attachments
    Route::post('/api/upload-temp-file', [AttachmentController::class, 'uploadTemp'])->name('attachments.upload-temp');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{attachment}/thumbnail', [AttachmentController::class, 'thumbnail'])->name('attachments.thumbnail');
    Route::get('/attachments/{attachment}/preview', [AttachmentController::class, 'preview'])->name('attachments.preview');

    // Comments
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/tasks/{task}/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/tasks/{task}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Task Templates
    Route::get('/task-templates', [TaskTemplateController::class, 'index'])->name('task-templates.index');
    Route::get('/api/task-templates', [TaskTemplateController::class, 'getTemplatesApi'])->name('task-templates.api');
    Route::post('/task-templates', [TaskTemplateController::class, 'store'])->name('task-templates.store');
    Route::put('/task-templates/{template}', [TaskTemplateController::class, 'update'])->name('task-templates.update');
    Route::delete('/task-templates/{template}', [TaskTemplateController::class, 'destroy'])->name('task-templates.destroy');

    // Users
    Route::get('/api/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/api/managers', [UserController::class, 'managers'])->name('users.managers');

    // Equipment
    Route::get('/api/equipment', [EquipmentController::class, 'getEquipment'])->name('equipment.index');
    Route::get('/api/equipment/all', [EquipmentController::class, 'getAllEquipment'])->name('equipment.all');
});
