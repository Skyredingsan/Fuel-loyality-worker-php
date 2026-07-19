<?php

declare(strict_types=1);

namespace App\Http\Resources;

use FuelPoints\Result\Domain\Models\MonthlyResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MonthlyResult
 */
class MonthlyResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'expert_id'    => $this->expert_id,
            'period'       => $this->period->format('Y-m'),
            'status'       => $this->status->value,
            'status_label' => $this->status->label(),
            'user'         => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'expert'       => $this->whenLoaded('expert', fn () => new UserResource($this->expert)),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}