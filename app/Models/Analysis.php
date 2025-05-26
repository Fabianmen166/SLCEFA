<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    protected $fillable = [
        'process_id',
        'service_id',
        'status',
        'cantidad',
        'approved',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function phAnalysis()
    {
        return $this->hasOne(PhAnalysis::class, 'analysis_id');
    }
    public function conductivityAnalysis()
    {
        return $this->hasOne(ConductivityAnalysis::class, 'analysis_id');
    }
}