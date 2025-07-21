<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticalControl extends Model
{
    use HasFactory;

    protected $table = 'analytical_controls';

    protected $fillable = [
        'analysis_id',
        'masa_suelo',
        'masa_agua',
        'masa_suelo_seco',
        'humedad_fortificada_teorica',
        'humedad_obtenida',
        'humedad_fortificada',
        'recuperacion',
        'valor_referencia',
        'valor_obtenido',
        'blanco_metodo',
        'resultado',
        'limite_cuantificacion_metodo',
        'rango_metodo',
        'humedad_replica_1',
        'humedad_replica_2',
        'dpr',
        'identificacion_mf', // ID para muestra fortificada
        'identificacion_mr', // ID para muestra de referencia
        'identificacion_dm', // ID para muestra duplicada
        'identificacion_bm', // ID para muestra de blanco
        'estado',
        'observaciones',
        'analista', // Nuevo campo para el nombre del analista
    ];

    public function analysis()
    {
        return $this->belongsTo(Analysis::class);
    }

    public function humidityAnalysis()
    {
        return $this->belongsTo(HumidityAnalysis::class, 'analysis_id', 'analysis_id');
    }
}