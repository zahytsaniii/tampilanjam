<?php

namespace App\Http\Controllers;

use App\Models\QuranVerse;
use Illuminate\Http\Request;

class QuranVerseController extends Controller
{
    /**
     * Halaman admin + list ayat
     */
    public function index(Request $request)
    {
        $verses = QuranVerse::all();
        $editData = null;

        if ($request->has('edit')) {
            $editData = QuranVerse::findOrFail($request->edit);
        }

        return view('display.quran-verse-admin', compact('verses', 'editData'));
    }

    /**
     * Simpan ayat baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'surah'        => 'required|string',
            'arabic_text'  => 'required|string',
            'translation'  => 'required|string',
            'active'       => 'boolean',
        ]);

        QuranVerse::create($request->only(
            'surah',
            'arabic_text',
            'translation',
            'active'
        ));

        return back()->with('success', 'Ayat berhasil ditambahkan');
    }

    /**
     * Update ayat
     */
    public function update(Request $request, QuranVerse $quranVerse)
    {
        $request->validate([
            'surah'        => 'required|string',
            'arabic_text'  => 'required|string',
            'translation'  => 'required|string',
            'active'       => 'boolean',
        ]);

        $quranVerse->update($request->only(
            'surah',
            'arabic_text',
            'translation',
            'active'
        ));

        return back()->with('success', 'Ayat berhasil diperbarui');
    }

    /**
     * Hapus ayat
     */
    public function destroy(QuranVerse $quranVerse)
    {
        $quranVerse->delete();
        return back()->with('success', 'Ayat berhasil dihapus');
    }

    /**
     * DATA UNTUK TAMPILAN JAM
     * (hanya ayat aktif)
     */
    public function display()
    {
        $verses = QuranVerse::where('active', true)->get();
        return view('display.quran-verse-display', compact('verses'));
    }
}
