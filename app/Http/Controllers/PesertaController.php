<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Http\Request;

class PesertaController extends Controller
{
    public function index()
    {
        return Peserta::with('tiket.event')->get();
    }

    public function show($id)
    {
        return Peserta::with('tiket.event')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tiket_id'      => 'required|exists:tiket,id',
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email',
            'sudah_checkin' => 'boolean',
            'daftar_pada'   => 'required|date',
        ]);

        $peserta = Peserta::create([
            'tiket_id'      => $request->tiket_id,
            'nama'          => $request->nama,
            'email'         => $request->email,
            'sudah_checkin' => $request->sudah_checkin ?? false,
            'daftar_pada'   => $request->daftar_pada,
        ]);

        return response()->json($peserta, 201);
    }

    public function update(Request $request, $id)
    {
        $peserta = Peserta::findOrFail($id);

        $request->validate([
            'tiket_id'      => 'sometimes|exists:tiket,id',
            'nama'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|email',
            'sudah_checkin' => 'boolean',
            'daftar_pada'   => 'sometimes|date',
        ]);

        $peserta->update($request->only([
            'tiket_id',
            'nama',
            'email',
            'sudah_checkin',
            'daftar_pada'
        ]));

        return response()->json($peserta);
    }

    public function destroy($id)
    {
        $peserta = Peserta::findOrFail($id);
        $peserta->delete();

        return response()->json(['message' => 'Peserta deleted']);
    }
}
