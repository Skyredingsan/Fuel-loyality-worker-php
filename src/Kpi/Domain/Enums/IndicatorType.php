<?php

declare(strict_types=1);

namespace FuelPoints\Kpi\Domain\Enums;

/**
 * Тип KPI-показателя.
 *
 *  - BASE    — базовый:  факт >= base_value ? base_weight : 0
 *  - EXTRA   — бонусный: round(fact * extra_weight)
 *  - PENALTY — штрафной: round(fact * penalty_weight)  (penalty_weight < 0)
 */
enum IndicatorType: string
{
    case BASE    = 'base';
    case EXTRA   = 'extra';
    case PENALTY = 'penalty';

    public function label(): string
    {
        return match ($this) {
            self::BASE    => 'Базовый',
            self::EXTRA   => 'Дополнительный',
            self::PENALTY => 'Штрафной',
        };
    }
}