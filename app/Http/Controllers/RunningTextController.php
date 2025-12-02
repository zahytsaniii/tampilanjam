<?php

namespace App\Http\Controllers;

use App\Models\RunningText;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    public function index()
    {
        $texts = RunningText::all();
        return view('display.runningtext', compact('texts'));
    }

    public function edit($id)
    {
        $texts = RunningText::all();
        $editData = RunningText::findOrFail($id);

        return view('display.runningtext', compact('texts', 'editData'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'active' => 'boolean'
        ]);

        RunningText::create($request->only('message', 'active'));

        return back()->with('success', 'Running text berhasil ditambahkan');
    }

    public function update(Request $request, RunningText $runningText)
    {
        $request->validate([
            'message' => 'required|string',
            'active' => 'boolean'
        ]);

        $runningText->update($request->only('message', 'active'));

        return back()->with('success', 'Running text berhasil diperbarui');
    }

    public function destroy(RunningText $runningText)
    {
        $runningText->delete();
        return back()->with('success', 'Running text berhasil dihapus');
    }
}
