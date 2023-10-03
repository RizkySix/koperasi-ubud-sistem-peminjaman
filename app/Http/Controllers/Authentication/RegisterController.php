<?php

namespace App\Http\Controllers\Authentication;

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

        $status = $response instanceof Exception ? 500 : 201;
       
        return response()->json([
            'status' => $status == 201 ? true : false,
            'data' => $status == 201 ? UserResource::make($response) : $response->getMessage(),
        ], $status);        
        
    }


}
