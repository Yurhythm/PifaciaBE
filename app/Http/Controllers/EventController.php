<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        return Event::all();
    }

    public function show($id)
    {
        return Event::findOrFail($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'        => 'required|string|max:255',
            'brosur_pdf'   => 'required|mimes:pdf|file|between:100,500',
            'mulai_pada'   => 'required|date',
            'selesai_pada' => 'required|date|after_or_equal:mulai_pada',
            'daring'       => 'boolean',
            'metadata'     => 'nullable|array',
        ]);

        // Upload file PDF
        $path = $request->file('brosur_pdf')->store('brosur', 'public');

        $event = Event::create([
            'judul'        => $request->judul,
            'brosur_pdf'   => $path,
            'mulai_pada'   => $request->mulai_pada,
            'selesai_pada' => $request->selesai_pada,
            'daring'       => $request->daring ?? false,
            'metadata'     => $request->metadata,
        ]);

        return response()->json($event, 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'judul'        => 'sometimes|string|max:255',
            'brosur_pdf'   => 'nullable|mimes:pdf|file|between:100,500',
            'mulai_pada'   => 'sometimes|date',
            'selesai_pada' => 'sometimes|date|after_or_equal:mulai_pada',
            'daring'       => 'boolean',
            'metadata'     => 'nullable|array',
        ]);

        if ($request->hasFile('brosur_pdf')) {
            // Hapus file lama
            Storage::disk('public')->delete($event->brosur_pdf);

            $event->brosur_pdf = $request->file('brosur_pdf')->store('brosur', 'public');
        }

        $event->update($request->only(['judul', 'mulai_pada', 'selesai_pada', 'daring', 'metadata']));

        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        Storage::disk('public')->delete($event->brosur_pdf);
        $event->delete();

        return response()->json(['message' => 'Event deleted']);
    }
}
