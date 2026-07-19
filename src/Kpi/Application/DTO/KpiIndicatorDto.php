<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Application\DTO;

use FuelPoints\Kpi\Domain\Enums\IndicatorType;

final readonly class KpiIndicatorDto
{
    public function __construct(
        public ?int $id,
        public ?int $categoryId,
        public ?string $categoryCode,
        public ?string $categoryName,
        public string $code,
        public string $name,
        public ?string $description,
        public string $unit,
        public IndicatorType $indicatorType,
        public ?float $baseValue,
        public ?int $baseWeight,
        public ?int $extraWeight,
        public ?int $penaltyWeight,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            categoryId: $data['category_id'] ?? null,
            categoryCode: $data['category_code'] ?? null,
            categoryName: $data['category_name'] ?? null,
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? null,
            unit: $data['unit'],
            indicatorType: \FuelPoints\Kpi\Domain\Enums\IndicatorType::from($data['indicator_type']),
            baseValue: isset($data['base_value']) ? (float) $data['base_value'] : null,
            baseWeight: isset($data['base_weight']) ? (int) $data['base_weight'] : null,
            extraWeight: isset($data['extra_weight']) ? (int) $data['extra_weight'] : null,
            penaltyWeight: isset($data['penalty_weight']) ? (int) $data['penalty_weight'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'category_id'    => $this->categoryId,
            'category_code'  => $this->categoryCode,
            'category_name'  => $this->categoryName,
            'code'           => $this->code,
            'name'           => $this->name,
            'description'    => $this->description,
            'unit'           => $this->unit,
            'indicator_type' => $this->indicatorType->value,
            'base_value'     => $this->baseValue,
            'base_weight'    => $this->baseWeight,
            'extra_weight'   => $this->extraWeight,
            'penalty_weight' => $this->penaltyWeight,
        ];
    }
}