<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetReadyForReview extends Notification
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
            ->subject('Your asset is ready for review')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('An asset has been routed to you for review.')
            ->line('**Asset Name:** ' . $this->asset->name)
            ->line('**Project:** ' . $this->asset->project->name)
            ->line('**Uploaded by:** ' . $this->asset->uploadedBy->name)
            ->action('Review Asset', $assetUrl)
            ->line('Please review this asset at your earliest convenience.');
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
            'uploaded_by' => $this->asset->uploadedBy->name,
            'message' => $this->asset->uploadedBy->name . "'s asset is ready for review: " . $this->asset->name,
            'type' => 'asset_ready_for_review',
        ];
    }
}
