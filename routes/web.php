<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\PrayerScheduleController;
use App\Http\Controllers\RunningTextController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

//Display
Route::get('/', [DisplayController::class, 'index'])->name('display');

// Login routes
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    //Device Settings
    Route::get('/device-settings', [DeviceController::class, 'index'])->name('device.settings');
    Route::post('/device-settings/password', [DeviceController::class, 'changePassword'])->name('device.password');
    Route::post('/device-settings/factory-reset', [DeviceController::class, 'factoryReset'])->name('device.reset');
    Route::post('/device-settings/license', [DeviceController::class, 'activateLicense'])->name('device.license');
    
    // Settings
    Route::get('/settings/masjid', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/masjid', [SettingController::class, 'save'])->name('settings.save');

    // Jadwal sholat
    Route::get('/schedule', [PrayerScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/fetch-api', [PrayerScheduleController::class, 'fetchFromAPI'])->name('schedule.fetch-api');
    Route::post('/schedule/import', [PrayerScheduleController::class, 'import'])->name('schedule.import');
    Route::post('/schedule/generate', [PrayerScheduleController::class, 'generate'])->name('schedule.generate');
    Route::post('/schedule/generate-month', [PrayerScheduleController::class, 'generateMonth'])->name('schedule.generate-month');
    Route::post('/schedule/store', [PrayerScheduleController::class, 'store'])->name('schedule.store');
    Route::get('/schedule/template', [PrayerScheduleController::class, 'downloadTemplate'])->name('schedule.template');

    // Display settings
    Route::get('/running-text/display', [RunningTextController::class, 'index'])->name('display.runningtext');
    Route::resource('running-text', RunningTextController::class);

    Route::get('/display/appearance', function () {
        return view('display.appearance');
    })->name('display.appearance');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
