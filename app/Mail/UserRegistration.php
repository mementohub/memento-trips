<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * UserRegistration Mailable
 *
 * Sent after a new user registers. Contains a verification link
 * for email confirmation. The view receives `$mail_message` (HTML
 * body with verification link) and `$from_user` (the new user).
 *
 * @package App\Mail
 */
class UserRegistration extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string  Email subject line */
    public $mail_subject;

    /** @var string  HTML email body with verification link */
    public $mail_message;

    /** @var mixed  Newly registered user */
    public $from_user;

    /**
     * Create a new message instance.
     *
     * @param  string  $mail_message  Email body with verification link
     * @param  string  $mail_subject  Email subject line
     * @param  mixed   $from_user     Newly registered user
     */
    public function __construct(string $mail_message, string $mail_subject, $from_user)
    {
        $this->mail_subject = $mail_subject;
        $this->mail_message = $mail_message;
        $this->from_user = $from_user;
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
            view: 'auth.email_verify_mail',
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