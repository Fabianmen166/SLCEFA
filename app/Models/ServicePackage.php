<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service;

class ServicePackage extends Model
{
    use HasFactory;

    protected $table = 'service_packages';
    protected $primaryKey = 'service_packages_id';

    protected $fillable = [
        'nombre',
        'precio',
        'acreditado',
        'included_services',
    ];

    public function quotes()
    {
        return $this->belongsToMany(Quote::class, 'quote_services', 'service_packages_id', 'quote_id')
                    ->withPivot('cantidad', 'subtotal', 'services_id');
    }

    public function getIncludedServicesAttribute($value)
    {
        $serviceIds = $value ? json_decode($value, true) : [];
        if (empty($serviceIds)) {
            return [];
        }

        // Corrección: "service" debe ser "Service" (respetar mayúsculas)
        return Service::whereIn('services_id', $serviceIds)
                      ->pluck('descripcion')
                      ->toArray();
    }

    public function setIncludedServicesAttribute($value)
    {
        $this->attributes['included_services'] = $value ? json_encode($value) : null;
    }
}