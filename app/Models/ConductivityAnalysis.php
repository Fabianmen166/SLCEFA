<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConductivityAnalysis extends Model
{
    protected $table = 'conductivity_analyses';

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
    ];

    protected $casts = [
        'items_ensayo' => 'array',
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'veracidad_analitica' => 'array',
        'fecha_analisis' => 'date',
        'review_date' => 'datetime',
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