<?php

namespace App\Action\Authentication;

use App\Jobs\RegisterOtpSendNotification;
use App\Models\User;
use App\Trait\UserCustomTrait;
use Exception;

class RegisterNasabahAction
{
    use UserCustomTrait;

    private $request = [];

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Handle Action
     */
    public function handle_action() : User|Exception
    {
       try {
            $user = User::create($this->request);
            
            //buat token
            $token = $user->createToken('koperasi-ubud' , ['nasabah'])->plainTextToken;
            $user['token'] = $token;
            
            //buat otp code
            $otpCode = $this->generate_otp($user->id);

            //panggil otp job
            RegisterOtpSendNotification::dispatch($user->id , $user->phone_number , $user->full_name , $otpCode);

            return $user;
       } catch (Exception $e) {
            return $e;
       }
    }
}