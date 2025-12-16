<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisplayAppearanceController extends Controller
{
    /**
     * Tampilkan halaman pengaturan theme
     */
    public function index()
    {
        // Ambil setting theme dari database
        $theme = DB::table('settings')
            ->where('key', 'theme')
            ->value('value') ?? 'theme1';

        $settings = [
            'theme' => $theme
        ];

        return view('display.appearance', compact('settings'));
    }

    /**
     * Simpan pengaturan theme
     */
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:theme1,theme2,theme3'
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'theme'],
            ['value' => $request->theme]
        );

        return back()->with('success', 'Template berhasil diganti');
    }
}
