<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('settings.masjid', compact('settings'));
    }

    public function save(Request $request)
    {
        // ✅ VALIDASI AUDIO (OPTIONAL)
        $request->validate([
            'audio_file' => 'nullable|mimes:mp3|max:5120', // max 5MB
        ]);

        // ✅ PROSES SEMUA INPUT KECUALI FILE
        $data = $request->except(['_token', 'audio_file']);

        foreach ($data as $key => $value) {

            if (is_array($value)) {
                $value = json_encode($value); // simpan sebagai JSON
            }

            Setting::setValue($key, $value);
        }

        // ✅ KHUSUS PROSES UPLOAD AUDIO MP3
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $filename = 'adzan_' . time() . '.mp3';

            $file->move(public_path('audio'), $filename);

            // Simpan path ke database
            Setting::setValue('audio_file', 'audio/' . $filename);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
