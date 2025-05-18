<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetOtpNotification extends Notification
{
    use Queueable;

    protected $otp;
    public function __construct($otp)
    {
        $this->otp = $otp;
    }
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('OTP Code for Password Reset')
        ->line("Your OTP code is: {$this->otp}")
        ->line('It is valid for 10 minutes.');
    }
}
