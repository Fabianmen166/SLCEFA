<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $table = 'quote';

    protected $primaryKey = 'quote_id';

    public $incrementing = false; // No es auto-incrementado

    protected $keyType = 'string'; // Especificamos que la clave primaria es un string

    protected $fillable = [
        'quote_id',
        'customers_id',
        'total',
        'id_user',
        'archivo',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customers_id', 'customers_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function services()
    {
        return $this->belongsToMany(Services::class, 'quote_service', 'quote_id', 'services_id')
                    ->withPivot('cantidad', 'subtotal')
                    ->withTimestamps();
    }
}