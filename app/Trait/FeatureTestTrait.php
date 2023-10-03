<?php

namespace App\Trait;

trait FeatureTestTrait
{
    /**
     * set payload for create user test
     */
    private function set_payload(string $phoneNumber) : array
    {
        return [
            'full_name' => fake()->name(),
            'phone_number' => $phoneNumber,
            'password' => 'password',
            'password_confirmation' => 'password',
            'address' => fake()->address(),
            'birth_date' => fake()->date()
        ];
    }
}
