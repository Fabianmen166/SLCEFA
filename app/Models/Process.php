<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $primaryKey = 'process_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'process_id',
        'quote_id',
        'item_code',
        'comunicacion_cliente',
        'dias_procesar',
        'fecha_recepcion',
        'descripcion',
        'lugar_muestreo',
        'fecha_muestreo',
        'responsable_recepcion',
        'fecha_entrega',
        'status',
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime', // Cast to Carbon instance
        'fecha_entrega' => 'datetime',  // Cast to Carbon instance
        'fecha_muestreo' => 'date',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function analyses()
    {
        return $this->hasMany(Analysis::class, 'process_id', 'process_id');
    }

    public function pendingPhAnalyses()
    {
        return $this->analyses()
                    ->where('status', 'pending')
                    ->whereHas('service', function ($query) {
                        $query->where('descripcion', 'like', '%pH%');
                    });
    }





    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_recepcion', 'user_id');
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