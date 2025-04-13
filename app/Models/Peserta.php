<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    use HasFactory;

    protected $table = 'peserta';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'tiket_id', 'nama', 'email', 'sudah_checkin', 'daftar_pada'
    ];

    protected $casts = [
        'sudah_checkin' => 'boolean',
        'daftar_pada' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($peserta) {
            $peserta->id = (string) Str::uuid();
        });
    }

    public function tiket()
    {
        return $this->belongsTo(Tiket::class);
    }

    public function event()
    {
        return $this->tiket->event(); // lewat relasi tiket
    }
}
