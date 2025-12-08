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

        // ✅ Ambil MAC Address
        $mac = $this->getServerMacAddress();

        // ✅ Masking MAC untuk keamanan (PRODUCTION SAFE)
        if ($mac) {
            $maskedMac = substr($mac, 0, 8) . ':XX:XX';
        } else {
            $maskedMac = '-';
        }

        return view('device.settings', [
            'settings' => $settings,
            'user' => $user,
            'mac' => $mac, 
        ]);
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

    // ===========================
    // LICENSE
    // ===========================
    private function getServerMacAddress()
    {
        if (stristr(PHP_OS, 'WIN')) {
            $output = shell_exec('getmac');
            preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $output, $matches);
            return $matches[0] ?? null;
        } else {
            $output = shell_exec("ip link show | grep link/ether | head -n 1");
            preg_match('/([0-9a-f]{2}:){5}[0-9a-f]{2}/', $output, $matches);
            return $matches[0] ?? null;
        }
    }

    private function generateLicenseFromMac($mac)
    {
        $secret = config('app.key'); // SECRET INTERNAL APLIKASI
        return strtoupper(hash_hmac('sha256', $mac, $secret));
    }

    public function showGeneratedLicense()
    {
        $mac = $this->getServerMacAddress();
        $license = $this->generateLicenseFromMac($mac);

        return response()->json([
            'mac_address' => $mac,
            'valid_license' => $license
        ]);
    }

    public function activateLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required'
        ]);

        $mac = $this->getServerMacAddress();

        if (!$mac) {
            return back()->with('error', 'Gagal mendeteksi MAC Address server.');
        }

        $generatedLicense = $this->generateLicenseFromMac($mac);

        if ($request->license_key === $generatedLicense) {

            Setting::updateOrCreate(['key' => 'license_key'], [
                'value' => $request->license_key
            ]);

            Setting::updateOrCreate(['key' => 'license_mac'], [
                'value' => $mac
            ]);

            Setting::updateOrCreate(['key' => 'license_status'], [
                'value' => 'valid'
            ]);

            Setting::updateOrCreate(['key' => 'license_expired_at'], [
                'value' => Carbon::now()->addYear()->toDateString()
            ]);

            return back()->with('success', 'License berhasil diaktifkan!');
        }

        Setting::updateOrCreate(['key' => 'license_key'], [
            'value' => $request->license_key
        ]);
        Setting::updateOrCreate(['key' => 'license_status'], [
            'value' => 'invalid'
        ]);

        return back()->with('error', 'License tidak valid untuk device ini.');
    }
}
