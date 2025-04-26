<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteServicePackage extends Model
{
    protected $fillable = ['quote_id', 'service_packages_id', 'quantity'];

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class, 'service_packages_id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }
}