<?php

namespace App\Notifications;

use App\Models\WorkspaceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceRequestRejected extends Notification
{
    use Queueable;

    public $workspaceRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(WorkspaceRequest $workspaceRequest)
    {
        $this->workspaceRequest = $workspaceRequest;
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
            ->subject('Workspace Request Rejected')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your workspace request has been rejected.')
            ->line('Workspace Name: **' . $this->workspaceRequest->name . '**')
            ->line('Reason: ' . ($this->workspaceRequest->decision_reason ?: 'No reason provided'))
            ->line('If you have questions, please contact the administrator.')
            ->line('Thank you for your understanding.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'workspace_request_id' => $this->workspaceRequest->id,
            'workspace_name' => $this->workspaceRequest->name,
            'message' => 'Your workspace request "' . $this->workspaceRequest->name . '" has been rejected.',
            'reason' => $this->workspaceRequest->decision_reason,
            'type' => 'workspace_request_rejected',
        ];
    }
}
