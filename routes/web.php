<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FsTeamController;
use App\Http\Controllers\RpwsisTeamController;
use App\Http\Controllers\ContractManagementTeamController;
use App\Http\Controllers\RowTeamController;
use App\Http\Controllers\PcrTeamController;
use App\Http\Controllers\PaoTeamController;
use App\Http\Controllers\AdministrativeController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\MapController;

// Authentication Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/guest/authenticate', [GuestController::class, 'authenticate'])->name('guest.authenticate');
Route::get('/guest/terms', [GuestController::class, 'terms'])->name('guest.terms');
Route::post('/guest/accept-terms', [GuestController::class, 'acceptTerms'])->name('guest.accept');
Route::get('/guest/dashboard', [GuestController::class, 'index'])->name('guest.dashboard');
Route::post('/guest/logout', [GuestController::class, 'logout'])->name('guest.logout');

Route::get('/map', [MapController::class, 'Showmap'])->name('map');
Route::get('/guest/team/{team_slug}/downloadables', [GuestController::class, 'teamDownloadables'])->name('guest.team.downloadables');
Route::get('/guest/team/{team_slug}/resolutions', [GuestController::class, 'teamResolutions'])->name('guest.team.resolutions');
// Routes that require login
Route::middleware(['auth', 'check.active'])->group(function () {

    // Terms and Conditions (RA10173)
    Route::get('/terms', [TermsController::class, 'show'])->name('terms.show');
    Route::post('/terms/agree', [TermsController::class, 'agree'])->name('terms.agree');

    Route::get('/administrative', [AdministrativeController::class, 'index'])->name('administrative.index');
    Route::post('/administrative', [AdministrativeController::class, 'store'])->name('administrative.store');
    Route::delete('/administrative/{id}', [AdministrativeController::class, 'destroy'])->name('administrative.destroy');

    //guest
    // Route::get('/guest/dashboard', [App\Http\Controllers\GuestController::class, 'index'])->name('guest.dashboard');

    //Map Routes
    Route::post('/map/upload', [MapController::class, 'upload'])->name('map.upload');
    Route::get('/map/files', [MapController::class, 'fileManager'])->name('map.files');
    Route::delete('/map/delete', [MapController::class, 'deleteFile']);

    // Protected Routes (Must have agreed to terms)
    Route::middleware(['check.terms'])->group(function () {

        // Admin Routes
        Route::middleware(['check.role:admin'])->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
            Route::get('/users', [AdminController::class, 'manageUsers'])->name('admin.users');
            Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
            Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
            Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
            // Add more admin routes here

            Route::post('/events', [AdminController::class, 'storeEvent'])->name('admin.events.store');
            Route::delete('/events/{id}', [AdminController::class, 'destroyEvent'])->name('admin.events.destroy');
            // Manage Custom Categories
            Route::post('/event-categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
            Route::delete('/event-categories/{id}', [AdminController::class, 'destroyCategory'])->name('admin.categories.destroy');

            //Downloadables
            Route::post('/downloadables/upload', [AdminController::class, 'uploadDownloadable'])->name('admin.downloadables.upload');
            //IA Resolutions
            Route::post('/resolutions/upload', [AdminController::class, 'uploadResolution'])->name('admin.resolutions.upload');
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

                //resolutions
                
                Route::post('/ia-resolutions/upload', [FsTeamController::class, 'uploadResolution'])->name('fs.resolutions.upload');
                Route::post('/ia-resolutions/{id}/update', [FsTeamController::class, 'updateResolution'])->name('fs.resolutions.update');

                Route::delete('/ia-resolutions/{id}/delete', [FsTeamController::class, 'deleteResolution'])->name('fs.resolutions.delete');
                
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

                //delete
                Route::delete('/ia-resolutions/{id}/delete', [RpwsisTeamController::class, 'deleteResolution'])->name('rpwsis.resolutions.delete');

                Route::post('/ia-resolutions/{id}/update', [RpwsisTeamController::class, 'updateResolution'])->name('rpwsis.resolutions.update');
                Route::post('/ia-resolutions/{id}/status', [RpwsisTeamController::class, 'updateResolutionStatus'])->name('rpwsis.resolutions.update_status');
            });
        });

        // ==========================================
        // Contract Management Team Routes
        // ==========================================
        Route::prefix('cm_team')->group(function () {

            // 👁️ VIEWERS (Open to all logged-in agency staff)
            Route::get('/dashboard', [ContractManagementTeamController::class, 'index'])->name('cm.dashboard');
            Route::get('/downloadables', [ContractManagementTeamController::class, 'downloadables'])->name('cm.downloadables');
            Route::get('/ia-resolutions', [ContractManagementTeamController::class, 'resolutions'])->name('cm.resolutions');

            // 🔒 EDITORS ONLY (Locked to Contract Management Team and Admin)
            Route::middleware(['check.role:cm_team,admin'])->group(function () {
                Route::post('/downloadables/upload', [ContractManagementTeamController::class, 'uploadForm'])->name('cm.downloadables.upload');
                Route::post('/downloadables/{id}/update', [ContractManagementTeamController::class, 'updateForm'])->name('cm.downloadables.update');
                Route::delete('/downloadables/{id}/delete', [ContractManagementTeamController::class, 'deleteForm'])->name('cm.downloadables.delete');

                Route::post('/ia-resolutions/upload', [ContractManagementTeamController::class, 'uploadResolution'])->name('cm.resolutions.upload');

                //delete
                Route::delete('/resolutions/{id}/delete', [ContractManagementTeamController::class, 'deleteResolution'])->name('cm.resolutions.delete');

                Route::post('/ia-resolutions/{id}/update', [ContractManagementTeamController::class, 'updateResolution'])->name('cm.resolutions.update');
                Route::post('/ia-resolutions/{id}/status', [ContractManagementTeamController::class, 'updateResolutionStatus'])->name('cm.resolutions.update_status');
            });
        });

        // ==========================================
        // Right Of Way Team Routes
        // ==========================================
        Route::prefix('row_team')->group(function () {

            // 👁️ VIEWERS (Open to all logged-in agency staff)
            Route::get('/dashboard', [RowTeamController::class, 'index'])->name('row.dashboard');
            Route::get('/downloadables', [RowTeamController::class, 'downloadables'])->name('row.downloadables');
            Route::get('/ia-resolutions', [RowTeamController::class, 'resolutions'])->name('row.resolutions');

            // 🔒 EDITORS ONLY (Locked to Row Team and Admin)
            Route::middleware(['check.role:row_team,admin'])->group(function () {
                Route::post('/downloadables/upload', [RowTeamController::class, 'uploadForm'])->name('row.downloadables.upload');
                Route::post('/downloadables/{id}/update', [RowTeamController::class, 'updateForm'])->name('row.downloadables.update');
                Route::delete('/downloadables/{id}/delete', [RowTeamController::class, 'deleteForm'])->name('row.downloadables.delete');

                Route::post('/ia-resolutions/upload', [RowTeamController::class, 'uploadResolution'])->name('row.resolutions.upload');

                //delete 
                Route::delete('/resolutions/{id}/delete', [RowTeamController::class, 'deleteResolution'])->name('row.resolutions.delete');

                Route::post('/ia-resolutions/{id}/update', [RowTeamController::class, 'updateResolution'])->name('row.resolutions.update');
                Route::post('/ia-resolutions/{id}/status', [RowTeamController::class, 'updateResolutionStatus'])->name('row.resolutions.update_status');
            });
        });

        // ==========================================
        // Program Completion Report Team Routes
        // ==========================================
        Route::prefix('pcr_team')->group(function () {

            // 👁️ VIEWERS (Open to all logged-in agency staff)
            Route::get('/dashboard', [PcrTeamController::class, 'index'])->name('pcr.dashboard');
            Route::get('/downloadables', [PcrTeamController::class, 'downloadables'])->name('pcr.downloadables');
            Route::get('/ia-resolutions', [PcrTeamController::class, 'resolutions'])->name('pcr.resolutions');

            // 🔒 EDITORS ONLY (Locked to PCR Team and Admin)
            Route::middleware(['check.role:pcr_team,admin'])->group(function () {
                Route::post('/downloadables/upload', [PcrTeamController::class, 'uploadForm'])->name('pcr.downloadables.upload');
                Route::post('/downloadables/{id}/update', [PcrTeamController::class, 'updateForm'])->name('pcr.downloadables.update');
                Route::delete('/downloadables/{id}/delete', [PcrTeamController::class, 'deleteForm'])->name('pcr.downloadables.delete');

                Route::post('/ia-resolutions/upload', [PcrTeamController::class, 'uploadResolution'])->name('pcr.resolutions.upload');

                //delete
                Route::delete('/resolutions/{id}/delete', [PcrTeamController::class, 'deleteResolution'])->name('pcr.resolutions.delete');

                Route::post('/ia-resolutions/{id}/update', [PcrTeamController::class, 'updateResolution'])->name('pcr.resolutions.update');
                Route::post('/ia-resolutions/{id}/status', [PcrTeamController::class, 'updateResolutionStatus'])->name('pcr.resolutions.update_status');
            });
        });

        // ==========================================
        // Programming Team Routes
        // ==========================================
        Route::prefix('pao_team')->group(function () {

            // 👁️ VIEWERS (Open to all logged-in agency staff)
            Route::get('/dashboard', [PaoTeamController::class, 'index'])->name('pao.dashboard');
            Route::get('/downloadables', [PaoTeamController::class, 'downloadables'])->name('pao.downloadables');
            Route::get('/ia-resolutions', [PaoTeamController::class, 'resolutions'])->name('pao.resolutions');

            // 🔒 EDITORS ONLY (Locked to Programming Team and Admin)
            Route::middleware(['check.role:pao_team,admin'])->group(function () {
                Route::post('/downloadables/upload', [PaoTeamController::class, 'uploadForm'])->name('pao.downloadables.upload');
                Route::post('/downloadables/{id}/update', [PaoTeamController::class, 'updateForm'])->name('pao.downloadables.update');
                Route::delete('/downloadables/{id}/delete', [PaoTeamController::class, 'deleteForm'])->name('pao.downloadables.delete');

                Route::post('/ia-resolutions/upload', [PaoTeamController::class, 'uploadResolution'])->name('pao.resolutions.upload');

                //delete
                Route::delete('/resolutions/{id}/delete', [PaoTeamController::class, 'deleteResolution'])->name('pao.resolutions.delete');

                Route::post('/ia-resolutions/{id}/update', [PaoTeamController::class, 'updateResolution'])->name('pao.resolutions.update');
                Route::post('/ia-resolutions/{id}/status', [PaoTeamController::class, 'updateResolutionStatus'])->name('pao.resolutions.update_status');
            });
        });

    });
})
    ->middleware('check.active'); // Ensure user is active before allowing access to any routes within this group
