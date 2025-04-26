<?php

// app/Models/Process.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $table = 'processes';

    protected $primaryKey = 'process_id';

    protected $fillable = [
        'quote_id',
        'item_code',
        'status',
        'comunicacion_cliente',
        'dias_procesar',
        'fecha_recepcion',
        'descripcion',
        'lugar_muestreo',
        'fecha_muestreo',
        'responsable_recepcion',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_entrega' => 'date',
        'fecha_muestreo' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'quote_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'process_service', 'process_id', 'services_id')
                    ->withTimestamps();
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_recepcion');
    }

    public function analyses()
    {
        return $this->hasMany(Analysis::class, 'process_id');
    }

    // Nueva relación para conectar con ServiceProcessDetail
    public function serviceDetails()
    {
        return $this->hasMany(ServiceProcessDetail::class, 'process_id');
    }
}