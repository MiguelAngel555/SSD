<?php

namespace App\Notifications;

use App\Models\Secuencia;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecuenciaCambioEstadoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Secuencia $secuencia,
        private string $estadoAnterior,
    ) {}

    public function via($notifiable): array
    {
        return [FcmChannel::class, 'mail'];
    }

    /**
     * Correo de cambio de estado. Si el destinatario es el director de la
     * carrera (por ejemplo cuando la secuencia entra a "en_proceso_validacion"),
     * se usa una redacción con llamado a validar en vez del mensaje genérico
     * para docentes/autores.
     */
    public function toMail($notifiable): MailMessage
    {
        $asignatura = $this->secuencia->asignatura->nombre ?? 'la secuencia';
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        $mensaje = (new MailMessage)
            ->subject('Cambio de estado en secuencia didáctica')
            ->greeting("¡Hola, {$notifiable->nombre}!");

        if ($this->esDirector($notifiable)) {
            $mensaje->line(sprintf(
                'La secuencia didáctica de **%s** pasó a estado "%s" y ya está lista para tu validación.',
                $asignatura,
                $this->estadoLegible($this->secuencia->estado),
            ))->action('Ir a mis secuencias', "{$frontendUrl}/director/secuencias");
        } else {
            $mensaje->line(sprintf(
                'Tu secuencia de **%s** pasó de "%s" a "%s".',
                $asignatura,
                $this->estadoLegible($this->estadoAnterior),
                $this->estadoLegible($this->secuencia->estado),
            ))->action('Ver secuencia', "{$frontendUrl}/secuencias/{$this->secuencia->id}");
        }

        return $mensaje;
    }

    private function esDirector($notifiable): bool
    {
        return $this->secuencia->carrera
            && $this->secuencia->carrera->director_id === $notifiable->id;
    }

    /**
     * Payload que consume FcmChannel. Se mantiene simple: título/cuerpo para
     * la notificación del sistema y "data" para que la app del reloj pueda
     * navegar directo a la secuencia si el usuario toca la notificación.
     */
    public function toFcm($notifiable): array
    {
        $asignatura = $this->secuencia->asignatura->nombre ?? 'tu secuencia';

        return [
            'notification' => [
                'title' => 'Cambio de estado',
                'body' => sprintf(
                    '%s pasó de "%s" a "%s".',
                    $asignatura,
                    $this->estadoLegible($this->estadoAnterior),
                    $this->estadoLegible($this->secuencia->estado),
                ),
            ],
            'data' => [
                'secuencia_id' => (string) $this->secuencia->id,
                'estado_anterior' => $this->estadoAnterior,
                'estado_nuevo' => $this->secuencia->estado,
            ],
        ];
    }

    private function estadoLegible(string $estado): string
    {
        return str_replace('_', ' ', $estado);
    }
}
