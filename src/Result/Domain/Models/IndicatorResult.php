<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Models;

use Carbon\Carbon;
use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int           $id
 * @property int           $monthly_result_id
 * @property int           $indicator_id
 * @property float|null    $fact_value
 * @property int           $calculated_points
 * @property string|null   $supporting_document_url
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 *
 * @property MonthlyResult $monthlyResult
 * @property KpiIndicator  $indicator
 */
class IndicatorResult extends Model
{
    protected $table = 'indicator_results';

    protected $fillable = [
        'monthly_result_id',
        'indicator_id',
        'fact_value',
        'calculated_points',
        'supporting_document_url',
    ];

    protected $casts = [
        'fact_value'        => 'float',
        'calculated_points' => 'integer',
    ];

    /**
     * @return BelongsTo<MonthlyResult, self>
     */
    public function monthlyResult(): BelongsTo
    {
        return $this->belongsTo(MonthlyResult::class, 'monthly_result_id');
    }

    /**
     * @return BelongsTo<KpiIndicator, self>
     */
    public function indicator(): BelongsTo
    {
        return $this->belongsTo(KpiIndicator::class, 'indicator_id');
    }
}