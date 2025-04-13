<?php

namespace App\Jobs;

use App\Exports\EventExport;
use App\Models\JobStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ExportEventJob implements ShouldQueue
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
            $fileName = 'exports/event-' . now()->format('YmdHis') . '-' . $this->jobId . '.xlsx';
            Excel::store(new EventExport($this->fields), 'public/'.$fileName, 'local');

            JobStatus::where('id', $this->jobId)->update([
                'status' => 'success',
                'attachment' => $fileName,
                'message' => 'Event export berhasil.'
            ]);
        } catch (\Exception $e) {
            JobStatus::where('id', $this->jobId)->update([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
