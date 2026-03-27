<?php

namespace App\Notifications;

use App\Models\WorkspaceUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceRequestPending extends Notification
{
    use Queueable;

    public $workspaceUser;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(WorkspaceUser $workspaceUser, User $user)
    {
        $this->workspaceUser = $workspaceUser;
        $this->user = $user;
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
            ->subject('New Workspace Access Request')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('{$this->user->name} has requested access to your workspace: **' . $this->workspaceUser->workspace->name . '**')
            ->line('Please review and approve or reject this request.')
            ->action('View Notifications', route('notifications.index'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'workspace_user_id' => $this->workspaceUser->id,
            'workspace_id' => $this->workspaceUser->workspace->id,
            'workspace_name' => $this->workspaceUser->workspace->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'role' => $this->workspaceUser->role,
            'message' => '{$this->user->name} has requested access to workspace: ' . $this->workspaceUser->workspace->name,
            'type' => 'workspace_request',
        ];
    }
}
