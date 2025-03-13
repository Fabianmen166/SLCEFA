<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizacion';
    protected $primaryKey = 'id_cotizacion';
    public $incrementing = false; // No autoincremental
    protected $keyType = 'string'; // Tipo de clave es string

    protected $fillable = [
        'id_cotizacion', // Añadido porque ahora lo asignaremos manualmente
        'id_user',
        'nombre_empresa',
        'nombre_persona',
        'nit',
        'direccion',
        'telefono',
        'correo',
        'precio',
        'estado_de_pago',
        'archivo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'user_id');
    }
}