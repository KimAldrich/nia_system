<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FsTeamController;
use App\Http\Controllers\RpwsisTeamController;

// Authentication Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes that require login
Route::middleware(['auth'])->group(function () {

    // Terms and Conditions (RA10173)
    Route::get('/terms', [TermsController::class, 'show'])->name('terms.show');
    Route::post('/terms/agree', [TermsController::class, 'agree'])->name('terms.agree');

    // Protected Routes (Must have agreed to terms)
    Route::middleware(['check.terms'])->group(function () {

        // Admin Routes
        Route::middleware(['check.role:admin'])->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
            Route::get('/users', [AdminController::class, 'manageUsers'])->name('admin.users');
            Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
            // Add more admin routes here

            Route::post('/events', [AdminController::class, 'storeEvent'])->name('admin.events.store');
            Route::delete('/events/{id}', [AdminController::class, 'destroyEvent'])->name('admin.events.destroy');
            // Manage Custom Categories
            Route::post('/event-categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
            Route::delete('/event-categories/{id}', [AdminController::class, 'destroyCategory'])->name('admin.categories.destroy');
        });

        // ==========================================
        // FS Team Routes
        // ==========================================
        Route::prefix('fs-team')->group(function () {

            // 👁️ VIEWERS (Open to all logged-in agency staff)
            Route::get('/dashboard', [FsTeamController::class, 'index'])->name('fs.dashboard');
            Route::get('/downloadables', [FsTeamController::class, 'downloadables'])->name('fs.downloadables');
            Route::get('/ia-resolutions', [FsTeamController::class, 'resolutions'])->name('fs.resolutions');

            // 🔒 EDITORS ONLY (Locked to FS Team and Admin)
            Route::middleware(['check.role:fs_team,admin'])->group(function () {
                Route::post('/downloadables/upload', [FsTeamController::class, 'uploadForm'])->name('fs.downloadables.upload');
                Route::post('/downloadables/{id}/update', [FsTeamController::class, 'updateForm'])->name('fs.downloadables.update');
                Route::delete('/downloadables/{id}/delete', [FsTeamController::class, 'deleteForm'])->name('fs.downloadables.delete');

                Route::post('/ia-resolutions/upload', [FsTeamController::class, 'uploadResolution'])->name('fs.resolutions.upload');
                Route::post('/ia-resolutions/{id}/update', [FsTeamController::class, 'updateResolution'])->name('fs.resolutions.update');
                Route::post('/ia-resolutions/{id}/status', [FsTeamController::class, 'updateResolutionStatus'])->name('fs.resolutions.update_status');
                Route::post('/projects/{project}/update-status', [FsTeamController::class, 'updateStatus'])->name('fs.projects.update');
            });
        });

        // ==========================================
        // RP-WSIS Team Routes
        // ==========================================
        Route::prefix('rpwsis_team')->group(function () {

            // 👁️ VIEWERS (Open to all logged-in agency staff)
            Route::get('/dashboard', [RpwsisTeamController::class, 'index'])->name('rpwsis.dashboard');
            Route::get('/downloadables', [RpwsisTeamController::class, 'downloadables'])->name('rpwsis.downloadables');
            Route::get('/ia-resolutions', [RpwsisTeamController::class, 'resolutions'])->name('rpwsis.resolutions');

            // 🔒 EDITORS ONLY (Locked to RP-WSIS Team and Admin)
            Route::middleware(['check.role:rpwsis_team,admin'])->group(function () {
                Route::post('/downloadables/upload', [RpwsisTeamController::class, 'uploadForm'])->name('rpwsis.downloadables.upload');
                Route::post('/downloadables/{id}/update', [RpwsisTeamController::class, 'updateForm'])->name('rpwsis.downloadables.update');
                Route::delete('/downloadables/{id}/delete', [RpwsisTeamController::class, 'deleteForm'])->name('rpwsis.downloadables.delete');

                Route::post('/ia-resolutions/upload', [RpwsisTeamController::class, 'uploadResolution'])->name('rpwsis.resolutions.upload');
                Route::post('/ia-resolutions/{id}/update', [RpwsisTeamController::class, 'updateResolution'])->name('rpwsis.resolutions.update');
                Route::post('/ia-resolutions/{id}/status', [RpwsisTeamController::class, 'updateResolutionStatus'])->name('rpwsis.resolutions.update_status');
            });
        });

        //dito niyo add yung mga routes per team, dapat naka middleware parang fs team

    });
});