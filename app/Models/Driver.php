<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'nationality',
        'date_of_birth',
        'number',
        'photo_url',
        'bio',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'number' => 'integer',
        ];
    }

    public function seasonEntries(): HasMany
    {
        return $this->hasMany(SeasonEntry::class);
    }

    /**
     * Retrieves the team the driver raced for in a specific season year.
     * Checks loaded relationships first to prevent N+1 queries when properly eager loaded.
     */
    public function teamForSeason(int $seasonYear): ?Team
    {
        if ($this->relationLoaded('seasonEntries')) {
            $entry = $this->seasonEntries->first(function ($entry) use ($seasonYear) {
                return $entry->season?->year === $seasonYear;
            });

            if ($entry) {
                return $entry->team;
            }
        }

        return $this->seasonEntries()
            ->whereHas('season', fn($q) => $q->where('year', $seasonYear))
            ->first()
                ?->team;
    }

    /**
     * Helper for the current UI: gets the driver's team for the currently active season (2024).
     */
    public function currentTeam(): ?Team
    {
        return $this->teamForSeason(2024);
    }

    public function raceResults()
    {
        return $this->hasMany(\App\Models\RaceResult::class);
    }

    /**
     * Total championship points scored by this driver.
     */
    public function totalPoints(): float
    {
        return (float) $this->raceResults()->sum('points');
    }

    /**
     * Computed age from date_of_birth.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
