<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'customers_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'solicitante',
        'contacto',
        'telefono',
        'nit',
        'correo',
        'tipo_cliente',
        'customer_type_id',
    ];

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type_id', 'customer_type_id');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'customers_id', 'customers_id');
    }
}