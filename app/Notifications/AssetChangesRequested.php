<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetChangesRequested extends Notification
{
    use Queueable;

    public $asset;
    public $approval;
    public $requester;

    /**
     * Create a new notification instance.
     */
    public function __construct(Asset $asset, Approval $approval, $requester)
    {
        $this->asset = $asset;
        $this->approval = $approval;
        $this->requester = $requester;
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
        
        $message = (new MailMessage)
            ->subject($this->requester->name . ' requested changes')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Changes have been requested for your asset.')
            ->line('**Asset Name:** ' . $this->asset->name)
            ->line('**Requested by:** ' . $this->requester->name);
        
        if ($this->approval->decision_reason) {
            $message->line('**Reason:** ' . $this->approval->decision_reason);
        }
        
        // Check if there are annotations
        if ($this->asset->annotations()->count() > 0) {
            $message->line('**Annotations:** The asset has been annotated with specific feedback.');
        }
        
        $message->action('View Annotated Asset', $assetUrl)
            ->line('Please review the requested changes and update your asset.');
        
        return $message;
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
            'requester_name' => $this->requester->name,
            'reason' => $this->approval->decision_reason,
            'has_annotations' => $this->asset->annotations()->count() > 0,
            'message' => $this->requester->name . ' requested changes for: ' . $this->asset->name,
            'type' => 'asset_changes_requested',
        ];
    }
}
