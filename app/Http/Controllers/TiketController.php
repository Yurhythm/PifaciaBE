<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Tiket;
use Illuminate\Http\Request;

class TiketController extends Controller
{
    public function index()
    {
        return Tiket::with('event')->get();
    }

    public function show($id)
    {
        return Tiket::with('event')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'tipe'     => 'required|string|max:255',
            'harga'    => 'required|numeric|min:0',
            'tersedia' => 'boolean',
            'fitur'    => 'nullable|array',
        ]);

        $tiket = Tiket::create([
            'event_id' => $request->event_id,
            'tipe'     => $request->tipe,
            'harga'    => $request->harga,
            'tersedia' => $request->tersedia ?? true,
            'fitur'    => $request->fitur,
        ]);

        $event = Event::find($request->event_id);

        audit_trail('Tiket', 'Tambah', 'Tambah data tiket event'.$event->judul);

        return response()->json($tiket, 201);
    }

    public function update(Request $request, $id)
    {
        $tiket = Tiket::findOrFail($id);

        $request->validate([
            'event_id' => 'sometimes|exists:events,id',
            'tipe'     => 'sometimes|string|max:255',
            'harga'    => 'sometimes|numeric|min:0',
            'tersedia' => 'boolean',
            'fitur'    => 'nullable|array',
        ]);

        $tiket->update($request->only(['event_id', 'tipe', 'harga', 'tersedia', 'fitur']));

        $event = Event::find($request->event_id);

        audit_trail('Tiket', 'Update', 'Update data tiket event'.$event->judul);

        return response()->json($tiket);
    }

    public function destroy($id)
    {
        $tiket = Tiket::findOrFail($id);

        $event = Event::find($tiket->event_id);
        audit_trail('Tiket', 'Hapus', 'Hapus data tiket event'.$event->judul);

        $tiket->delete();

        return response()->json(['message' => 'Tiket deleted']);
    }
}
