<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        // Primero, verificar si el servicio ya existe
        $existingService = Service::where('descripcion', 'Análisis de Intercambio Catiónico')->first();
        
        if (!$existingService) {
            // Si no existe, crearlo
            Service::create([
                'descripcion' => 'Análisis de Intercambio Catiónico',
                'precio' => 0.00,
                'acreditado' => true,
            ]);
            
            DB::statement('ALTER TABLE services AUTO_INCREMENT = 1');
        }
    }
} 