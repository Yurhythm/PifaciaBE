<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\Tiket;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TicketImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $event = Event::where('judul', $data['event_judul'] ?? '')->first();

        if (!$event) {
            return;
        }

        Tiket::updateOrCreate(
            ['id' => $data['id'] ?? Str::uuid()],
            [
                'event_id' => $event->id,
                'tipe'     => $data['tipe'] ?? null,
                'harga'    => $data['harga'] ?? 0,
                'tersedia' => isset($data['tersedia']) ? filter_var($data['tersedia'], FILTER_VALIDATE_BOOLEAN) : true,
                'fitur'    => isset($data['fitur']) ? json_decode($data['fitur'], true) : null,
            ]
        );
    }
}
