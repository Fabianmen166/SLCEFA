<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $primaryKey = 'services_id';

    protected $fillable = [
        'descripcion',
        'precio',
        'acreditado',
    ];

    public function quotes()
    {
        return $this->belongsToMany(Quote::class, 'quote_services', 'services_id', 'quote_id')
                    ->withPivot('cantidad', 'subtotal', 'service_packages_id');
    }
}