<?php

namespace App\Jobs;

use App\Trait\WhatsAppNotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegisterOtpSendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use WhatsAppNotificationTrait;

    public $tries = 3;
    public $backoff = 5;

    private $phoneNumber , $fullName , $otpCode;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phoneNumber , string $fullName , string $otpCode)
    {
        $this->phoneNumber = $phoneNumber;
        $this->fullName = $fullName;
        $this->otpCode = $otpCode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = "Halo *" . $this->fullName . "*,\n\n" .
                "Terima kasih telah mendaftar di *Koperasi Ubud*. Berikut adalah kode OTP Anda untuk menyelesaikan proses registrasi:\n\n" .
                "Kode OTP: *" . $this->otpCode . "*\n\n" .
                "Kode akan kedaluarsa dalam 1 jam kedepan.\n\n" .
                "Mohon jangan berikan kode ini kepada siapapun demi keamanan akun Anda.\n\n" .
                "Terima kasih telah memilih *Koperasi Ubud*!\n\n" .
                "Salam,\n" .
                "Tim Layanan Pelanggan Koperasi Ubud";

        //panggil WA notification trait
        $this->notification($this->phoneNumber , $message);

    }
}
