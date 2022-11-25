<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class APIWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
            ->subject('Welcome to the Cartes.io API')
            ->greeting('Hey there!')
            ->line('You have successfully created your first access token for the Cartes.io API.')
            ->line('You can now use this token to access the API and create, update and delete maps and markers. When you create resources using the token, they will be associated with your account.')
            // Warning about the token expiration and sensitive data
            ->line('Please note that access tokens are sensitive and should be kept secret.')
            ->line('For your security, we automatically expire access tokens after 1 year.')
            // Read the docs
            ->action('Read the API docs', 'https://github.com/M-Media-Group/Cartes.io/wiki/API')
            ->line('Thank you for using our Cartes.io!');
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
