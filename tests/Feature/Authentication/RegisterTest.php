<?php

namespace Tests\Feature\Authentication;

use App\Jobs\RegisterOtpSendNotification;
use App\Models\Admin;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Trait\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase, FeatureTestTrait;
    private $payload = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group authentication-test
     */
    public function test_success_register_nasabah_should_queue_otp_code(): void
    {
        Queue::fake();
        
        Queue::assertNothingPushed();

        $this->payload = $this->set_payload('087123123123');
       
        $response = $this->postJson(RouteServiceProvider::DOMAIN . '/register/nasabah' , $this->payload);
        $response->assertStatus(201);
      
        //pastikan queue dijalankan
        Queue::assertPushed(RegisterOtpSendNotification::class);

        $this->assertDatabaseCount('users' , 1);
        $this->assertDatabaseCount('otp_codes' , 1);
        $this->assertDatabaseHas('otp_codes' , [
            'phone_number' => User::select('phone_number')->first()->phone_number, 
            'otp_code' => DB::table('otp_codes')->select('otp_code')->first()->otp_code,
        ]);
    }

     /**
     * @group authentication-test
     */
    public function test_success_register_nasabah_should_return_valid_json_response() : void
    {
        Queue::fake();
        
        $this->payload = $this->set_payload('087123123123');

        $response = $this->postJson(RouteServiceProvider::DOMAIN . '/register/nasabah' , $this->payload);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'data' => [
                    'full_name',
                    'phone_number',
                    'verified_status',
                    'address' ,
                    'birth_date',
                    'token',
                    'role'
                ]
            ]);
        
        $response->assertJson([
            'status' => true,
            'data' => [
                'full_name' => $this->payload['full_name'],
                'verified_status' => false, //pastikan masih false karena belum diverifikasi,
                'role' => 'Nasabah'
            ]
        ]);

    }


    /**
     * @group authentication-test
     */
    public function test_register_nasabah_should_fail_when_phone_number_is_exists() : void
    {
        $this->test_success_register_nasabah_should_return_valid_json_response();

        $response = $this->postJson(RouteServiceProvider::DOMAIN . '/register/nasabah' , $this->payload);
        
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'validation_errors' => [
                'phone_number'
            ]
        ]);

        $this->assertDatabaseCount('users' , 1);
        $this->assertDatabaseCount('otp_codes' , 1);
    }



    //new one
     /**
     * @group authentication-test
     */
    public function test_success_register_admin_should_queue_otp_code(): void
    {
        Queue::fake();
        
        Queue::assertNothingPushed();

        $this->payload = $this->set_payload('087123123124');
       
        $response = $this->postJson(RouteServiceProvider::DOMAIN . '/register/admin' , $this->payload);
        $response->assertStatus(201);
      
        //pastikan queue dijalankan
        Queue::assertPushed(RegisterOtpSendNotification::class);

        $this->assertDatabaseCount('admins' , 1);
        $this->assertDatabaseCount('otp_codes' , 1);
        $this->assertDatabaseHas('otp_codes' , [
            'phone_number' => Admin::select('phone_number')->first()->phone_number, 
            'otp_code' => DB::table('otp_codes')->select('otp_code')->first()->otp_code,
        ]);
    }

     /**
     * @group authentication-test
     */
    public function test_success_register_admin_should_return_valid_json_response() : void
    {
        Queue::fake();
        
        $this->payload = $this->set_payload('087123123124');

        $response = $this->postJson(RouteServiceProvider::DOMAIN . '/register/admin' , $this->payload);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'data' => [
                    'full_name',
                    'phone_number',
                    'verified_status',
                    'address' ,
                    'birth_date',
                    'token',
                    'role'
                ]
            ]);
        
        $response->assertJson([
            'status' => true,
            'data' => [
                'full_name' => $this->payload['full_name'],
                'verified_status' => false, //pastikan masih false karena belum diverifikasi,
                'role' => 'Admin'
            ]
        ]);

    }


    /**
     * @group authentication-test
     */
    public function test_register_admin_should_fail_when_phone_number_is_exists() : void
    {
        $this->test_success_register_admin_should_return_valid_json_response();

        $response = $this->postJson(RouteServiceProvider::DOMAIN . '/register/admin' , $this->payload);
        
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'validation_errors' => [
                'phone_number'
            ]
        ]);

        $this->assertDatabaseCount('admins' , 1);
        $this->assertDatabaseCount('otp_codes' , 1);
    }


   
}
