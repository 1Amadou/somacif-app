<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment; // <-- AJOUT 1 : Importer la classe Attachment
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $replyMessage;
    public ?string $attachmentPath; // <-- AJOUT 2 : Propriété pour le chemin du fichier

    // --- CORRECTION : On accepte un chemin de fichier optionnel ---
    public function __construct(string $replyMessage, ?string $attachmentPath = null)
    {
        $this->replyMessage = $replyMessage;
        $this->attachmentPath = $attachmentPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: Votre message à SOMACIF',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.reply',
        );
    }

    // --- AMÉLIORATION : On ajoute la méthode pour les pièces jointes ---
    public function attachments(): array
    {
        if ($this->attachmentPath) {
            return [
                Attachment::fromPath($this->attachmentPath),
            ];
        }
        return [];
    }
}