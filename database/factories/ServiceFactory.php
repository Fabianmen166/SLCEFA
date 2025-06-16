<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition()
    {
        return [
            'descripcion' => $this->faker->sentence(3),
            'precio' => $this->faker->randomFloat(2, 50, 1000),
            'acreditado' => $this->faker->boolean,
        ];
    }
} 