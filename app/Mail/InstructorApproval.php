<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * InstructorApproval Mailable
 *
 * Sent to an agency/instructor when their joining request is approved
 * or rejected by an admin. Uses a configurable subject and message
 * body passed from the admin panel.
 *
 * @package App\Mail
 */
class InstructorApproval extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string  Email subject line */
    public $mail_subject;

    /** @var string  HTML email body content */
    public $mail_message;

    /**
     * Create a new message instance.
     *
     * @param  string  $mail_message  Email body content
     * @param  string  $mail_subject  Email subject line
     */
    public function __construct(string $mail_message, string $mail_subject)
    {
        $this->mail_subject = $mail_subject;
        $this->mail_message = $mail_message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mail_subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admin.seller.approval_mail',
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