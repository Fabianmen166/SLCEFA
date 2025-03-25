<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $primaryKey = 'services_id';

    protected $fillable = [
        'descripcion',
        'precio',
        'acreditado',
    ];

    public function quotes()
    {
        return $this->belongsToMany(Quote::class, 'quote_service', 'services_id', 'quote_id')
                    ->withPivot('cantidad', 'subtotal')
                    ->withTimestamps();
    }
}