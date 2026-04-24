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
        'power_unit',
    ];

    protected function casts(): array
    {
        return [
            'founded_year' => 'integer',
        ];
    }

    public function seasonEntries(): HasMany
    {
        return $this->hasMany(SeasonEntry::class);
    }

    public function raceResults()
    {
        return $this->hasMany(\App\Models\RaceResult::class);
    }

    /**
     * Total championship points scored across all race results.
     */
    public function totalPoints(): float
    {
        return (float) $this->raceResults()->sum('points');
    }
}
