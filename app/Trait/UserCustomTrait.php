<?php

namespace App\Trait;

use Illuminate\Support\Facades\DB;

trait UserCustomTrait
{
    /**
     * Generate Otp method
     */
    public function generate_otp(int $userId) : int
    {
        $otpCode = mt_rand(123145, 999999);
        $payload = [
            'user_id' => $userId,
            'otp_code' => $otpCode,
            'expired_time' => now()->addHour(1)
        ];

        //hapus seluruh otp code user sebelum dibuat kembali
        DB::table('otp_codes')->where('user_id' , $userId)->delete();
        
        DB::table('otp_codes')->insert($payload);

        return $otpCode;
    }
}