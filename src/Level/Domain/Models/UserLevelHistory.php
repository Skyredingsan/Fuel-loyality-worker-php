<?php

declare(strict_types=1);

namespace FuelPoints\Level\Domain\Models;

use Carbon\Carbon;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int      $id
 * @property int      $user_id
 * @property int      $level_id
 * @property Carbon   $assigned_at
 * @property int      $points_year
 * @property Carbon   $created_at
 * @property Carbon   $updated_at
 *
 * @property User     $user
 * @property Level    $level
 */
class UserLevelHistory extends Model
{
    protected $table = 'user_level_history';

    protected $fillable = [
        'user_id',
        'level_id',
        'assigned_at',
        'points_year',
    ];

    protected $casts = [
        'assigned_at' => 'date',
        'points_year' => 'integer',
    ];

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Level, self>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }
}