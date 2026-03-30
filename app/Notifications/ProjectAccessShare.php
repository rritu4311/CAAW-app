<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectAccessShare extends Notification
{
    use Queueable;

    public $project;
    public $inviter;
    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, $inviter = null, $status = 'approved')
    {
        $this->project = $project;
        $this->inviter = $inviter;
        $this->status = $status;
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
        $subject = 'Project Invitation Sent - Awaiting Approval';
            
        $greeting = 'Hello ' . $notifiable->name . '!';
        

            $message = 'An invitation has been sent for you to join the project: **' . $this->project->name . '**';
            $message .= '\n\nThe invitation is currently awaiting approval from the project owner.';
            $message .= '\n\nYou will be notified once the approval is processed.';
            $actionText = 'View Notifications';
            $actionUrl = route('notifications.index');
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($message)
            ->line($this->inviter ? 'Invited by: ' . $this->inviter->name : '')
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
        $message = 'An invitation has been sent for you to join the project: ' . $this->project->name . ' (Awaiting approval)';
        
            
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'inviter_name' => $this->inviter?->name,
            'status' => $this->status,
            'message' => $message,
            'type' => $this->status === 'pending' ? 'project_invitation_pending' : 'project_invitation',
        ];
    }
}
