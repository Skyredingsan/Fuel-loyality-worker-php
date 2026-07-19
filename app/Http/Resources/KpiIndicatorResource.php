<?php

declare(strict_types=1);

namespace App\Http\Resources;

use FuelPoints\Kpi\Domain\Models\KpiIndicator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin KpiIndicator
 */
class KpiIndicatorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'category_id'         => $this->category_id,
            'category_code'       => $this->whenLoaded('category', fn () => $this->category->code),
            'category_name'       => $this->whenLoaded('category', fn () => $this->category->name),
            'code'                => $this->code,
            'name'                => $this->name,
            'description'         => $this->description,
            'unit'                => $this->unit,
            'indicator_type'      => $this->indicator_type->value,
            'indicator_type_label' => $this->indicator_type->label(),
            'base_value'          => $this->base_value,
            'base_weight'         => $this->base_weight,
            'extra_weight'        => $this->extra_weight,
            'penalty_weight'      => $this->penalty_weight,
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}