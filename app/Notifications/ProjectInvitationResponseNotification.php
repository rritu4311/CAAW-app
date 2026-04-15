<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectInvitationResponseNotification extends Notification
{
    use Queueable;

    public $project;
    public $user;
    public $status;
    public $inviter;

    /**
     * Create a new notification instance.
     *
     * @param Project $project
     * @param User $user
     * @param string $status
     * @param User|null $inviter
     */
    public function __construct(Project $project, User $user, string $status, ?User $inviter = null)
    {
        $this->project = $project;
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
        $subject = "Project Invitation {$statusText}";
        
        $greeting = 'Hello ' . $notifiable->name . '!';
        
        $line = "**{$this->user->name}** has {$statusText} your invitation to join the project: **{$this->project->name}**";
        
        if ($this->status === 'accepted') {
            $actionText = 'View Project';
            $actionUrl = route('projects.share', $this->project);
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
        $message = "{$this->user->name} has {$statusText} your invitation to join project: {$this->project->name}";
        
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'inviter_id' => $this->inviter?->id,
            'status' => $this->status,
            'message' => $message,
            'created_at' => now()->toIso8601String(),
            'type' => 'project_invitation_response',
        ];
    }
}
