<?php

use App\Exports\AnggotaExport;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\JenisKasController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\ManajemenAdminController;
use App\Http\Controllers\ManajemenUserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;



Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/api/chart-data', [HomeController::class, 'getChartData'])->name('chart.data');
Route::get('/api/detail-data', [HomeController::class, 'getDetailData'])->name('detail.data');



Route::get('/accept-invitation/{user}', function (\App\Models\User $user, Request $request) {
    Log::debug('Request timestamp: ' . now());
    Log::debug('Signed URL: ' . $request->fullUrl());
    return (new \App\Http\Controllers\InvitationController)->showPasswordForm($user);
})->middleware('signed')->name('invitation.accept');


Route::post('/accept-invitation/{user}', [InvitationController::class, 'storePassword']);

Route::middleware(['guest'])
    ->group(function () {
        Route::get('/login', [GoogleController::class, 'index'])->name('login');
        Route::get('/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
        Route::get('/google/callback', [GoogleController::class, 'handleGoogleCallback']);
        Route::post('/login', [GoogleController::class, 'store'])->name('login.store');
    });

Route::middleware(['auth'])
    ->group(function () {
        Route::post('/logout', [googleController::class, 'logout'])->name('logout');
    });

Route::middleware(['admin'])->group(function () {
    // In your web routes (usually routes/web.php)
    Route::get('/api/jenis-kas/{id}', [KasController::class, 'getJenisKasDetail']);
    Route::get('/jenis-kas/search', [KasController::class, 'searchJenisKas'])->name('jenis_kas.search');
    Route::get('/manajemen-admin/export', [ManajemenAdminController::class, 'exportAnggotaExcel'])->name('manajemen-admin.export');
    Route::get('/search/users', [KasController::class, 'searchUsers'])->name('users.search');
    Route::get('/kas/report-monthly', [KasController::class, 'monthlyReport'])->name('kas.report-monthly');
    Route::get('/kas/available-years', [KasController::class, 'getAvailableYears'])->name('kas.available-years');
    Route::get('/kas/data', [KasController::class, 'data'])->name('kas.data');
    Route::resource('manajemen-admin', ManajemenAdminController::class);
    Route::resource('jenis-kas', JenisKasController::class);
    Route::middleware(['tutup_buku'])->group(function () {});
    Route::resource('kas', KasController::class);
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/belum-bayar-detail', [DashboardAdminController::class, 'getBelumBayarDetail'])
        ->name('dashboard.belum-bayar-detail');
    Route::get('/dashboard/chart-data', [DashboardAdminController::class, 'getChartData'])
        ->name('dashboard.chart-data');
    Route::get('/dashboard/tahun-tersedia', [DashboardAdminController::class, 'getTahunTersedia'])
        ->name('dashboard.tahun-tersedia');
    Route::post('/admin/dashboard/send-reminder-email', [DashboardAdminController::class, 'sendReminderEmail'])->name('dashboard.send-reminder-email');
    Route::post('/dashboard/send-bulk-reminders', [DashboardAdminController::class, 'sendBulkReminders'])->name('dashboard.send-bulk-reminders');
    Route::get('/dashboard/iuran-sukarela-detail', [DashboardAdminController::class, 'getIuranSukarelaDetail'])
        ->name('dashboard.iuran-sukarela-detail');
    Route::get('/admin/dashboard/export/all', [App\Http\Controllers\DashboardAdminController::class, 'exportAllDataToExcel'])->name('dashboard.export.all');
    Route::post('/kirim-wa', [DashboardAdminController::class, 'sendWhatsappReminder']);
    Route::post('/kirim-wa/reminder', [DashboardAdminController::class, 'kirimWhatsappPerJenisKas'])->name('kirim.whatsapp.reminder');
    Route::get('/jenis-kas/export/excel', [JenisKasController::class, 'exportExcel'])->name('jenis-kas.export.excel');
});





require __DIR__ . '/email/verify.php';


Route::middleware(['user'])->group(function () {
    Route::controller(ManajemenUserController::class)
        ->prefix('user')
        ->group(function () {
            Route::get('/dashboard', 'dashboard')->name('user.dashboard');
            Route::get('/riwayat', 'riwayat')->name('user.riwayat');
        });
});
