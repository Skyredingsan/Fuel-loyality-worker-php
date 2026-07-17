<?php

declare(strict_types=1);

namespace FuelPoints\Level\Domain\Models;

use Carbon\Carbon;
use FuelPoints\Shared\Domain\ValueObjects\Privileges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int             $id
 * @property string          $name
 * @property int             $min_points_per_year
 * @property Privileges      $privileges
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 */
class Level extends Model
{
    protected $table = 'levels';

    protected $fillable = [
        'name',
        'min_points_per_year',
        'privileges',
    ];

    protected $casts = [
        'min_points_per_year' => 'integer',
        'privileges'          => Privileges::class,
    ];

    /**
     * @return HasMany<UserLevelHistory>
     */
    public function history(): HasMany
    {
        return $this->hasMany(UserLevelHistory::class, 'level_id');
    }
}