<?php

declare(strict_types=1);

namespace App\Http\Resources;

use FuelPoints\User\Domain\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'email'        => $this->email,
            'role'         => $this->role->value,
            'role_label'   => $this->role->label(),
            'fio'          => $this->fio,
            'cluster_name' => $this->cluster_name,
            'azs_count'    => $this->azs_count,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}