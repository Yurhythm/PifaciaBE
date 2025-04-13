<?php

namespace App\Exports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PesertaExport implements FromCollection, WithHeadings
{
    protected $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function collection()
    {
        return Peserta::with('tiket.event')
            ->get()
            ->map(function ($peserta) {
                $data = [];

                foreach ($this->fields as $field) {
                    if ($field === 'event_judul') {
                        $data[$field] = $peserta->event->judul ?? null;
                    } else
                    if ($field === 'tipe_tiket') {
                        $data[$field] = $peserta->tiket->tipe ?? null;
                    } else {
                        $data[$field] = $peserta->$field ?? null;
                    }
                }

                return $data;
            });
    }

    public function headings(): array
    {
        return $this->fields;
    }
}
