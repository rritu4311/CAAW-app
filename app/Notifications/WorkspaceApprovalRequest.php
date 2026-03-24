<?php

namespace App\Notifications;

use App\Models\WorkspaceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceApprovalRequest extends Notification
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
            ->subject('New Workspace Approval Request')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new workspace approval request has been submitted.')
            ->line('Workspace Name: **' . $this->workspaceRequest->name . '**')
            ->line('Requested by: ' . $this->workspaceRequest->requester->name)
            ->action('Review Request', route('notifications.index'))
            ->line('Please review and approve or reject this workspace request.');
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
            'requester_name' => $this->workspaceRequest->requester->name,
            'requester_id' => $this->workspaceRequest->requested_by,
            'message' => 'New workspace approval request: ' . $this->workspaceRequest->name . ' by ' . $this->workspaceRequest->requester->name,
            'type' => 'workspace_approval_request',
        ];
    }
}
