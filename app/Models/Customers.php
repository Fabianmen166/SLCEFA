<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $primaryKey = 'customers_id';

    protected $fillable = [
        'solicitante',
        'contacto',
        'telefono',
        'nit',
        'correo',
        'tipo_cliente', // Nuevo campo
    ];

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'customers_id', 'customers_id');
    }
}