<?php

namespace App\Trait;

use Illuminate\Support\Facades\Http;

trait WhatsAppNotificationTrait
{
    /**
     * Handle incoming notification WhatsApp API
     */
    public function notification(string $targetPhone, string $message) : void
    {
        //substring
        $targetPhone = '62' . substr($targetPhone , 1);

        Http::withHeaders(['Authorization' => env('WA_API_TOKEN' , '')])->post('https://api.fonnte.com/send', [
            'target' => $targetPhone,
            'message' => $message,
            'delay' => '30-60',
        ]);
        
    }
}