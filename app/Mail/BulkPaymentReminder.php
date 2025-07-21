<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class BulkPaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $jenisKasNama; // Tambahkan properti ini jika perlu

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $jenisKasNama) // Tambahkan parameter yang dibutuhkan
    {
        $this->user = $user;
        $this->jenisKasNama = $jenisKasNama;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Pembayaran Iuran Bulanan (Penting)', // Subject yang berbeda
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bulk_payment_reminder', // View email yang berbeda
            with: [
                'name' => $this->user->name,
                'jenisKasNama' => $this->jenisKasNama, // Kirim data ke view
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
