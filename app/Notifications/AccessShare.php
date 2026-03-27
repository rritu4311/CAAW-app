<?php

namespace App\Notifications;

use App\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessShare extends Notification
{
    use Queueable;

    public $workspace;
    public $inviter;
    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(Workspace $workspace, $inviter = null, $status = 'approved')
    {
        $this->workspace = $workspace;
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
        $subject = 'Workspace Invitation Sent - Awaiting Approval';
            
        $greeting = 'Hello ' . $notifiable->name . '!';
        

            $message = 'An invitation has been sent for you to join the workspace: **' . $this->workspace->name . '**';
            $message .= '\n\nThe invitation is currently awaiting approval from the workspace owner.';
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
        $message = 'An invitation has been sent for you to join the workspace: ' . $this->workspace->name . ' (Awaiting approval)';
        
            
        return [
            'workspace_id' => $this->workspace->id,
            'workspace_name' => $this->workspace->name,
            'inviter_name' => $this->inviter?->name,
            'status' => $this->status,
            'message' => $message,
            'type' => $this->status === 'pending' ? 'workspace_invitation_pending' : 'workspace_invitation',
        ];
    }
}
