<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'event';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'judul', 'brosur_pdf', 'mulai_pada', 'selesai_pada', 'daring', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'mulai_pada' => 'datetime',
        'selesai_pada' => 'datetime',
        'daring' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
