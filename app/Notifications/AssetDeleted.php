<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetDeleted extends Notification
{
    use Queueable;

    public $assetName;
    public $assetType;
    public $projectName;
    public $folderName;
    public $deletedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $assetName, string $assetType, ?string $projectName, ?string $folderName, $deletedBy)
    {
        $this->assetName = $assetName;
        $this->assetType = $assetType;
        $this->projectName = $projectName;
        $this->folderName = $folderName;
        $this->deletedBy = $deletedBy;
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
        $message = (new MailMessage)
            ->subject($this->deletedBy->name . ' deleted an asset')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('An asset has been deleted from a project you are part of.')
            ->line('**Asset Name:** ' . $this->assetName)
            ->line('**File Type:** ' . $this->assetType);
        
        if ($this->projectName) {
            $message->line('**Project:** ' . $this->projectName);
        }
        
        if ($this->folderName) {
            $message->line('**Folder:** ' . $this->folderName);
        }
        
        $message->line('**Deleted by:** ' . $this->deletedBy->name)
            ->line('This asset is no longer available in the system.');
        
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
            'asset_name' => $this->assetName,
            'asset_type' => $this->assetType,
            'project_name' => $this->projectName,
            'folder_name' => $this->folderName,
            'deleted_by_id' => $this->deletedBy->id,
            'deleted_by_name' => $this->deletedBy->name,
            'message' => $this->deletedBy->name . ' deleted the asset: ' . $this->assetName,
            'type' => 'asset_deleted',
        ];
    }
}
