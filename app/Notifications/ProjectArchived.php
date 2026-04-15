<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectArchived extends Notification
{
    use Queueable;

    public $project;
    public $archivedBy;
    public $isArchived;

    /**
     * Create a new notification instance.
     * 
     * @param Project $project
     * @param object $archivedBy User who archived/unarchived
     * @param bool $isArchived true if archived, false if unarchived
     */
    public function __construct(Project $project, $archivedBy, bool $isArchived = true)
    {
        $this->project = $project;
        $this->archivedBy = $archivedBy;
        $this->isArchived = $isArchived;
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
        $action = $this->isArchived ? 'archived' : 'unarchived';
        $subject = $this->archivedBy->name . ' ' . $action . ' a project';
        
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A project you are part of has been ' . $action . '.')
            ->line('**Project Name:** ' . $this->project->name);
        
        if ($this->project->workspace) {
            $message->line('**Workspace:** ' . $this->project->workspace->name);
        }
        
        $message->line('**' . ucfirst($action) . ' by:** ' . $this->archivedBy->name);
        
        if ($this->isArchived) {
            $message->line('This project has been archived and is now in read-only mode.');
        } else {
            $message->line('This project has been restored to active status.');
        }
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $action = $this->isArchived ? 'archived' : 'unarchived';
        
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'workspace_id' => $this->project->workspace_id,
            'workspace_name' => $this->project->workspace->name ?? null,
            'archived_by_id' => $this->archivedBy->id,
            'archived_by_name' => $this->archivedBy->name,
            'is_archived' => $this->isArchived,
            'message' => $this->project->name . ' has been ' . $action . ' by ' . $this->archivedBy->name,
            'type' => $this->isArchived ? 'project_archived' : 'project_unarchived',
        ];
    }
}
