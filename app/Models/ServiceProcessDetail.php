<?php

// app/Models/ServiceProcessDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProcessDetail extends Model
{
    protected $table = 'service_process_details';

    protected $fillable = [
        'process_id',
        'services_id',
        'type',
        'description',
        'result',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'services_id', 'services_id');
    }
}