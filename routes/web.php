<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\SponsorController;
use Illuminate\Support\Facades\Route;

// ---------- Publik ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/event', [EventController::class, 'index'])->name('events.index');
Route::get('/event/{event}', [EventController::class, 'show'])->name('events.show');

// ---------- Tamu ----------
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login']);
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register']);
    Route::get('/admin/masuk', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/masuk', [AuthController::class, 'adminLogin']);
});

// ---------- Semua pengguna login ----------
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi');
    Route::get('/berkas/{jenis}/{id}', [FileController::class, 'show'])->name('files.show');
});

// ---------- Organizer ----------
Route::middleware(['auth', 'role:organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/dashboard', [OrganizerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profil', [OrganizerController::class, 'profil'])->name('profil');
    Route::put('/profil', [OrganizerController::class, 'updateProfil'])->name('profil.update');

    Route::get('/proposal', [OrganizerController::class, 'proposals'])->name('proposals');
    Route::get('/proposal/buat', [OrganizerController::class, 'create'])->name('proposals.create');
    Route::post('/proposal', [OrganizerController::class, 'store'])->name('proposals.store');
    Route::get('/proposal/{event}/ubah', [OrganizerController::class, 'edit'])->name('proposals.edit');
    Route::put('/proposal/{event}', [OrganizerController::class, 'update'])->name('proposals.update');
    Route::delete('/proposal/{event}', [OrganizerController::class, 'destroy'])->name('proposals.destroy');
    Route::post('/proposal/{event}/ajukan', [OrganizerController::class, 'ajukan'])->name('proposals.ajukan');
    Route::post('/proposal/{event}/selesai', [OrganizerController::class, 'selesai'])->name('proposals.selesai');

    Route::get('/komitmen', [OrganizerController::class, 'komitmen'])->name('komitmen');
    Route::post('/komitmen/{commitment}/keputusan', [OrganizerController::class, 'keputusan'])->name('komitmen.keputusan');
    Route::get('/riwayat', [OrganizerController::class, 'riwayat'])->name('riwayat');

    Route::get('/laporan', [OrganizerController::class, 'laporanIndex'])->name('laporan.index');
    Route::get('/laporan/{event}', [OrganizerController::class, 'laporanShow'])->name('laporan.show');
    Route::post('/laporan/{event}/keuangan', [OrganizerController::class, 'simpanKeuangan'])->name('laporan.keuangan');
    Route::post('/laporan/{event}/hasil', [OrganizerController::class, 'simpanHasil'])->name('laporan.hasil');
});

// ---------- Sponsor ----------
Route::middleware(['auth', 'role:sponsor'])->prefix('sponsor')->name('sponsor.')->group(function () {
    Route::get('/dashboard', [SponsorController::class, 'dashboard'])->name('dashboard');
    Route::get('/profil', [SponsorController::class, 'profil'])->name('profil');
    Route::put('/profil', [SponsorController::class, 'updateProfil'])->name('profil.update');

    Route::post('/event/{event}/komitmen', [SponsorController::class, 'storeCommitment'])->name('komitmen.store');
    Route::post('/komitmen/{commitment}/ajukan-ulang', [SponsorController::class, 'resubmit'])->name('komitmen.resubmit');

    Route::get('/laporan', [SponsorController::class, 'laporanIndex'])->name('laporan.index');
    Route::get('/laporan/{event}', [SponsorController::class, 'laporanShow'])->name('laporan.show');
    Route::post('/laporan/{report}/tanggapi', [SponsorController::class, 'tanggapi'])->name('laporan.tanggapi');
});

// ---------- Admin ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/akun', [AdminController::class, 'akun'])->name('akun');
    Route::post('/akun/{user}', [AdminController::class, 'akunVerifikasi'])->name('akun.verifikasi');
    Route::get('/proposal', [AdminController::class, 'proposals'])->name('proposal');
    Route::get('/proposal/{event}', [AdminController::class, 'proposalShow'])->name('proposal.show');
    Route::post('/proposal/{event}', [AdminController::class, 'proposalVerifikasi'])->name('proposal.verifikasi');
    Route::get('/kegiatan', [AdminController::class, 'kegiatan'])->name('kegiatan');
    Route::delete('/kegiatan/{event}', [AdminController::class, 'kegiatanDestroy'])->name('kegiatan.destroy');
    Route::get('/komitmen', [AdminController::class, 'komitmen'])->name('komitmen');
    Route::get('/pelaporan', [AdminController::class, 'pelaporan'])->name('pelaporan');
});
