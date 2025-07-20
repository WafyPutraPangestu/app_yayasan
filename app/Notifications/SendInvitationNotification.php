<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class SendInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Membuat link undangan yang aman dan berlaku selama 24 jam
        $invitationUrl = URL::temporarySignedRoute(
            'invitation.accept',
            now()->addHours(24),
            ['user' => $notifiable->id]
        );

        return (new MailMessage)
            ->subject('Undangan untuk Mengaktifkan Akun Anda')
            ->line('Anda telah didaftarkan di sistem Yayasan As Salam. Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda dan membuat password.')
            ->action('Aktifkan Akun & Buat Password', $invitationUrl) // <-- Menggunakan link kustom kita
            ->line('Link ini akan kedaluwarsa dalam 24 jam.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
