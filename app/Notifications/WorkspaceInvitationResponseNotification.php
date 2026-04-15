<?php

namespace App\Notifications;

use App\Models\Workspace;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationResponseNotification extends Notification
{
    use Queueable;

    public $workspace;
    public $user;
    public $status;
    public $inviter;

    /**
     * Create a new notification instance.
     *
     * @param Workspace $workspace
     * @param User $user
     * @param string $status
     * @param User|null $inviter
     */
    public function __construct(Workspace $workspace, User $user, string $status, ?User $inviter = null)
    {
        $this->workspace = $workspace;
        $this->user = $user;
        $this->status = $status;
        $this->inviter = $inviter;
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
        $statusText = ucfirst($this->status);
        $subject = "Workspace Invitation {$statusText}";
        
        $greeting = 'Hello ' . $notifiable->name . '!';
        
        $line = "**{$this->user->name}** has {$statusText} your invitation to join the workspace: **{$this->workspace->name}**";
        
        if ($this->status === 'accepted') {
            $actionText = 'View Workspace';
            $actionUrl = route('workspaces.share', $this->workspace);
        } else {
            $actionText = 'View Notifications';
            $actionUrl = route('notifications.index');
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line)
            ->action($actionText, $actionUrl)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusText = ucfirst($this->status);
        $message = "{$this->user->name} has {$statusText} your invitation to join workspace: {$this->workspace->name}";
        
        return [
            'workspace_id' => $this->workspace->id,
            'workspace_name' => $this->workspace->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'inviter_id' => $this->inviter?->id,
            'status' => $this->status,
            'message' => $message,
            'created_at' => now()->toIso8601String(),
            'type' => 'workspace_invitation_response',
        ];
    }
}
