<?php

declare(strict_types=1);

namespace FuelPoints\User\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Уведомление ТМ: результаты подтверждены координатором.
 */
final class ResultConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $monthlyResultId,
        public readonly string $period,
        public readonly int $yearlyPoints,
        public readonly string $levelName,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return app()->environment('testing')
            ? ['database']
            : ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return new MailMessage()
            ->subject(subject: "Результаты за {$this->period} подтверждены")
            ->greeting(greeting: "Здравствуйте, {$notifiable->fio}!")
            ->line(line: "Ваши результаты за период {$this->period} были подтверждены координатором.")
            ->line(line: "Годовой баланс баллов: **{$this->yearlyPoints}**")
            ->line(line: "Текущий уровень: **{$this->levelName}**")
            ->action(text: 'Посмотреть детали', url: url(path: '/dashboard'))
            ->line(line: 'Спасибо за работу!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'type'              => 'result_confirmed',
            'monthly_result_id' => $this->monthlyResultId,
            'period'            => $this->period,
            'yearly_points'     => $this->yearlyPoints,
            'level_name'        => $this->levelName,
        ];
    }
}
