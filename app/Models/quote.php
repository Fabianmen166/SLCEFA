<?php

// app/Models/Quote.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $table = 'quotes';
    protected $primaryKey = 'quote_id';
    public $incrementing = false;
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

    public function quoteServices()
    {
        return $this->hasMany(QuoteService::class, 'quote_id', 'quote_id');
    }

    public function processes()
    {
        return $this->hasMany(Process::class, 'quote_id', 'quote_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'quote_services', 'quote_id', 'services_id')
                    ->withPivot('cantidad', 'subtotal', 'service_packages_id')
                    ->withTimestamps();
    }

    // Nueva relación para ServicePackages
    public function servicePackages()
    {
        return $this->belongsToMany(ServicePackage::class, 'quote_services', 'quote_id', 'service_packages_id')
                    ->withPivot('cantidad', 'subtotal', 'services_id')
                    ->withTimestamps();
    }
}