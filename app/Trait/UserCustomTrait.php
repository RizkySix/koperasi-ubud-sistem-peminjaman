<?php

namespace App\Trait;

use Illuminate\Support\Facades\DB;

trait UserCustomTrait
{
    /**
     * Generate Otp method
     */
    public function generate_otp(string $phoneNumber) : int
    {
        $otpCode = mt_rand(123145, 999999);
        $payload = [
            'phone_number' => $phoneNumber,
            'otp_code' => $otpCode,
            'expired_time' => now()->addHour(1)
        ];

        //hapus seluruh otp code user sebelum dibuat kembali
        DB::table('otp_codes')->where('phone_number' , $phoneNumber)->delete();
        
        DB::table('otp_codes')->insert($payload);

        return $otpCode;
    }
}