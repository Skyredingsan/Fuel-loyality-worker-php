<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Models;

use Carbon\Carbon;
use FuelPoints\Result\Domain\Enums\ResultStatus;
use FuelPoints\User\Domain\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int            $id
 * @property int            $user_id        ТМ
 * @property int|null       $expert_id      Эксперт
 * @property Carbon         $period         Первый день месяца
 * @property ResultStatus   $status         draft | confirmed
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 *
 * @property User           $user
 * @property User|null      $expert
 * @property Collection<int, IndicatorResult> $indicatorResults
 */
class MonthlyResult extends Model
{
    protected $table = 'monthly_results';

    protected $fillable = [
        'user_id',
        'expert_id',
        'period',
        'status',
    ];

    protected $casts = [
        'period' => 'date',
        'status' => ResultStatus::class,
    ];

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    /**
     * @return HasMany<IndicatorResult>
     */
    public function indicatorResults(): HasMany
    {
        return $this->hasMany(IndicatorResult::class, 'monthly_result_id');
    }
}