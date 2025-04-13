<?php

namespace App\Imports;

use App\Models\Event;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EventImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $event = new Event();
        $event->id = (string) Str::uuid();

        $event->judul = $row['judul'] ?? null;
        $event->brosur_pdf = $row['brosur_pdf'] ?? null;
        $event->mulai_pada = $row['mulai_pada'] ?? null;
        $event->selesai_pada = $row['selesai_pada'] ?? null;
        $event->daring = filter_var($row['daring'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (isset($row['metadata'])) {
            $decoded = json_decode($row['metadata'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $event->metadata = $decoded;
            }
        }

        return $event;
    }
}
