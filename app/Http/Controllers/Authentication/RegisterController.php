<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\RegisterAdminAction;
use App\Action\Authentication\RegisterNasabahAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\RegisterRequest;
use App\Http\Resources\UserResource;
use Exception;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Handle register for nasabah.
     */
    public function register_nasabah(RegisterRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
        
        $action = new RegisterNasabahAction($validatedData);
        $response = $action->handle_action();

        if($response instanceof Exception){
            return response()->json([
                'status' => false,
                'error' => $response->getMessage(),
            ], 500);  
        }else{
            return response()->json([
                'status' => true,
                'data' => UserResource::make($response),
            ], 201);  
        }      
        
    }


    /**
     * Handle register for nasabah.
     */
    public function register_admin(RegisterRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
        
        $action = new RegisterAdminAction($validatedData);
        $response = $action->handle_action();

        if($response instanceof Exception){
            return response()->json([
                'status' => false,
                'error' => $response->getMessage(),
            ], 500);  
        }else{
            return response()->json([
                'status' => true,
                'data' => UserResource::make($response),
            ], 201);  
        }      
    }


}
