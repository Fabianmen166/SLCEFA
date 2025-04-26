<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteService extends Model
{
    protected $table = 'quote_services';
    protected $primaryKey = 'quote_services_id';
    protected $fillable = ['quote_id', 'services_id', 'service_packages_id', 'cantidad', 'subtotal'];

    public function service()
    {
        return $this->belongsTo(Service::class, 'services_id', 'services_id');
    }

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class, 'service_packages_id', 'service_packages_id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'quote_id');
    }
}