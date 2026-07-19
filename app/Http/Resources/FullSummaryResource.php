<?php

declare(strict_types=1);

namespace App\Http\Resources;

use FuelPoints\Result\Domain\Services\FullResultSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FullResultSummary
 */
class FullSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id'          => $this->userId,
            'user_fio'         => $this->userFio,
            'period'           => $this->period,
            'categories'       => $this->categories->map(fn ($c) => $c->toArray())->all(),
            'total_points'     => $this->totalForPeriod()->value,
            'yearly_points'    => $this->yearlyPoints,
            'level'            => $this->level ? [
                'id'                  => $this->level->id,
                'name'                => $this->level->name,
                'min_points_per_year' => $this->level->min_points_per_year,
                'privileges'          => $this->level->privileges?->toArray() ?? [],
            ] : null,
            'detailed_results' => $this->detailedResults->map(fn ($r) => [
                'id'                      => $r->id,
                'indicator_id'            => $r->indicator_id,
                'indicator_code'          => $r->indicator?->code,
                'indicator_name'          => $r->indicator?->name,
                'indicator_type'          => $r->indicator?->indicator_type->value,
                'fact_value'              => $r->fact_value,
                'calculated_points'       => $r->calculated_points,
                'supporting_document_url' => $r->supporting_document_url,
            ])->all(),
        ];
    }
}