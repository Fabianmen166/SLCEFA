<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analytical extends Model
{
    use HasFactory;

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
        'estado',
        'observaciones'
    ];

    /**
     * Relación con el análisis de humedad
     */
    public function humidityAnalysis(): BelongsTo
    {
        return $this->belongsTo(HumidityAnalysis::class, 'analysis_id', 'analysis_id');
    }

    /**
     * Relación con el análisis principal
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class, 'analysis_id');
    }
}