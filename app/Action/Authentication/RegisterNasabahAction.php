<?php

namespace App\Action\Authentication;

use App\Jobs\RegisterOtpCodeJob;
use App\Models\User;

class RegisterNasabahAction
{
    private $request = [];

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Handle Action
     */
    public function handle_action()
    {
       $user = User::create($this->request);
        
       //buat token
       $token = $user->createToken('koperasi-ubud' , ['nasabah'])->plainTextToken;
       $user['token'] = $token;
    
       //panggil otp job
       RegisterOtpCodeJob::dispatch($user->id , $user->phone_number , $user->full_name);

       return $user;
    }
}