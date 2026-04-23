<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

    protected $fillable = ['year'];

    public function seasonEntries(): HasMany
    {
        return $this->hasMany(SeasonEntry::class);
    }
}
