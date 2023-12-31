<?php

namespace Tests\Feature\Authentication;

use App\Jobs\RegisterOtpSendNotification;
use App\Models\Admin;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Trait\FeatureTestTrait;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase, FeatureTestTrait;
    private $user , $secUser , $thirdUser;
    private $admin , $secAdmin , $thirdAdmin;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group authentication-test
     */
    public function test_nasabah_resend_otp_should_queued_and_replace_old_otp_with_new_otp(): void
    {
        Queue::fake();

        Queue::assertNothingPushed();

        //panggil setup nasabah
        $this->nasabah();
        
        //pastikan sudah ada otp code sebelumnya
        $this->assertDatabaseCount('otp_codes' , 1);
        $currentOtpCode = $this->get_otp();
      
        $this->assertDatabaseHas('otp_codes' , (array)$currentOtpCode);

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(200);

        //pastikan otp code masih 1 saat ini
        $this->assertDatabaseCount('otp_codes' , 1);

        //pastikan otp code yang sebelumnya mising
        $this->assertDatabaseMissing('otp_codes' , (array)$currentOtpCode);
        
        //pastikan code otp baru sudah disimpan
        $currentOtpCode = $this->get_otp();
        $this->assertDatabaseHas('otp_codes' , (array)$currentOtpCode);

        Queue::assertPushed(RegisterOtpSendNotification::class);
    }


     /**
     * @group authentication-test
     */
    public function test_nasabah_only_one_resend_otp_request_per_minute() : void
    {
        //panggil setup nasabah
        $this->nasabah();
        
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(429);

        //teleport kemasa depan sebanyak 2 menit
        Carbon::setTestNow(now()->addMinutes(2));

        //harusnya sekarang success lagi
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(200);
    }


     /**
     * @group authentication-test
     */
    public function test_nasabah_send_otp_with_invalid_otp_or_expired_otp_or_invalid_user_try_to_use_should_fail() : void
    {
        //panggil setup nasabah
        $this->nasabah();

        $this->assertDatabaseCount('otp_codes' , 1);
        $getOtp = $this->get_otp();

        //coba hit endpoint dengan invalid otp
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => '123123'
        ]);
        $response->assertStatus(422);

        //coba hit endpoint oleh invalid user
        $response = $this->actingAs($this->secUser)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => $getOtp->otp_code
        ]);
        $response->assertStatus(422);
        
        //coba hit endpoint saat otp sudah expired
        Carbon::setTestNow(now()->addHours(1));

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => $getOtp->otp_code
        ]);
        $response->assertStatus(422);        
    }


     /**
     * @group authentication-test
     */
    public function test_nasabah_phone_number_should_verified_when_send_otp_success() : void
    {
        //panggil setup nasabah
        $this->nasabah();

        $this->assertDatabaseCount('otp_codes' , 1);
        $getOtp = $this->get_otp();

        //apastikan masih null
        $this->assertEquals(null , $this->user->phone_number_verified);

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => $getOtp->otp_code
        ]);
        $response->assertStatus(200);

        //pastikan email verified sudah tidak null
        $this->user->refresh();
        $this->assertNotEquals(null , $this->user->phone_number_verified);

        //pastikan table otp codes sudah kosong
        $this->assertDatabaseEmpty('otp_codes');
        $this->assertDatabaseMissing('otp_codes' , (array)$getOtp);

    }


    /**
     * @group authentication-test
     */
    public function test_verified_nasabah_cant_access_resend_and_send_otp_endpoint() : void
    {
        //panggil setup nasabah
        $this->nasabah();

        //hit endpoint resend otp
        $response = $this->actingAs($this->thirdUser)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(403);

        //hit endpoint send otp
        $response = $this->actingAs($this->thirdUser)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => '087123444444'
        ]);
        $response->assertStatus(403);

    }


    //new one
      /**
     * @group authentication-test
     */
    public function test_admin_resend_otp_should_queued_and_replace_old_otp_with_new_otp(): void
    {
        //panggil setup admin
        $this->admin();

        Queue::fake();

        Queue::assertNothingPushed();

        //pastikan sudah ada otp code sebelumnya
        $this->assertDatabaseCount('otp_codes' , 1);
        $currentOtpCode = $this->get_otp();
      
        $this->assertDatabaseHas('otp_codes' , (array)$currentOtpCode);

        $response = $this->actingAs($this->admin)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(200);

        //pastikan otp code masih 1 saat ini
        $this->assertDatabaseCount('otp_codes' , 1);

        //pastikan otp code yang sebelumnya mising
        $this->assertDatabaseMissing('otp_codes' , (array)$currentOtpCode);
        
        //pastikan code otp baru sudah disimpan
        $currentOtpCode = $this->get_otp();
        $this->assertDatabaseHas('otp_codes' , (array)$currentOtpCode);

        Queue::assertPushed(RegisterOtpSendNotification::class);
    }


     /**
     * @group authentication-test
     */
    public function test_admin_only_one_resend_otp_request_per_minute() : void
    {
         //panggil setup admin
         $this->admin();

        $response = $this->actingAs($this->admin)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(429);

        //teleport kemasa depan sebanyak 2 menit
        Carbon::setTestNow(now()->addMinutes(2));

        //harusnya sekarang success lagi
        $response = $this->actingAs($this->admin)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(200);
    }


     /**
     * @group authentication-test
     */
    public function test_admin_send_otp_with_invalid_otp_or_expired_otp_or_invalid_user_try_to_use_should_fail() : void
    {
         //panggil setup admin
         $this->admin();

        $this->assertDatabaseCount('otp_codes' , 1);
        $getOtp = $this->get_otp();

        //coba hit endpoint dengan invalid otp
        $response = $this->actingAs($this->admin)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => '123123'
        ]);
        $response->assertStatus(422);

        //coba hit endpoint oleh invalid admin
        $response = $this->actingAs($this->secAdmin)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => $getOtp->otp_code
        ]);
        $response->assertStatus(422);
        
        //coba hit endpoint saat otp sudah expired
        Carbon::setTestNow(now()->addHours(1));

        $response = $this->actingAs($this->admin)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => $getOtp->otp_code
        ]);
        $response->assertStatus(422);        
    }


     /**
     * @group authentication-test
     */
    public function test_admin_phone_number_should_verified_when_send_otp_success() : void
    {
         //panggil setup admin
         $this->admin();

        $this->assertDatabaseCount('otp_codes' , 1);
        $getOtp = $this->get_otp();

        //apastikan masih null
        $this->assertEquals(null , $this->admin->phone_number_verified);

        $response = $this->actingAs($this->admin)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => $getOtp->otp_code
        ]);
        $response->assertStatus(200);

        //pastikan email verified sudah tidak null
        $this->admin->refresh();
        $this->assertNotEquals(null , $this->admin->phone_number_verified);

        //pastikan table otp codes sudah kosong
        $this->assertDatabaseEmpty('otp_codes');
        $this->assertDatabaseMissing('otp_codes' , (array)$getOtp);

    }


    /**
     * @group authentication-test
     */
    public function test_verified_admin_cant_access_resend_and_send_otp_endpoint() : void
    {
         //panggil setup admin
         $this->admin();
         
        //hit endpoint resend otp
        $response = $this->actingAs($this->thirdAdmin)->postJson(RouteServiceProvider::DOMAIN . '/otp/resend');
        $response->assertStatus(403);

        //hit endpoint send otp
        $response = $this->actingAs($this->thirdAdmin)->postJson(RouteServiceProvider::DOMAIN . '/otp/send' , [
            'otp_code' => '087123444444'
        ]);
        $response->assertStatus(403);

    }


    /**
     * Nasabah test
     */
    private function nasabah() : void
    {
         //nasabah
         $payload = $this->set_payload('087123123123');
         $this->register_test($payload);
 
         $this->user = User::first();
         $this->secUser = User::factory()->create([
             'phone_number_verified' => null
         ]);
         $this->thirdUser = User::factory()->create();
 
    }

    /**
     * Admin test
     */
    private function admin() : void
    {
           
        //admin
        $payload = $this->set_payload('087123123124');
        $this->register_test($payload , false);

        $this->admin = Admin::first();
        $this->secAdmin = Admin::factory()->create([
            'phone_number_verified' => null
        ]);
        $this->thirdAdmin = Admin::factory()->create();
    }

    /**
     * hit register endpoint
     */
    private function register_test(array $payload , bool $who = true) : void
    {
       Queue::fake();
       
       if($who){
            $this->postJson(RouteServiceProvider::DOMAIN . '/register/nasabah' , $payload);
       }else{
            $this->postJson(RouteServiceProvider::DOMAIN . '/register/admin' , $payload);
       }
    }
    
    /**
     * Get Otp test
     */
    private function get_otp() : object
    {
        $currentOtpCode = DB::table('otp_codes')->select('otp_code' , 'phone_number')->first();
        return $currentOtpCode;
    }
}
