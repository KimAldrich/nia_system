<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FsTeamController;

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
            Route::post('/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
            // Add more admin routes here
        });

        // FS Team Routes
        Route::middleware(['check.role:fs_team'])->prefix('fs-team')->group(function () {
            Route::get('/dashboard', [FsTeamController::class, 'index'])->name('fs.dashboard');
            Route::get('/downloadables', [FsTeamController::class, 'downloadables'])->name('fs.downloadables');
            Route::post('/downloadables/upload', [FsTeamController::class, 'uploadForm'])->name('fs.downloadables.upload');
            Route::post('/downloadables/{id}/update', [FsTeamController::class, 'updateForm'])->name('fs.downloadables.update');
            Route::get('/ia-resolutions', [FsTeamController::class, 'resolutions'])->name('fs.resolutions');
            Route::post('/ia-resolutions/upload', [FsTeamController::class, 'uploadResolution'])->name('fs.resolutions.upload');
            Route::post('/ia-resolutions/{id}/update', [FsTeamController::class, 'updateResolution'])->name('fs.resolutions.update');
            Route::post('/ia-resolutions/{id}/status', [FsTeamController::class, 'updateResolutionStatus'])->name('fs.resolutions.update_status');
            Route::post('/projects/{project}/update-status', [FsTeamController::class, 'updateStatus'])->name('fs.projects.update');
        });


        //dito niyo add yung mga routes per team, dapat naka middleware parang fs team

    });
});