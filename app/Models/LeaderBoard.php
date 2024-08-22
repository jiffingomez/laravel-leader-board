<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaderBoard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'age',
        'points',
        'address',
    ];

    /**
     * Get the winner boards associated with this leaderboard.
     *
     * @return HasMany
     */
    public function winnerBoards(): HasMany
    {
        return $this->hasMany(WinnerBoard::class);
    }
}
