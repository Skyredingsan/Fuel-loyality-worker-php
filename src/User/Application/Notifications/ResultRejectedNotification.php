<?php

declare(strict_types=1);

namespace FuelPoints\User\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Уведомление ТМ и эксперту: результаты отклонены с причиной.
 */
final class ResultRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $period,
        public readonly string $reason,
        public readonly string $tmFio,
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
            ->subject("Результаты за {$this->period} отклонены")
            ->greeting("Здравствуйте, {$notifiable->fio}!")
            ->line("Результаты для ТМ **{$this->tmFio}** за период {$this->period} были отклонены.")
            ->line("**Причина:**")
            ->line($this->reason)
            ->action('Перейти к вводу результатов', url('/enter-results'))
            ->line('Пожалуйста, скорректируйте данные и отправьте заново.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'type'   => 'result_rejected',
            'period' => $this->period,
            'reason' => $this->reason,
            'tm_fio' => $this->tmFio,
        ];
    }
}