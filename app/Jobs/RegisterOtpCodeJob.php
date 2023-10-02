<?php

namespace App\Jobs;

use App\Trait\WhatsAppNotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RegisterOtpCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use WhatsAppNotificationTrait;

    public $tries = 3;
    public $backoff = 5;

    private $userId, $phoneNumber , $fullName;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userID , string $phoneNumber , string $fullName)
    {
        $this->userId = $userID;
        $this->phoneNumber = $phoneNumber;
        $this->fullName = $fullName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $otpCode = mt_rand(123145, 999999);
        $payload = [
            'user_id' => $this->userId,
            'otp_code' => $otpCode,
            'expired_time' => now()->addHour(1)
        ];

        $message = "Halo *" . $this->fullName . "*,\n\n" .
                "Terima kasih telah mendaftar di *Koperasi Ubud*. Berikut adalah kode OTP Anda untuk menyelesaikan proses registrasi:\n\n" .
                "Kode OTP: *" . $otpCode . "*\n\n" .
                "Kode akan kedaluarsa dalam 1 jam kedepan.\n\n" .
                "Mohon jangan berikan kode ini kepada siapapun demi keamanan akun Anda.\n\n" .
                "Terima kasih telah memilih *Koperasi Ubud*!\n\n" .
                "Salam,\n" .
                "Tim Layanan Pelanggan Koperasi Ubud";

        //panggil WA notification trait
        $this->notification($this->phoneNumber , $message);

        DB::table('otp_codes')->insert($payload);
    }
}
