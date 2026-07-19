<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\DTO;

/**
 * Один пункт ввода результата (от фронта).
 */
final readonly class IndicatorResultInputDto
{
    public function __construct(
        public string $indicatorCode,
        public ?float $factValue,
        public ?string $documentUrl = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            indicatorCode: $data['indicator_code'],
            factValue: $data['fact_value'] ?? null,
            documentUrl: $data['document_url'] ?? null,
        );
    }
}