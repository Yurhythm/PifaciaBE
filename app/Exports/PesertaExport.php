<?php

namespace App\Exports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PesertaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Peserta::with('tiket.event')->get()->map(function ($peserta) {
            return [
                'id'            => $peserta->id,
                'event_judul'   => $peserta->tiket->event->judul ?? null,
                'tipe_tiket'    => $peserta->tiket->tipe ?? null,
                'nama'          => $peserta->nama,
                'email'         => $peserta->email,
                'sudah_checkin' => $peserta->sudah_checkin,
                'daftar_pada'   => $peserta->daftar_pada,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'id',
            'event_judul',
            'tipe_tiket',
            'nama',
            'email',
            'sudah_checkin',
            'daftar_pada',
        ];
    }
}
