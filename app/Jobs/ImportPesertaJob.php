<?php

namespace App\Jobs;

use App\Imports\PesertaImport;
use App\Models\JobStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportPesertaJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $jobStatusId;
    public $filePath;

    public function __construct($filePath, $jobStatusId)
    {
        $this->filePath = $filePath;
        $this->jobStatusId = $jobStatusId;
    }

    public function handle()
    {
        try {
            Excel::import(new PesertaImport, storage_path("app/{$this->filePath}"));
            JobStatus::where('id', $this->jobStatusId)->update(['status' => 'completed']);
        } catch (\Exception $e) {
            JobStatus::where('id', $this->jobStatusId)->update([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);
        }
    }
}
