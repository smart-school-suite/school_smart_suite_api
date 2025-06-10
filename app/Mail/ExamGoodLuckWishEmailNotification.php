<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
class ExamGoodLuckWishEmailNotification extends Mailable
{
    use Queueable, SerializesModels;

     public $recipientName;
    public $examTitle;
    public $examDateTime;
    public $recipientType;

    /**
     * Create a new message instance.
     */
    public function __construct($recipientName, $examTitle, Carbon $examDateTime)
    {
        $this->recipientName = $recipientName;
        $this->examTitle = $examTitle;
        $this->examDateTime = $examDateTime;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
         return new Envelope(
            subject: "'Good Luck for Your Upcoming Exam: $this->examTitle'!'",
         );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ExamGoodLuckWishEmail',
            with: [
                'name' => $this->recipientName,
                'examTitle' => $this->examTitle,
                'examDateTime' => $this->examDateTime->format('F j, Y, h:i A'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
