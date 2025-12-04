<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    public function index()
    {
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
            'password' => bcrypt($request->new_password),
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
            'license_status'
        ])->delete();

        return back()->with('success', 'Device berhasil di-reset ke pengaturan awal.');
    }
}
