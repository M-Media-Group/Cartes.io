<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MapsSummary extends Notification
{
    use Queueable;

    private $maps;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($maps)
    {
        $this->maps = $maps;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Your maps with new markers')
            ->greeting('Hey!')
            ->line('The following maps owned by you saw new markers created by other people in the last week:')
            ->line($this->maps->map(function ($map) {
                return $map->title ?? 'Untitled map';
            })->implode(' - '))
            ->action('View maps', config('app.spa_url') . '/users/' . $notifiable->username)
            ->line('Thank you for using Cartes.io!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
