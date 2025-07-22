<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Carbon\Carbon; // Tambahkan Carbon

class BulkPaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $jenisKasNama;
    public $detailBulan; // Properti baru untuk info bulan dan tahun

    /**
     * Create a new message instance.
     */
    // Perbarui constructor untuk menerima bulan dan tahun
    public function __construct(User $user, string $jenisKasNama, int $bulan, int $tahun)
    {
        $this->user = $user;
        $this->jenisKasNama = $jenisKasNama;
        // Format bulan dan tahun agar lebih mudah dibaca di email
        $this->detailBulan = Carbon::create($tahun, $bulan)->translatedFormat('F Y');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // Subject bisa dibuat lebih dinamis
            subject: "Penting: Pengingat Pembayaran Iuran {$this->jenisKasNama} untuk {$this->detailBulan}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bulk_payment_reminder',
            with: [
                'name' => $this->user->name,
                'jenisKasNama' => $this->jenisKasNama,
                'detailBulan' => $this->detailBulan, // Kirim data bulan ke view
            ],
        );
    }
    // ...
}
