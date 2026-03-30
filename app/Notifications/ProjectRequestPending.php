<?php

namespace App\Notifications;

use App\Models\ProjectCollaborator;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectRequestPending extends Notification
{
    use Queueable;

    public $projectCollaborator;
    public $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjectCollaborator $projectCollaborator, User $user)
    {
        $this->projectCollaborator = $projectCollaborator;
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
            ->subject('New Project Access Request')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('{$this->user->name} has requested access to your project: **' . $this->projectCollaborator->project->name . '**')
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
            'project_collaborator_id' => $this->projectCollaborator->id,
            'project_id' => $this->projectCollaborator->project->id,
            'project_name' => $this->projectCollaborator->project->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
            'role' => $this->projectCollaborator->role,
            'message' => '{$this->user->name} has requested access to project: ' . $this->projectCollaborator->project->name,
            'type' => 'project_request',
        ];
    }
}
