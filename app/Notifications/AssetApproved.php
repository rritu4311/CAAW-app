<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\Approval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetApproved extends Notification
{
    use Queueable;

    public $asset;
    public $approval;
    public $approver;

    /**
     * Create a new notification instance.
     */
    public function __construct(Asset $asset, Approval $approval, $approver)
    {
        $this->asset = $asset;
        $this->approval = $approval;
        $this->approver = $approver;
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
            ->subject($this->approver->name . ' approved your asset')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your asset has been approved.')
            ->line('**Asset Name:** ' . $this->asset->name)
            ->line('**Approved by:** ' . $this->approver->name);
        
        if ($this->approval->decision_reason) {
            $message->line('**Approver Comment:** ' . $this->approval->decision_reason);
        }
        
        $message->action('View Asset', $assetUrl)
            ->line('Thank you for using our application!');
        
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
            'approver_name' => $this->approver->name,
            'approver_comment' => $this->approval->decision_reason,
            'message' => $this->approver->name . ' approved your asset: ' . $this->asset->name,
            'type' => 'asset_approved',
        ];
    }
}
