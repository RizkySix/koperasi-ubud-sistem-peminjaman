<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\UserLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Admin;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
   /**
    * Handle login request
    */
    public function login(UserLoginRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
        
        $action = new LoginAction($validatedData);
        $response = $action->handle_action();

        $status = $response instanceof User || $response instanceof Admin ? 200 : 400;

        if($response instanceof Exception){
            return response()->json([
                'status' => false,
                'error' => $response->getMessage()
            ], 500);
        }else{
            return response()->json([
                'status' => $status === 200 ? true : false,
                'data' => $status === 200 ?  UserResource::make($response) : 'Credentials tidak ditemukan',
            ], $status);
        }

    }


    /**
    * Handle login request
    */
    public function logout(Request $request) : JsonResponse
    {
        $user = $request->user();
       
        //hapus token saat ini
        /* $tokenId = Str::before($request->bearerToken(), '|');
        $user->tokens()->where('id' , $tokenId)->delete(); */
        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'data' => 'Logout berhasil'
        ] , 200);
    }


     /**
    * Handle login request
    */
    public function test_data(Request $request)
    {
        return $request->user();
        /* if( auth()->user() instanceof User){
            return 'User';
        }elseif( auth()->user() instanceof Admin){
            return 'Admin';
        } */
    }
}
