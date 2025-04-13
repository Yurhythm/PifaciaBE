<?php

namespace App\Jobs;

use App\Models\JobStatus;
use App\Exports\TicketExport;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExportTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            $filename = 'exports/tiket-' . now()->format('YmdHis') . '-' . $this->jobId . '.xlsx';

            Excel::store(new TicketExport($this->fields), 'public/'.$filename, 'local');

            JobStatus::where('id', $this->jobId)->update([
                'status' => 'success',
                'attachment' => $filename,
                'message' => 'Tiket export berhasil.'
            ]);
        } catch (\Exception $e) {
            JobStatus::where('id', $this->jobId)->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
