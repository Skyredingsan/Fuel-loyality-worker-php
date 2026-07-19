<?php

declare(strict_types=1);

namespace App\Http\Resources;

use FuelPoints\Level\Domain\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Level
 */
class LevelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'min_points_per_year' => $this->min_points_per_year,
            'privileges'          => $this->privileges?->toArray() ?? [],
            'created_at'          => $this->created_at?->toIso8601String(),
        ];
    }
}