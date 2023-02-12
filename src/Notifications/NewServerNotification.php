<?php

namespace Hanoivip\Game\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewServerNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    private $name;
    private $title;
    private $createdAt;

    public function __construct($name, $title, $createdAt)
    {
        $this->name = $name;
        $this->title = $title;
        $this->createdAt = $createdAt;
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
            'name'=>$this->name,
            'title'=>$this->title,
            'createdAt'=>$this->createdAt
        ];
    }
}
