<?php

namespace App\Action\Authentication;

use Exception;
use Illuminate\Support\Facades\DB;

class OtpSendAction
{
    private $otpCode;

    public function __construct(int $otpCode)
    {
        $this->otpCode = $otpCode;
    }

    /**
     * Handle Action
     */
    public function handle_action() : bool|Exception
    {
        try {
            $user = auth()->user();
            $findOtp = DB::table('otp_codes')
                            ->where('user_id' , $user->id)
                            ->where('otp_code' , $this->otpCode)
                            ->where('expired_time' , '>' , now());

            if($findOtp->first()){
                $user->phone_number_verified = now();
                $user->save();
                $findOtp->delete();

                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return $e;
        }

    }
}