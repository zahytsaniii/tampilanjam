<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function index()
    {
        Setting::firstOrCreate(
            ['key' => 'device_id'],
            ['value' => 'DEV-' . strtoupper(Str::random(12))]
        );

        Setting::firstOrCreate(
            ['key' => 'license_status'],
            ['value' => 'invalid']
        );

        $settings = Setting::pluck('value', 'key')->toArray();
        $user = Auth::user();

        return view('device.settings', compact('settings', 'user'));
    }

    // ===========================
    // GANTI PASSWORD
    // ===========================
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama salah.');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    // ===========================
    // FACTORY RESET
    // ===========================
    public function factoryReset()
    {
        DB::table('prayer_schedules')->truncate();
        DB::table('running_texts')->truncate();

        DB::table('settings')->whereNotIn('key', [
            'device_id',
            'license_key',
            'license_status',
            'license_expired_at'
        ])->delete();

        return back()->with('success', 'Device berhasil di-reset ke pengaturan awal.');
    }


    public function activateLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required'
        ]);

        if ($request->license_key === 'MASJID-2025-VALID') {

            Setting::updateOrCreate(['key' => 'license_key'], [
                'value' => $request->license_key
            ]);

            Setting::updateOrCreate(['key' => 'license_status'], [
                'value' => 'valid'
            ]);

            Setting::updateOrCreate(['key' => 'license_expired_at'], [
                'value' => Carbon::now()->addYear()->toDateString()
            ]);

            return back()->with('success', 'License berhasil diaktifkan!');
        }

        Setting::updateOrCreate(
            ['key' => 'license_key'],
            ['value' => $request->license_key]
        );
        Setting::updateOrCreate(
            ['key' => 'license_status'],
            ['value' => 'invalid']
        );

        return back()->with('error', 'License tidak valid.');
    }
}
