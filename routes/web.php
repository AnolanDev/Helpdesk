<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets Routes
    Route::resource('tickets', TicketController::class);

    // Ticket Actions
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'addComment'])->name('tickets.comments');
    Route::patch('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
    Route::patch('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::patch('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::get('/tickets/{ticket}/export-activities', [TicketController::class, 'exportActivities'])->name('tickets.export-activities');

    // Boards Routes
    Route::resource('boards', BoardController::class);

    // Board Sharing Routes
    Route::get('/boards/{board}/available-users', [BoardController::class, 'getAvailableUsers'])->name('boards.available-users');
    Route::post('/boards/{board}/share', [BoardController::class, 'share'])->name('boards.share');
    Route::patch('/boards/{board}/share/{sharedUser}', [BoardController::class, 'updateShare'])->name('boards.update-share');
    Route::delete('/boards/{board}/share/{sharedUser}', [BoardController::class, 'unshare'])->name('boards.unshare');

    // Tasks Routes
    Route::resource('tasks', TaskController::class);

    // Task Actions
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::get('/tasks/{task}/transitions', [TaskController::class, 'getAllowedTransitions'])->name('tasks.transitions');

    // Sub-Tasks Routes
    Route::post('/tasks/{task}/sub-tasks', [SubTaskController::class, 'store'])->name('sub-tasks.store');
    Route::patch('/sub-tasks/{subTask}', [SubTaskController::class, 'update'])->name('sub-tasks.update');
    Route::patch('/sub-tasks/{subTask}/toggle', [SubTaskController::class, 'toggleStatus'])->name('sub-tasks.toggle');
    Route::delete('/sub-tasks/{subTask}', [SubTaskController::class, 'destroy'])->name('sub-tasks.destroy');
    Route::patch('/tasks/{task}/sub-tasks/order', [SubTaskController::class, 'updateOrder'])->name('sub-tasks.order');

    // Users Routes
    Route::resource('users', UserController::class);

    // User Import Routes - Solo administradores
    Route::get('/users-import', [UserImportController::class, 'index'])->name('users.import.index');
    Route::get('/users-import/template', [UserImportController::class, 'downloadTemplate'])->name('users.import.template');
    Route::post('/users-import', [UserImportController::class, 'import'])->name('users.import.store');
    Route::get('/users-import/{userImport}', [UserImportController::class, 'show'])->name('users.import.show');
    Route::delete('/users-import/{userImport}', [UserImportController::class, 'destroy'])->name('users.import.destroy');

    // Notifications Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/clear-read', [NotificationController::class, 'deleteAllRead'])->name('notifications.clear-read');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Settings Routes - Solo administradores
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [SettingController::class, 'reset'])->name('settings.reset');
});

require __DIR__.'/auth.php';
