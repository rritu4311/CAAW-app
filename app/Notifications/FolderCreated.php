<?php

namespace App\Notifications;

use App\Models\Folder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FolderCreated extends Notification
{
    use Queueable;

    public $folder;
    public $createdBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Folder $folder, $createdBy)
    {
        $this->folder = $folder;
        $this->createdBy = $createdBy;
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
        $folderUrl = $this->folder->parent_folder_id 
            ? route('folders.show', $this->folder->id)
            : route('projects.show', $this->folder->project_id);
        
        return (new MailMessage)
            ->subject($this->createdBy->name . ' created a new folder')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new folder has been created in a project you are part of.')
            ->line('**Folder Name:** ' . $this->folder->name)
            ->line('**Project:** ' . ($this->folder->project->name ?? 'Unknown'))
            ->line('**Created by:** ' . $this->createdBy->name)
            ->action('View Folder', $folderUrl)
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
            'folder_id' => $this->folder->id,
            'folder_name' => $this->folder->name,
            'project_id' => $this->folder->project_id,
            'project_name' => $this->folder->project->name ?? null,
            'parent_folder_id' => $this->folder->parent_folder_id,
            'parent_folder_name' => $this->folder->parent->name ?? null,
            'created_by_id' => $this->createdBy->id,
            'created_by_name' => $this->createdBy->name,
            'message' => $this->createdBy->name . ' created a new folder: ' . $this->folder->name,
            'type' => 'folder_created',
        ];
    }
}
