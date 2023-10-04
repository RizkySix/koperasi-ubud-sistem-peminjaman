<?php

namespace App\Action\Authentication;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class LoginAction
{
    private $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;    
    }

    /**
     * Handle Action
     */
    public function handle_action() : mixed
    {
        try {
            
            //check crendential nasabah
            if(Auth::attempt(['phone_number' => $this->data['phone_number'], 'password' => $this->data['password']])){
                $user = auth()->user();

                //buat token baru
                $token = $user->createToken('koperasi-ubud' , ['nasabah'])->plainTextToken;

                $user['token'] = $token;
                $user['role'] = 'Nasabah';

                return $user;
            }
            
            //check credential admin
            if(Auth::guard('admins')->attempt(['phone_number' => $this->data['phone_number'], 'password' => $this->data['password']])){
                $user = auth('admins')->user();

                //buat token baru
                $token = $user->createToken('koperasi-ubud' , ['admin'])->plainTextToken;

                $user['token'] = $token;
                $user['role'] = 'Admin';

                return $user;
            }

            return false;

        } catch (Exception $e) {
            return $e;
        }
    }

}