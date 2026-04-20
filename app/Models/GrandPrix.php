<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrandPrix extends Model
{
    use HasFactory;

    protected $table = 'grand_prixes';

    protected $fillable = [
        'circuit_id',
        'name',
        'season',
        'date',
        'round_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'season'       => 'integer',
            'round_number' => 'integer',
        ];
    }

    public function circuit(): BelongsTo
    {
        return $this->belongsTo(Circuit::class);
    }

    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    /**
     * Returns the winning driver of this Grand Prix, or null.
     */
    public function winner(): ?Driver
    {
        $result = $this->raceResults()->where('position', 1)->with('driver')->first();
        return $result?->driver;
    }
}
