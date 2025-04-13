<?php

namespace App\Imports;

use App\Models\Peserta;
use App\Models\Tiket;
use App\Models\Event;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PesertaImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $event = Event::where('judul', $data['event_judul'])->first();
        if (!$event) return;

        $tiket = Tiket::where('event_id', $event->id)
                      ->where('tipe', $data['tipe_tiket'])
                      ->first();
        if (!$tiket) return;

        Peserta::updateOrCreate(
            ['id' => $data['id'] ?? Str::uuid()],
            [
                'tiket_id'       => $tiket->id,
                'nama'           => $data['nama'],
                'email'          => $data['email'],
                'sudah_checkin'  => filter_var($data['sudah_checkin'], FILTER_VALIDATE_BOOLEAN),
                'daftar_pada'    => $data['daftar_pada'],
            ]
        );
    }
}
