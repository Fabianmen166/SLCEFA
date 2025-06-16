<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Process;
use App\Models\PhosphorusAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PhosphorusAnalysisController extends Controller
{
    public function index()
    {
        try {
            Log::info('User accessing phosphorus analysis index:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);

            $phosphorusAnalyses = PhosphorusAnalysis::with(['analysis.process.quote.customer', 'user'])
                ->get()
                ->filter(function ($analysis) {
                    return $analysis->analysis->status === 'pending';
                });

            Log::info('Returned analyses loaded for index:', [
                'phosphorusAnalyses_count' => $phosphorusAnalyses->count(),
                'analysis_ids' => $phosphorusAnalyses->pluck('id')->toArray(),
            ]);

            $processes = Process::where('status', 'pending')
                ->with([
                    'analyses' => function ($query) {
                        $query->where('status', 'pending')
                              ->whereHas('service', function ($serviceQuery) {
                                  $serviceQuery->where('descripcion', 'like', '%Fósforo%');
                              })
                              ->with('service');
                    },
                ])
                ->get()
                ->filter(function ($process) {
                    return $process->analyses->isNotEmpty();
                });

            Log::info('Pending processes loaded for index:', [
                'processes_count' => $processes->count(),
            ]);

            return view('phosphorus_analyses.index', compact('phosphorusAnalyses', 'processes'));
        } catch (\Exception $e) {
            Log::error('Error in PhosphorusAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('personal_tecnico.dashboard')
                           ->with('error', 'Error al cargar la gestión de análisis de fósforo: ' . $e->getMessage());
        }
    }

    public function phosphorusAnalysis($processId, $serviceId)
    {
        try {
            $process = Process::with(['quote.customer', 'analyses.service'])
                ->findOrFail($processId);

            $analysis = Analysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->firstOrFail();

            return view('phosphorus_analyses.process', compact('process', 'analysis'));
        } catch (\Exception $e) {
            Log::error('Error in PhosphorusAnalysisController@phosphorusAnalysis: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'service_id' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('phosphorus_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de fósforo: ' . $e->getMessage());
        }
    }

    public function storePhosphorusAnalysis(Request $request, $processId, $serviceId)
    {
        try {
            $request->validate([
                'consecutivo_no' => 'required|string',
                'equipo_utilizado' => 'required|string',
                'resolucion_instrumental' => 'required|string',
                'unidades_reporte' => 'required|string',
                'intervalo_metodo' => 'required|string',
                'items_ensayo' => 'required|json',
                'controles_analiticos' => 'required|json',
                'precision_analitica' => 'required|json',
                'veracidad_analitica' => 'required|json',
                'observaciones' => 'nullable|string',
                'reporte_resultados' => 'nullable|json',
                'controles_calidad' => 'nullable|json',
                'unidad_concentracion' => 'nullable|string',
                'regresion' => 'nullable|string',
                'longitud_onda' => 'nullable|string',
                'espesor_capa' => 'nullable|string',
                'fecha_hora_medida' => 'nullable|date',
                'coeficientes_calculados' => 'nullable|string',
                'grado_determinacion' => 'nullable|string',
                'valor_limite' => 'nullable|string',
            ]);

            $analysis = Analysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->firstOrFail();

            $phosphorusAnalysis = PhosphorusAnalysis::create([
                'analysis_id' => $analysis->id,
                'consecutivo_no' => $request->consecutivo_no,
                'fecha_analisis' => now(),
                'user_id' => Auth::id(),
                'equipo_utilizado' => $request->equipo_utilizado,
                'resolucion_instrumental' => $request->resolucion_instrumental,
                'unidades_reporte' => $request->unidades_reporte,
                'intervalo_metodo' => $request->intervalo_metodo,
                'items_ensayo' => $request->items_ensayo,
                'controles_analiticos' => $request->controles_analiticos,
                'precision_analitica' => $request->precision_analitica,
                'veracidad_analitica' => $request->veracidad_analitica,
                'observaciones' => $request->observaciones,
                'reporte_resultados' => $request->reporte_resultados,
                'controles_calidad' => $request->controles_calidad,
                'unidad_concentracion' => $request->unidad_concentracion,
                'regresion' => $request->regresion,
                'longitud_onda' => $request->longitud_onda,
                'espesor_capa' => $request->espesor_capa,
                'fecha_hora_medida' => $request->fecha_hora_medida,
                'coeficientes_calculados' => $request->coeficientes_calculados,
                'grado_determinacion' => $request->grado_determinacion,
                'valor_limite' => $request->valor_limite,
            ]);

            $analysis->update(['status' => 'completed']);

            Log::info('Phosphorus analysis stored successfully:', [
                'user_id' => Auth::id(),
                'analysis_id' => $analysis->id,
                'phosphorus_analysis_id' => $phosphorusAnalysis->id,
            ]);

            return redirect()->route('phosphorus_analysis.index')
                           ->with('success', 'Análisis de fósforo guardado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error in PhosphorusAnalysisController@storePhosphorusAnalysis: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'service_id' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('phosphorus_analysis.index')
                           ->with('error', 'Error al guardar el análisis de fósforo: ' . $e->getMessage());
        }
    }
}
