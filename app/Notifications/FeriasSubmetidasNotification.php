<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ferias;

class FeriasSubmetidasNotification extends Notification
{
    use Queueable;

    protected $ferias;

    public function __construct(Ferias $ferias)
    {
        $this->ferias = $ferias;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Novo Pedido de Férias Submetido')
            ->greeting('Olá ' . $notifiable->primeiro_nome . ',')
            ->line('O colaborador ' . $this->ferias->user->primeiro_nome . ' submeteu um pedido de férias.')
            ->line('Período: ' . $this->ferias->data_inicio->format('d/m/Y') . ' a ' . $this->ferias->data_fim->format('d/m/Y'))
            ->action('Ver Pedido de Férias', url('/admin/ferias/' . $this->ferias->id))
            ->line('Por favor, reveja este pedido no sistema.');
    }
}
