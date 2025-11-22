<?php

namespace App\Notifications;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormSubmissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected FormSubmission $submission
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $page = $this->submission->page;
        
        return (new MailMessage)
            ->subject('New Form Submission on ' . $page->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new form submission on your page "' . $page->title . '".')
            ->line('Submitted at: ' . $this->submission->created_at->format('M d, Y H:i'))
            ->action('View Submission', url('/pages/' . $page->id . '/submissions'))
            ->line('Thank you for using PageBuilder!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'page_id' => $this->submission->page_id,
            'page_title' => $this->submission->page->title,
            'form_id' => $this->submission->form_id,
            'submitted_at' => $this->submission->created_at->toISOString(),
        ];
    }
}
