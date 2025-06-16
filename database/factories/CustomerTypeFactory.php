<?php

namespace Database\Factories;

use App\Models\CustomerType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerTypeFactory extends Factory
{
    protected $model = CustomerType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Interno', 'Externo']),
            'discount_percentage' => $this->faker->randomFloat(2, 0, 50),
            'description' => $this->faker->sentence,
        ];
    }
} 