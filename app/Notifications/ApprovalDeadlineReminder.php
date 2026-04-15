<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalDeadlineReminder extends Notification
{
    use Queueable;

    public $asset;
    public $approval;

    /**
     * Create a new notification instance.
     */
    public function __construct(Asset $asset, Approval $approval)
    {
        $this->asset = $asset;
        $this->approval = $approval;
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
        $assetUrl = route('assets.show', $this->asset->id);
        
        return (new MailMessage)
            ->subject('Approval deadline in 24 hours')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder that you have an asset approval deadline approaching.')
            ->line('**Asset Name:** ' . $this->asset->name)
            ->line('**Project:** ' . $this->asset->project->name)
            ->line('**Deadline:** Within 24 hours')
            ->action('Review Asset Now', $assetUrl)
            ->line('Please complete your review before the deadline to avoid delays.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'asset_id' => $this->asset->id,
            'asset_name' => $this->asset->name,
            'project_id' => $this->asset->project->id,
            'project_name' => $this->asset->project->name,
            'approval_id' => $this->approval->id,
            'message' => 'Approval deadline in 24 hours for: ' . $this->asset->name,
            'type' => 'approval_deadline_reminder',
        ];
    }
}
