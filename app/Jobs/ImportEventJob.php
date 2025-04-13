<?php

namespace App\Jobs;

use App\Imports\EventImport;
use App\Models\JobStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ImportEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $jobId;

    public function __construct($filePath, $jobId)
    {
        $this->filePath = $filePath;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        JobStatus::where('id', $this->jobId)->update(['status' => 'processing']);

        try {
            Excel::import(new EventImport, storage_path("app/{$this->filePath}"));

            JobStatus::where('id', $this->jobId)->update([
                'status' => 'success',
                'message' => 'Import completed successfully.'
            ]);
        } catch (\Exception $e) {
            JobStatus::where('id', $this->jobId)->update([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);

            throw $e; // agar masuk ke failed_jobs
        }
    }
}
