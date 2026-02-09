<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * UserForgetPassword Mailable
 *
 * Sent when a user requests a password reset. Contains a reset link
 * and token. The view receives `$mail_message` (HTML body with reset
 * link) and `$from_user` (the requesting user's details).
 *
 * @package App\Mail
 */
class UserForgetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string  Email subject line */
    public $mail_subject;

    /** @var string  HTML email body with reset link */
    public $mail_message;

    /** @var mixed  User who requested the reset */
    public $from_user;

    /**
     * Create a new message instance.
     *
     * @param  string  $mail_message  Email body content
     * @param  string  $mail_subject  Email subject line
     * @param  mixed   $from_user     User requesting the reset
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
            view: 'auth.forget_password_mail',
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