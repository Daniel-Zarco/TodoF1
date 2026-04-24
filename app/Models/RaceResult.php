<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'grand_prix_id',
        'driver_id',
        'team_id',
        'position',
        'points',
        'fastest_lap',
        'pole_position',
        'dnf',
        'total_time',
        'laps_completed',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'points' => 'decimal:1',
            'fastest_lap' => 'boolean',
            'pole_position' => 'boolean',
            'dnf' => 'boolean',
            'laps_completed' => 'integer',
        ];
    }

    public function grandPrix()
    {
        return $this->belongsTo(\App\Models\GrandPrix::class);
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }

    public function team()
    {
        return $this->belongsTo(\App\Models\Team::class);
    }
}
