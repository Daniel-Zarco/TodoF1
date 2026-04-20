<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'country',
        'base',
        'founded_year',
        'logo_url',
        'constructor_points',
        'power_unit',
    ];

    protected function casts(): array
    {
        return [
            'founded_year'        => 'integer',
            'constructor_points'  => 'integer',
        ];
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    /**
     * Total championship points scored across all race results.
     */
    public function totalPoints(): float
    {
        return (float) $this->raceResults()->sum('points');
    }
}
