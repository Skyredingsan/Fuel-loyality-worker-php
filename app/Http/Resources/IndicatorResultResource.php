<?php

declare(strict_types=1);

namespace App\Http\Resources;

use FuelPoints\Result\Domain\Models\IndicatorResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IndicatorResult
 */
class IndicatorResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'monthly_result_id'       => $this->monthly_result_id,
            'indicator_id'            => $this->indicator_id,
            'fact_value'              => $this->fact_value,
            'calculated_points'       => $this->calculated_points,
            'supporting_document_url' => $this->supporting_document_url,
            'indicator'               => $this->whenLoaded('indicator', fn () => new KpiIndicatorResource($this->indicator)),
            'created_at'              => $this->created_at?->toIso8601String(),
        ];
    }
}