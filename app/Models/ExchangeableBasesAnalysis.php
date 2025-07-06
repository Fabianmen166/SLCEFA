<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeableBasesAnalysis extends Model
{
    use HasFactory;

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
        'veracidad_analitica',
        'muestra_referencia_certificada_analitica',
        'items_ensayo',
        'observaciones',
        'review_status',
        'reviewed_by',
        'reviewer_role',
        'review_date',
        'review_observations',
    ];

    protected $casts = [
        'controles_analiticos' => 'array',
        'precision_analitica' => 'array',
        'veracidad_analitica' => 'array',
        'muestra_referencia_certificada_analitica' => 'array',
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
