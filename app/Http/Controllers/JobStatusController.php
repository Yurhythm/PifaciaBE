<?php

namespace App\Http\Controllers;

use App\Models\JobStatus;

class JobStatusController extends Controller
{

    public function checkQueueStatus($id)
    {
        $status = JobStatus::find($id);

        if (!$status) {
            return response()->json(['message' => 'Job ID not found'], 404);
        }

        return response()->json([
            'job_id' => $status->id,
            'type' => $status->type,
            'status' => $status->status,
            'message' => $status->message,
            'attachment_url' => $status->attachment
                ? asset('storage/' . $status->attachment)
                : null,
            'updated_at' => $status->updated_at,
        ]);
    }
}
