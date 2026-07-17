<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Domain\Models;

use Carbon\Carbon;
use FuelPoints\Kpi\Domain\Enums\IndicatorType;
use FuelPoints\Result\Domain\Models\IndicatorResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int             $id
 * @property int             $category_id
 * @property string          $code         ПМ1, ДПМ1, ШОЭК, ...
 * @property string          $name
 * @property string|null     $description
 * @property string          $unit         %, шт, чел
 * @property IndicatorType   $indicator_type
 * @property float|null      $base_value
 * @property int|null        $base_weight
 * @property int|null        $extra_weight
 * @property int|null        $penalty_weight
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 */
class KpiIndicator extends Model
{
    protected $table = 'kpi_indicators';

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'unit',
        'indicator_type',
        'base_value',
        'base_weight',
        'extra_weight',
        'penalty_weight',
    ];

    protected $casts = [
        'indicator_type' => IndicatorType::class,
        'base_value'     => 'float',
        'base_weight'    => 'integer',
        'extra_weight'   => 'integer',
        'penalty_weight' => 'integer',
    ];

    /**
     * @return BelongsTo<KpiCategory, self>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(KpiCategory::class, 'category_id');
    }

    /**
     * @return HasMany<IndicatorResult>
     */
    public function results(): HasMany
    {
        return $this->hasMany(IndicatorResult::class, 'indicator_id');
    }

    /**
     * Возвращает вес показателя в зависимости от типа.
     */
    public function weight(): ?int
    {
        return match ($this->indicator_type) {
            IndicatorType::BASE    => $this->base_weight,
            IndicatorType::EXTRA   => $this->extra_weight,
            IndicatorType::PENALTY => $this->penalty_weight,
        };
    }
}