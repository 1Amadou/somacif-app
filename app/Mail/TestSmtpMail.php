<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class TestSmtpMail extends Mailable
{
    use Queueable, SerializesModels;
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Test de Configuration SMTP - SOMACIF');
    }
    public function content(): Content
    {
        return new Content(markdown: 'emails.test-smtp');
    }
}