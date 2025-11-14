<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The OTP code.
     *
     * @var string
     */
    public $otp;

    /**
     * Create a new message instance.
     *
     * @param  string  $otp
     * @return void
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('🔐 Your RWAMP Email Verification Code'),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-otp',
            with: [
                'otp' => $this->otp,
                'formattedOtp' => $this->formatOtp($this->otp),
            ],
        );
    }

    /**
     * Format OTP with spaces (e.g., "123 456").
     *
     * @param  string  $otp
     * @return string
     */
    private function formatOtp(string $otp): string
    {
        return substr($otp, 0, 3) . ' ' . substr($otp, 3, 3);
    }
}

