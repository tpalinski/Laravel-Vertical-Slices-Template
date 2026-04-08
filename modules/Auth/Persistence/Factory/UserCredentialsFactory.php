<?php

namespace Modules\Auth\Persistence\Factory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserCredentialsFactory extends Factory
{
    protected $model = \Modules\Auth\Persistence\Model\UserCredentials::class;

    public function definition(): array
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
            'login' => fake()->userName(),
            'password' => Hash::make(fake()->password()),
            'userId' => fake()->numberBetween(0, 1024),
        ];
    }
}
