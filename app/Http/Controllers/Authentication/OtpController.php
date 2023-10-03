<?php

namespace App\Http\Controllers\Authentication;

use App\Action\Authentication\OtpSendAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\OtpRequest;
use App\Jobs\RegisterOtpSendNotification;
use App\Trait\UserCustomTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    use UserCustomTrait;

    /**
     * Handle resend otp request
     */
     public function resend_otp() : JsonResponse
     {
        $user = auth()->user();

        //generate otp baru
        $newOtp = $this->generate_otp($user->id);
        
        //panggil notifikasi send otp job
        RegisterOtpSendNotification::dispatch($user->id, $user->phone_number, $user->full_name , $newOtp);

        return response()->json([
            'status' => true,
            'data' => 'Kode Otp baru dikirim ke nomor WA ' . $user->phone_number,
        ],200);
     }


     /**
     * Handle resend otp request
     */
    public function send_otp(OtpRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();

        $action = new OtpSendAction($validatedData['otp_code']);
        $response = $action->handle_action();

        $status = $response == true ? 200 : 422;

        if($response instanceof Exception){
            return response()->json([
                'status' => false,
                'error' => $response->getMessage()
            ] , 500);
        }else{
            return response()->json([
                'status' => $response,
                'data' => $status == 200 ? 'Verifikasi akun berhasil' : 'Kode otp tidak sesuai'
            ] , $status);
        }
    }
}
