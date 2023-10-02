<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\RegisterNasabahAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\RegisterRequest;
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
        
        return response()->json([
            'response' => $response,
        ], 201);
        
    }

}
