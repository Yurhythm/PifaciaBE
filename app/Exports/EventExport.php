<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventExport implements FromCollection, WithHeadings
{
    protected $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function collection()
    {
        return Event::all()->map(function ($event) {
            $data = [];

            foreach ($this->fields as $field) {
                if ($field === 'metadata') {
                    $data[$field] = json_encode($event->metadata);
                } else {
                    $data[$field] = $event->{$field};
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
