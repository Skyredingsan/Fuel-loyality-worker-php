<?php

declare(strict_types=1);

namespace FuelPoints\Result\Application\DTO;

use FuelPoints\Shared\Domain\ValueObjects\Period;

/**
 * Запрос на ввод/обновление результатов за месяц.
 */
final readonly class EnterResultRequestDto
{
    /**
     * @param array<int, IndicatorResultInputDto> $results
     */
    public function __construct(
        public int $userId,
        public Period $period,
        public array $results,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $results = array_map(
            IndicatorResultInputDto::fromArray(...),
            $data['results'] ?? [],
        );

        return new self(
            userId: (int) $data['user_id'],
            period: Period::fromString($data['period']),
            results: $results,
        );
    }
}