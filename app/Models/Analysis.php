<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    protected $fillable = [
        'process_id',
        'service_id',
        'analysis_data',
        'status',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}