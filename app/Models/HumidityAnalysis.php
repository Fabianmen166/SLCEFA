<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HumidityAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_id',
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
        'hora_ingreso_horno',
        'hora_salida_horno',
        'temperatura_horno',
        'nombre_metodo',
        'intervalo_metodo',
        'equipo_utilizado',
        'unidades_reporte_equipo',
        'resolucion_instrumental',
        'fecha_fin_analisis',
        'codigo_interno',
        'peso_capsula',
        'peso_muestra',
        'peso_capsula_muestra_humedad',
        'peso_capsula_muestra_seca',
        'porcentaje_humedad',
        'observaciones',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
        
       
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'fecha_analisis' => 'date',
        'hora_ingreso_horno' => 'datetime:H:i',
        'hora_salida_horno' => 'datetime:H:i',
        // Eliminados los casts para arrays ya que no se usan más
    ];

    /**
     * Relación con el análisis principal
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class, 'analysis_id');
    }

    /**
     * Relación con el usuario que realizó el análisis
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con los controles analíticos
     * 
     * Nota: Cambiado a hasOne ya que cada análisis de humedad tiene un control analítico
     */
   public function analyticalControl()
{
    return $this->hasOne(AnalyticalControl::class, 'analysis_id', 'analysis_id');
}

    /**
     * Scope para análisis pendientes de revisión
     */
    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending');
    }

    /**
     * Scope para análisis aprobados
     */
    public function scopeApproved($query)
    {
        return $query->where('review_status', 'approved');
    }

    /**
     * Scope para análisis rechazados
     */
    public function scopeRejected($query)
    {
        return $query->where('review_status', 'rejected');
    }
}