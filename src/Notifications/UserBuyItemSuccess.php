<?php

namespace Hanoivip\Game\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserBuyItemSuccess extends Notification implements ShouldQueue
{
    use Queueable;
    
    private $server;
    private $item;
    private $role;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($server, $item, $role)
    {
        $this->server=$server;
        $this->item=$item;
        $this->role=$role;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
            'server'=>$this->server,
            'item'=>$this->item,
            'role'=>$this->role
        ];
    }
}
