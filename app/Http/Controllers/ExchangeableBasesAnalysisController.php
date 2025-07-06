<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExchangeableBasesAnalysis;
use App\Models\Analysis;
use Illuminate\Support\Facades\Auth;

class ExchangeableBasesAnalysisController extends Controller
{
    public function process($processId, $serviceId)
    {
        // Obtener el proceso para mostrar el dato en la vista
        $process = \App\Models\Process::where('process_id', $processId)->first();
        return view('bases_cambiables.process', compact('processId', 'serviceId', 'process'));
    }

    public function store(Request $request, $processId, $serviceId)
    {
        $request->validate([
            'volumen_final.*' => 'required|numeric|min:0.0001',
            'peso_muestra.*' => 'required|numeric|min:0.0001',
            'k_lectura.*' => 'required|numeric',
            'k_factor.*' => 'required|numeric',
            'ca_lectura.*' => 'required|numeric',
            'ca_factor.*' => 'required|numeric',
            'mg_lectura.*' => 'required|numeric',
            'mg_factor.*' => 'required|numeric',
            'na_lectura.*' => 'required|numeric',
            'na_factor.*' => 'required|numeric',
        ], [
            'required' => 'Este campo es obligatorio.',
            'numeric' => 'Debe ser un valor numérico.',
            'min' => 'Debe ser mayor a 0.'
        ]);

        // Buscar el análisis principal
        $analysis = Analysis::where('process_id', $processId)
            ->where('service_id', $serviceId)
            ->firstOrFail();

        // Guardar el análisis de bases cambiables
        $exchangeable = ExchangeableBasesAnalysis::updateOrCreate(
            ['analysis_id' => $analysis->id],
            [
                'analysis_id' => $analysis->id,
                'process_id' => $processId,
                'service_id' => $serviceId,
                'user_id' => Auth::id(),
                'controles_analiticos' => null,
                'precision_analitica' => null,
                'veracidad_analitica' => null,
                'muestra_referencia_certificada_analitica' => null,
                'items_ensayo' => json_encode($this->formatearItemsEnsayo($request)),
                'observaciones' => $request->observaciones_generales,
                'review_status' => 'pending',
            ]
        );

        // Cambiar estado del análisis principal a 'completed'
        $analysis->status = 'completed';
        $analysis->save();

        return redirect()->route('bases_cambiables_analysis.index')
            ->with('success', 'Análisis de Bases Cambiables guardado exitosamente.');
    }

    private function formatearItemsEnsayo($request)
    {
        $items = [];
        $n = count($request->volumen_final);
        for ($i = 0; $i < $n; $i++) {
            $items[] = [
                'codigo_interno' => $request->codigo_interno[$i] ?? null,
                'volumen_final' => $request->volumen_final[$i],
                'peso_muestra' => $request->peso_muestra[$i],
                'humedad' => $request->humedad[$i],
                'k_lectura' => $request->k_lectura[$i],
                'k_factor' => $request->k_factor[$i],
                'k_resultado' => $request->k_resultado[$i],
                'ca_lectura' => $request->ca_lectura[$i],
                'ca_factor' => $request->ca_factor[$i],
                'ca_resultado' => $request->ca_resultado[$i],
                'mg_lectura' => $request->mg_lectura[$i],
                'mg_factor' => $request->mg_factor[$i],
                'mg_resultado' => $request->mg_resultado[$i],
                'na_lectura' => $request->na_lectura[$i],
                'na_factor' => $request->na_factor[$i],
                'na_resultado' => $request->na_resultado[$i],
                'observaciones' => $request->observaciones[$i] ?? null,
            ];
        }
        return $items;
    }

    public function index()
    {
        // Buscar solo los análisis pendientes de bases cambiables (singular/plural, insensible a mayúsculas)
        $analyses = \App\Models\Analysis::with(['service', 'process.quote.customer'])
            ->whereHas('service', function($q) {
                $q->whereRaw('LOWER(descripcion) LIKE ?', ['%base cambiable%'])
                  ->orWhereRaw('LOWER(descripcion) LIKE ?', ['%bases cambiables%']);
            })
            ->where('status', 'pending')
            ->get();
        return view('bases_cambiables.index', compact('analyses'));
    }
}
