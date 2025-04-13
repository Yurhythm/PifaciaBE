<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tiket extends Model
{
    use HasFactory;

    protected $table = 'tiket';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'event_id', 'tipe', 'harga', 'tersedia', 'fitur'
    ];

    protected $casts = [
        'fitur' => 'array',
        'harga' => 'decimal:2',
        'tersedia' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
