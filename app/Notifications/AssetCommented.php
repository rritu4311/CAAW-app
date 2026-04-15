<?php

namespace App\Notifications;

use App\Models\Asset;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssetCommented extends Notification
{
    use Queueable;

    public $asset;
    public $comment;
    public $commenter;

    /**
     * Create a new notification instance.
     */
    public function __construct(Asset $asset, Comment $comment, $commenter)
    {
        $this->asset = $asset;
        $this->comment = $comment;
        $this->commenter = $commenter;
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
            ->subject($this->commenter->name . ' commented on your asset')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new comment has been added to your asset.')
            ->line('**Asset Name:** ' . $this->asset->name)
            ->line('**Commented by:** ' . $this->commenter->name);
        
        if ($this->comment->text) {
            $message->line('**Comment:** ' . $this->comment->text);
        }
        
        // Check if it's an annotation comment
        if ($this->comment->annotation_id) {
            $message->line('**Location:** On an annotated area of the asset');
        }
        
        $message->action('View Comment', $assetUrl)
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
            'comment_id' => $this->comment->id,
            'commenter_name' => $this->commenter->name,
            'comment_text' => $this->comment->text,
            'is_annotation_comment' => $this->comment->annotation_id !== null,
            'message' => $this->commenter->name . ' commented on your asset: ' . $this->asset->name,
            'type' => 'asset_commented',
        ];
    }
}
