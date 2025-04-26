<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    use HasFactory;

    protected $table = 'service_packages';
    protected $primaryKey = 'service_packages_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'nombre',
        'precio',
        'acreditado',
        'included_services',
    ];

    public function quoteServices()
    {
        return $this->hasMany(QuoteService::class, 'service_packages_id', 'service_packages_id');
    }

    public function getIncludedServicesAttribute()
    {
        $serviceIds = $this->getRawOriginal('included_services') ? json_decode($this->getRawOriginal('included_services'), true) : [];
        if (empty($serviceIds)) {
            return collect([]);
        }

        return Service::whereIn('services_id', $serviceIds)->get();
    }

    public function getIncludedServiceIdsAttribute()
    {
        return $this->getRawOriginal('included_services') ? json_decode($this->getRawOriginal('included_services'), true) : [];
    }

    public function setIncludedServicesAttribute($value)
    {
        $this->attributes['included_services'] = $value ? json_encode($value) : null;
    }

    // Optionally, keep this method for compatibility, but it's not needed for the PDF
    public function getIncludedServiceObjects()
    {
        return $this->included_services;
    }
}