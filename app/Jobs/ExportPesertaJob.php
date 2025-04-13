<?php

namespace App\Jobs;

use App\Exports\PesertaExport;
use App\Models\JobStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExportPesertaJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $jobStatusId;

    public function __construct($jobStatusId)
    {
        $this->jobStatusId = $jobStatusId;
    }

    public function handle()
    {
        JobStatus::where('id', $this->jobId)->update(['status' => 'processing']);

        try {
            $filename = 'exports/peserta-' . now()->format('YmdHis') . '-' . $this->jobId . '.xlsx';
            Excel::store(new PesertaExport, 'public/'.$filename, 'local');
            JobStatus::where('id', $this->jobStatusId)->update([
                'status'     => 'completed',
                'attachment' => $filename,
                'message' => 'Event export berhasil.'
            ]);
        } catch (\Exception $e) {
            JobStatus::where('id', $this->jobStatusId)->update([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);
        }
    }
}
