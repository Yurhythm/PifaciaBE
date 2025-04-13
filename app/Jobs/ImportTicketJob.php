<?php

namespace App\Jobs;

use App\Imports\TicketImport;
use App\Models\JobStatus;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $jobId;

    public function __construct(string $filePath, string $jobId)
    {
        $this->filePath = $filePath;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        JobStatus::where('id', $this->jobId)->update(['status' => 'processing']);

        try {
            Excel::import(new TicketImport, $this->filePath);

            JobStatus::where('id', $this->jobId)->update([
                'status' => 'success',
                'message' => 'Tiket import complete.'
            ]);
        } catch (\Exception $e) {
            JobStatus::where('id', $this->jobId)->update([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);
        }
    }
}
