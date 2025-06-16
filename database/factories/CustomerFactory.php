<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'solicitante' => $this->faker->company,
            'contacto' => $this->faker->name,
            'telefono' => $this->faker->phoneNumber,
            'nit' => $this->faker->unique()->numerify('##########'),
            'correo' => $this->faker->companyEmail,
            'customer_type_id' => CustomerType::factory(),
        ];
    }
} 