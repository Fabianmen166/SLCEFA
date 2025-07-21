<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SulfurAnalysis extends Model
{
    use HasFactory;

    protected $table = 'sulfur_analyses';

    protected $fillable = [
        'consecutivo_no',
        'analysis_id',
        'fecha_analisis',
        'user_id',
        'equipo_utilizado',
        'intervalo_metodo',
        'analista',
        'codigo_interno',
        'peso_muestra',
        'pw',
        'v_extractante',
        'lectura_blanco',
        'factor_dilucion',
        'sulfur_disponible_mg_l',
        'sulfur_disponible_mg_kg',
        'observaciones_item',
    ];

    protected $casts = [
        'fecha_analisis' => 'date',
        'review_date' => 'datetime',
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