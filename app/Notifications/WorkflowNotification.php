<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class WorkflowNotification extends Notification
{
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
        public readonly string $category = 'info',
    ) {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, string> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'category' => $this->category,
        ];
    }

    /** @return array<string, string> */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
