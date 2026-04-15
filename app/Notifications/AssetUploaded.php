<?php

namespace App\Notifications;

use App\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetUploaded extends Notification
{
    use Queueable;

    public $asset;
    public $uploader;

    /**
     * Create a new notification instance.
     */
    public function __construct(Asset $asset, $uploader)
    {
        $this->asset = $asset;
        $this->uploader = $uploader;
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
            ->subject($this->uploader->name . ' uploaded a new asset')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new asset has been uploaded to a project you are part of.')
            ->line('**Asset Name:** ' . $this->asset->name)
            ->line('**File Type:** ' . $this->asset->file_type)
            ->line('**File Size:** ' . $this->asset->formatted_size)
            ->line('**Project:** ' . ($this->asset->project->name ?? 'Unknown'))
            ->line('**Uploaded by:** ' . $this->uploader->name)
            ->action('View Asset', $assetUrl)
            ->line('You are receiving this notification because you are a member of the project or workspace.');
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
            'asset_type' => $this->asset->file_type,
            'asset_size' => $this->asset->file_size,
            'project_id' => $this->asset->project_id,
            'project_name' => $this->asset->project->name ?? null,
            'folder_id' => $this->asset->folder_id,
            'folder_name' => $this->asset->folder->name ?? null,
            'uploader_id' => $this->uploader->id,
            'uploader_name' => $this->uploader->name,
            'message' => $this->uploader->name . ' uploaded a new asset: ' . $this->asset->name,
            'type' => 'asset_uploaded',
        ];
    }
}
