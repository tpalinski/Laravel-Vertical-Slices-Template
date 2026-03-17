<?php

namespace Modules\Pibble\Persistence\Factory;

use Illuminate\Database\Eloquent\Factories\Factory;

class PibbleFactory extends Factory
{
    protected $model = \Modules\Pibble\Persistence\Model\Pibble::class;

    public function definition(): array
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
