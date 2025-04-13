<?php

namespace App\Exports;

use App\Models\Tiket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TicketExport implements FromCollection, WithHeadings
{
    protected $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function collection()
    {
        return Tiket::with('event')
            ->get()
            ->map(function ($tiket) {
                $data = [];

                foreach ($this->fields as $field) {
                    if ($field === 'event_judul') {
                        $data[$field] = $tiket->event->judul ?? null;
                    } elseif ($field === 'fitur') {
                        $data[$field] = json_encode($tiket->fitur);
                    } else {
                        $data[$field] = $tiket->$field ?? null;
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
