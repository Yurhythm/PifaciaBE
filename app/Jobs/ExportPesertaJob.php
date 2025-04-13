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

    protected $fields;
    protected $jobId;

    public function __construct(array $fields, string $jobId)
    {
        $this->fields = $fields;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        JobStatus::where('id', $this->jobId)->update(['status' => 'processing']);

        try {
            $filename = 'exports/peserta-' . now()->format('YmdHis') . '-' . $this->jobId . '.xlsx';

            Excel::store(new PesertaExport($this->fields), 'public/' . $filename, 'local');

            JobStatus::where('id', $this->jobId)->update([
                'status' => 'success',
                'attachment' => $filename,
                'message' => 'Peserta export berhasil.'
            ]);
        } catch (\Exception $e) {
            JobStatus::where('id', $this->jobId)->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
