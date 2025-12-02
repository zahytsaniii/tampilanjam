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
        $data = $request->except('_token');

        foreach ($data as $key => $value) {

            if (is_array($value)) {
                $value = json_encode($value); // simpan sebagai JSON
            }

            Setting::setValue($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
