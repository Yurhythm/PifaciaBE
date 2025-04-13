<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tiket extends Model
{
    use HasFactory;
    use SoftDeletes;

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
