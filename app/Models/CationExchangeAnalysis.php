<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CationExchangeAnalysis extends Model
{
    protected $fillable = [
        'analysis_id',
        'process_id',
        'service_id',
        'consecutivo_no',
        'fecha_analisis',
        'user_id',
        'codigo_probeta',
        'codigo_equipo',
        'controles_analiticos',
        'precision_analitica',
        'items_ensayo',
        'observaciones',
        'revisado_por',
        'fecha_revision',
        'aprobado',
        'observaciones_revision',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
    ];

    protected $casts = [
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'items_ensayo' => 'array',
    ];

    public function analysis()
    {
        return $this->belongsTo(Analysis::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
} 