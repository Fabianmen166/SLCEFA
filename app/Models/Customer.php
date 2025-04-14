<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'customers_id';

    protected $fillable = [
        'solicitante',
        'contacto',
        'telefono',
        'nit',
        'correo',
        'customer_type_id', // Nuevo campo
    ];

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'customers_id');
    }

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type_id');
    }
}