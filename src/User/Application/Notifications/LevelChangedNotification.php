<?php

declare(strict_types=1);

namespace FuelPoints\User\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Уведомление ТМ: достигнут новый уровень.
 */
final class LevelChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $newLevelName,
        public readonly int $yearlyPoints,
    ) {}

    public function via($notifiable): array
    {
        return app()->environment('testing')
            ? ['database']
            : ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Поздравляем! Новый уровень: {$this->newLevelName}")
            ->greeting("Здравствуйте, {$notifiable->fio}!")
            ->line("Вы достигли нового уровня: **{$this->newLevelName}**")
            ->line("Годовой баланс баллов: **{$this->yearlyPoints}**")
            ->line('Так держать!')
            ->action('Посмотреть привилегии', url('/levels'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'type'           => 'level_changed',
            'new_level_name' => $this->newLevelName,
            'yearly_points'  => $this->yearlyPoints,
        ];
    }
}