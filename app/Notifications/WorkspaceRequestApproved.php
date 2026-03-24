<?php

namespace App\Notifications;

use App\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceRequestApproved extends Notification
{
    use Queueable;

    public $workspace;

    /**
     * Create a new notification instance.
     */
    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
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
        return (new MailMessage)
            ->subject('Workspace Request Approved!')
            ->greeting('Great news ' . $notifiable->name . '!')
            ->line('Your workspace request has been approved.')
            ->line('Workspace Name: **' . $this->workspace->name . '**')
            ->action('View Workspace', route('workspaces.show', $this->workspace))
            ->line('You can now start using your workspace.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'workspace_id' => $this->workspace->id,
            'workspace_name' => $this->workspace->name,
            'message' => 'Your workspace request "' . $this->workspace->name . '" has been approved!',
            'type' => 'workspace_request_approved',
        ];
    }
}
