<?php

namespace App\Http\Controllers;

use App\Exports\EventExport;
use App\Jobs\ExportEventJob;
use App\Jobs\ImportEventJob;
use App\Models\Event;
use App\Models\JobStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->search) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        if ($request->sort_by) {
            $query->orderBy($request->sort_by, $request->sort_dir ?? 'asc');
        }

        return $query->paginate(10);
    }

    public function eventList()
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
            'brosur_pdf'   => 'mimes:pdf|file|between:100,500',
            'mulai_pada'   => 'required|date',
            'selesai_pada' => 'required|date|after_or_equal:mulai_pada',
            'daring'       => 'boolean',
            'metadata'     => 'nullable|array',
        ]);

        // Upload file PDF
        $path = null;
        if ($request->hasFile('brosur_pdf') && $request->file('brosur_pdf')->isValid()) {
            $path = $request->file('brosur_pdf')->store('brosur', 'public');
        }

        $event = Event::create([
            'judul'        => $request->judul,
            'brosur_pdf'   => $path,
            'mulai_pada'   => $request->mulai_pada,
            'selesai_pada' => $request->selesai_pada,
            'daring'       => $request->daring ?? false,
            'metadata'     => $request->metadata,
        ]);

        audit_trail('Event', 'Tambah', 'Tambah data event ' . $request->judul);

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

        audit_trail('Event', 'Update', 'Update data event ' . $request->judul);

        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        if ($event->brosur_pdf != null) {
            Storage::disk('public')->delete($event->brosur_pdf);
        }
        audit_trail('Event', 'Hapus', 'Hapus data event '. $event->judul);

        $event->delete();


        return response()->json(['message' => 'Event deleted ' . $event->judul]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*' => 'in:judul,brosur_pdf,mulai_pada,selesai_pada,daring,metadata,created_at,updated_at',
        ]);

        $jobId = (string) Str::uuid();

        JobStatus::create([
            'id' => $jobId,
            'type' => 'event_export',
            'status' => 'pending',
        ]);

        ExportEventJob::dispatch($request->fields, $jobId);

        audit_trail('Event', 'Export', 'Export data event');

        return response()->json([
            'message' => 'Export job dispatched',
            'job_id' => $jobId,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:1024',
        ]);

        $path = $request->file('file')->store('imports');
        $jobId = (string) Str::uuid();

        JobStatus::create([
            'id' => $jobId,
            'type' => 'event_import',
            'status' => 'pending',
        ]);

        ImportEventJob::dispatch($path, $jobId);

        audit_trail('Event', 'Import', 'Import data event');

        return response()->json(['job_id' => $jobId, 'message' => 'Job dispatched.']);
    }
}
