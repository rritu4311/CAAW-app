<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\AssetVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVersionUploaded extends Notification
{
    use Queueable;

    public $asset;
    public $version;
    public $uploadedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Asset $asset, AssetVersion $version, $uploadedBy)
    {
        $this->asset = $asset;
        $this->version = $version;
        $this->uploadedBy = $uploadedBy;
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
            ->subject($this->uploadedBy->name . ' uploaded a new version of ' . $this->asset->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new version has been uploaded for an asset you are following.')
            ->line('**Asset Name:** ' . $this->asset->name)
            ->line('**New Version:** ' . $this->version->formatted_version)
            ->line('**File Size:** ' . $this->version->formatted_size)
            ->line('**Project:** ' . ($this->asset->project->name ?? 'Unknown'))
            ->line('**Uploaded by:** ' . $this->uploadedBy->name)
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
            'version_id' => $this->version->id,
            'version_number' => $this->version->version_number,
            'version_formatted' => $this->version->formatted_version,
            'file_size' => $this->version->file_size,
            'project_id' => $this->asset->project_id,
            'project_name' => $this->asset->project->name ?? null,
            'folder_id' => $this->asset->folder_id,
            'folder_name' => $this->asset->folder->name ?? null,
            'uploaded_by_id' => $this->uploadedBy->id,
            'uploaded_by_name' => $this->uploadedBy->name,
            'message' => $this->uploadedBy->name . ' uploaded version ' . $this->version->formatted_version . ' of ' . $this->asset->name,
            'type' => 'new_version_uploaded',
        ];
    }
}
