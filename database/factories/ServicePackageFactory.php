<?php

namespace Database\Factories;

use App\Models\ServicePackage;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePackageFactory extends Factory
{
    protected $model = ServicePackage::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->words(3, true),
            'precio' => $this->faker->randomFloat(2, 200, 2000),
            'acreditado' => $this->faker->boolean,
            'included_services' => json_encode([Service::factory()->create()->services_id]),
        ];
    }
} 