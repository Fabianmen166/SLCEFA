<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $table = 'processes';

    protected $primaryKey = 'process_id';
    public $incrementing = false; // process_id is not auto-incrementing
    protected $keyType = 'string'; // process_id is a string

    protected $fillable = [
        'process_id', // Added to allow mass assignment
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



    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_recepcion', 'user_id');
    }

    public function analyses()
    {
        return $this->hasMany(Analysis::class, 'process_id', 'process_id');
    }

    public function serviceProcessDetails()
    {
        return $this->hasMany(ServiceProcessDetail::class, 'process_id', 'process_id');
    }
    public function services()
{
    return $this->belongsToMany(Service::class, 'process_service', 'process_id', 'services_id')
               ->withPivot('status', 'cantidad');
}
public function completedServices()
{
    return $this->belongsToMany(Service::class, 'process_service', 'process_id', 'services_id')
               ->withPivot('status', 'cantidad');
}
}