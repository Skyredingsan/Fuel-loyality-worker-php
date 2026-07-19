<?php

declare(strict_types=1);

namespace FuelPoints\Result\Domain\Enums;

/**
 * Статус ежемесячного результата.
 *
 *  - DRAFT     — черновик (эксперт может редактировать)
 *  - CONFIRMED — подтверждён координатором (участвует в годовом зачёте)
 */
enum ResultStatus: string
{
    case DRAFT     = 'draft';
    case CONFIRMED = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Черновик',
            self::CONFIRMED => 'Подтверждён',
        };
    }

    public function canBeEdited(): bool
    {
        return $this === self::DRAFT;
    }
}