<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    use HasFactory;

    protected $table = 'customer_types'; // Nombre correcto de la tabla
    protected $primaryKey = 'customer_type_id';

    protected $fillable = [
        'name',
        'discount_percentage',
        'description',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_type_id', 'customer_type_id');
    }
}