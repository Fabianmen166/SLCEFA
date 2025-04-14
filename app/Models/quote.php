<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $table = 'quotes'; // Asegúrate de que sea 'quotes' (plural)
    protected $primaryKey = 'quote_id';
    public $incrementing = false; // Porque quote_id es una cadena (string)
    protected $keyType = 'string';

    protected $fillable = [
        'quote_id',
        'user_id',
        'customers_id',
        'total',
        'archivo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id');
    }

    public function services()
    {
        return $this->belongsToMany(service::class, 'quote_services', 'quote_id', 'services_id')
                    ->withPivot('cantidad', 'subtotal', 'service_packages_id');
    }

    public function servicePackages()
    {
        return $this->belongsToMany(ServicePackage::class, 'quote_services', 'quote_id', 'service_packages_id')
                    ->withPivot('cantidad', 'subtotal', 'services_id');
    }
}