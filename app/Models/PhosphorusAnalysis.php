<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhosphorusAnalysis extends Model
{
    use HasFactory;

    protected $table = 'phosphorus_analyses';

    protected $fillable = [
        'analysis_id',
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
        'equipo_utilizado',
        'resolucion_instrumental',
        'unidades_reporte',
        'intervalo_metodo',
        'items_ensayo',
        'controles_analiticos',
        'precision_analitica',
        'veracidad_analitica',
        'observaciones',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
        'reporte_resultados',
        'controles_calidad',
        'unidad_concentracion',
        'regresion',
        'longitud_onda',
        'espesor_capa',
        'fecha_hora_medida',
        'coeficientes_calculados',
        'grado_determinacion',
        'valor_limite',
    ];

    protected $casts = [
        'items_ensayo' => 'array',
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'veracidad_analitica' => 'array',
        'fecha_analisis' => 'date',
        'review_date' => 'datetime',
        'reporte_resultados' => 'array',
        'controles_calidad' => 'array',
        'fecha_hora_medida' => 'datetime',
    ];

    public function analysis()
    {
        return $this->belongsTo(Analysis::class, 'analysis_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
