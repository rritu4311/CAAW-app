<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FolderDeleted extends Notification
{
    use Queueable;

    public $folderName;
    public $projectName;
    public $parentFolderName;
    public $deletedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $folderName, ?string $projectName, ?string $parentFolderName, $deletedBy)
    {
        $this->folderName = $folderName;
        $this->projectName = $projectName;
        $this->parentFolderName = $parentFolderName;
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
            ->subject($this->deletedBy->name . ' deleted a folder')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A folder has been deleted from a project you are part of.')
            ->line('**Folder Name:** ' . $this->folderName);
        
        if ($this->projectName) {
            $message->line('**Project:** ' . $this->projectName);
        }
        
        if ($this->parentFolderName) {
            $message->line('**Parent Folder:** ' . $this->parentFolderName);
        }
        
        $message->line('**Deleted by:** ' . $this->deletedBy->name)
            ->line('This folder and all its contents have been removed.');
        
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
            'folder_name' => $this->folderName,
            'project_name' => $this->projectName,
            'parent_folder_name' => $this->parentFolderName,
            'deleted_by_id' => $this->deletedBy->id,
            'deleted_by_name' => $this->deletedBy->name,
            'message' => $this->deletedBy->name . ' deleted the folder: ' . $this->folderName,
            'type' => 'folder_deleted',
        ];
    }
}
