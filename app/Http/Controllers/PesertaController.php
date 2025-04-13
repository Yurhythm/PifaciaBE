<?php

namespace App\Http\Controllers;

use App\Jobs\ExportPesertaJob;
use App\Jobs\ImportPesertaJob;
use App\Models\JobStatus;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PesertaController extends Controller
{
    public function index(Request $request)
    {
        $query = Peserta::query()->join('tiket', 'tiket.id', 'peserta.tiket_id')->join('event', 'event.id', 'tiket.event_id');

        if ($request->search) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->sort_by) {
            $query->orderBy($request->sort_by, $request->sort_dir ?? 'asc');
        }

        $query->select('peserta.*', 'event.judul', 'tiket.tipe')->with('tiket.event');
        return $query->paginate(10);
    }

    // public function index()
    // {
    //     return Peserta::with('tiket.event')->get();
    // }

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

        audit_trail('Peserta', 'Tambah', 'Tambah data peserta ' . $request->nama);

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

        audit_trail('Peserta', 'Update', 'Update data peserta ' . $request->nama);

        return response()->json($peserta);
    }

    public function destroy($id)
    {
        $peserta = Peserta::findOrFail($id);
        audit_trail('Peserta', 'Hapus', 'Hapus data peserta ' . $peserta->nama);

        $peserta->delete();

        return response()->json(['message' => 'Peserta deleted']);
    }

    public function export(Request $request)
    {
        $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*' => 'in:id,event_id,event_judul,tipe_tiket,nama,email,sudah_checkin,daftar_pada,created_at,updated_at'
        ]);

        $jobId = (string) Str::uuid();

        JobStatus::create([
            'id' => $jobId,
            'type' => 'peserta_export',
            'status' => 'pending',
        ]);

        ExportPesertaJob::dispatch($request->fields, $jobId);

        audit_trail('Peserta', 'Export', 'Export data peserta event');

        return response()->json([
            'message' => 'Export job started.',
            'job_id' => $jobId,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $jobId = (string) Str::uuid();
        $path = $request->file('file')->storeAs('imports', 'tiket-' . $jobId . '.xlsx');

        JobStatus::create([
            'id' => $jobId,
            'type' => 'peserta_import',
            'status' => 'pending'
        ]);

        ImportPesertaJob::dispatch($path, $jobId);

        audit_trail('Peserta', 'Import', 'Import data peserta event');

        return response()->json([
            'message' => 'Import job started.',
            'job_id' => $jobId
        ]);
    }
}
