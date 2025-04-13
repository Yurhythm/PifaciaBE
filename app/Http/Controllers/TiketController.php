<?php

namespace App\Http\Controllers;

use App\Jobs\ExportTicketJob;
use App\Jobs\ImportTicketJob;
use App\Models\Event;
use App\Models\JobStatus;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TiketController extends Controller
{
    public function index(Request $request)
    {
        $query = Tiket::query()->join('event', 'event.id', 'tiket.event_id');

        if ($request->search) {
            $query->where('tipe', 'like', '%' . $request->search . '%');
        }

        if ($request->sort_by) {
            $query->orderBy($request->sort_by, $request->sort_dir ?? 'asc');
        }

        $query->select('tiket.*', 'event.judul');
        return $query->paginate(10);
    }

    public function tiketList($event_id)
    {
        $tikets = Tiket::where('event_id', $event_id)->get();

        return response()->json($tikets);
    }

    // public function index()
    // {
    //     return Tiket::with('event')->get();
    // }

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

        audit_trail('Tiket', 'Tambah', 'Tambah data tiket event ' . $event->judul);

        return response()->json($tiket, 201);
    }

    public function update(Request $request, $id)
    {
        $tiket = Tiket::findOrFail($id);

        $request->validate([
            'event_id' => 'sometimes|exists:event,id',
            'tipe'     => 'sometimes|string|max:255',
            'harga'    => 'sometimes|numeric|min:0',
            'tersedia' => 'boolean',
            'fitur'    => 'nullable|array',
        ]);

        $tiket->update($request->only(['event_id', 'tipe', 'harga', 'tersedia', 'fitur']));

        $event = Event::find($request->event_id);

        audit_trail('Tiket', 'Update', 'Update data tiket event ' . $event->judul);

        return response()->json($tiket);
    }

    public function destroy($id)
    {
        $tiket = Tiket::findOrFail($id);

        $event = Event::find($tiket->event_id);
        audit_trail('Tiket', 'Hapus', 'Hapus data tiket event ' . $event->judul);

        $tiket->delete();

        return response()->json(['message' => 'Tiket deleted']);
    }

    public function export(Request $request)
    {
        $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*' => 'in:id,event_id,event_judul,tipe,harga,tersedia,fitur,created_at,updated_at'
        ]);

        $jobId = (string) Str::uuid();

        JobStatus::create([
            'id' => $jobId,
            'type' => 'tiket_export',
            'status' => 'pending',
        ]);

        ExportTicketJob::dispatch($request->fields, $jobId);

        audit_trail('Tiket', 'Export', 'Export data tiket event');

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
            'type' => 'tiket_import',
            'status' => 'pending'
        ]);

        ImportTicketJob::dispatch($path, $jobId);

        audit_trail('Tiket', 'Import', 'Import data tiket event');

        return response()->json([
            'message' => 'Import job started.',
            'job_id' => $jobId
        ]);
    }
}
