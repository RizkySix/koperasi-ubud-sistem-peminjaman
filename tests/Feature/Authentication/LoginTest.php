<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    private $user , $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }


    /**
     * @group authentication-test
     */
    public function test_success_login_with_valid_credentials(): void
    {
        $this->assertDatabaseEmpty('personal_access_tokens');

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/login' , [
            'phone_number' => $this->user->phone_number,
            'password' => 'password'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'full_name',
                'phone_number',
                'verified_status',
                'address' ,
                'birth_date',
                'token'
            ]
            ]);

        //pastikan dibuat
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        
    }


    /**
     * @group authentication-test
     */
    public function test_should_failded_login_when_credential_not_found() : void
    {
        $this->assertDatabaseEmpty('personal_access_tokens');

        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/login' , [
            'phone_number' => $this->user->phone_number,
            'password' => 'password-salah'
        ]);
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'status',
            'data'
            ]);

        //pastikan token masih kosong
        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    
     /**
     * @group authentication-test
     */
    public function test_multi_login_should_not_destroy_old_tokens() : void
    {
        $this->assertDatabaseEmpty('personal_access_tokens');

        //hit endpoint login pertama
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/login' , [
            'phone_number' => $this->user->phone_number,
            'password' => 'password'
        ]);
        $response->assertStatus(200);

        //pastikan dibuat
        $this->assertDatabaseCount('personal_access_tokens' , 1);

        //hit endpoint login kedua
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/login' , [
            'phone_number' => $this->user->phone_number,
            'password' => 'password'
        ]);
        $response->assertStatus(200);

        //pastikan dibuat
        $this->assertDatabaseCount('personal_access_tokens' , 2);

        //dapatkan token untuk test berikutnya
        $this->token = $response->json()['data']['token'];

    }


     /**
     * @group authentication-test
     */
   /*  public function test_logout_should_only_destroy_current_authentication_token() : void
    {
        $this->test_multi_login_should_not_destroy_old_tokens();
       
        //pastikan total token saat ini 2
        $this->assertDatabaseCount('personal_access_tokens' , 2);
        
        $response = $this->actingAs($this->user)->postJson(RouteServiceProvider::DOMAIN . '/logout');
        $response->assertStatus(200);
        

        //pastikan total token saat ini menjadi 1
        $this->assertDatabaseCount('personal_access_tokens' , 1);
        
    } */
}
