<?php

declare(strict_types=1);

namespace FuelPoints\User\Domain\Enums;

/**
 * Роли пользователей системы «Топливный Альянс».
 *
 *  - TM           — Территориальный менеджер (получатель баллов)
 *  - EXPERT       — Эксперт (вводит результаты ТМ)
 *  - COORDINATOR  — Координатор (управляет системой, подтверждает)
 */
enum UserRole: string
{
    case TM          = 'tm';
    case EXPERT      = 'expert';
    case COORDINATOR = 'coordinator';

    public function label(): string
    {
        return match ($this) {
            self::TM          => 'Территориальный менеджер',
            self::EXPERT      => 'Эксперт',
            self::COORDINATOR => 'Координатор',
        };
    }

    public function canEnterResults(): bool
    {
        return in_array($this, [self::EXPERT, self::COORDINATOR], true);
    }

    public function canManageSystem(): bool
    {
        return $this === self::COORDINATOR;
    }

    public function canConfirmResults(): bool
    {
        return $this === self::COORDINATOR;
    }
}