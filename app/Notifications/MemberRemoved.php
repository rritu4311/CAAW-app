<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberRemoved extends Notification
{
    use Queueable;

    public $contextName;
    public $contextType;
    public $removedUserName;
    public $removedBy;

    /**
     * Create a new notification instance.
     * 
     * @param string $contextName Name of the workspace or project
     * @param string $contextType 'workspace' or 'project'
     * @param string $removedUserName Name of the user who was removed
     * @param object $removedBy User who performed the removal
     */
    public function __construct(string $contextName, string $contextType, string $removedUserName, $removedBy)
    {
        $this->contextName = $contextName;
        $this->contextType = $contextType;
        $this->removedUserName = $removedUserName;
        $this->removedBy = $removedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $contextLabel = $this->contextType === 'workspace' ? 'workspace' : 'project';
        
        return (new MailMessage)
            ->subject('Member removed from ' . $this->contextName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A member has been removed from the ' . $contextLabel . ' you are part of.')
            ->line('**' . ucfirst($contextLabel) . ':** ' . $this->contextName)
            ->line('**Removed Member:** ' . $this->removedUserName)
            ->line('**Removed by:** ' . $this->removedBy->name)
            ->line('This member no longer has access to the ' . $contextLabel . '.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'context_name' => $this->contextName,
            'context_type' => $this->contextType,
            'removed_user_name' => $this->removedUserName,
            'removed_by_id' => $this->removedBy->id,
            'removed_by_name' => $this->removedBy->name,
            'message' => $this->removedUserName . ' was removed from ' . $this->contextName . ' by ' . $this->removedBy->name,
            'type' => 'member_removed',
        ];
    }
}
