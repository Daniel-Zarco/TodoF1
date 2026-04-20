<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Circuit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'country',
        'city',
        'length_km',
        'lap_count',
        'lap_record',
        'lap_record_driver',
        'photo_url',
    ];

    protected function casts(): array
    {
        return [
            'length_km' => 'decimal:3',
            'lap_count' => 'integer',
        ];
    }

    public function grandPrix(): HasMany
    {
        return $this->hasMany(GrandPrix::class);
    }
}
