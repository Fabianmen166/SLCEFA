<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $primaryKey = 'services_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'descripcion',
        'precio',
        'acreditado',
    ];

    public function analyses()
    {
        return $this->hasMany(Analysis::class, 'service_id', 'services_id');
    }
    public function quoteServices()
    {
        return $this->hasMany(QuoteService::class, 'services_id', 'services_id');
    }
}