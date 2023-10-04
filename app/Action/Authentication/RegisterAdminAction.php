<?php

namespace App\Action\Authentication;

use App\Jobs\RegisterOtpSendNotification;
use App\Models\Admin;
use App\Trait\UserCustomTrait;
use Exception;

class RegisterAdminAction
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
    public function handle_action() : Admin|Exception
    {
       try {
            $user = Admin::create($this->request);
            
            //buat token
            $token = $user->createToken('koperasi-ubud' , ['admin'])->plainTextToken;
            $user['token'] = $token;
            $user['role'] = 'Admin';
            
            //buat otp code
            $otpCode = $this->generate_otp($user->phone_number);

            //panggil otp job
            RegisterOtpSendNotification::dispatch($user->phone_number , $user->full_name , $otpCode);

            return $user;
       } catch (Exception $e) {
            return $e;
       }
    }
}