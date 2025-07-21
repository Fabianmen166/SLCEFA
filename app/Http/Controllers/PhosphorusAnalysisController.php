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
                'fecha_analisis' => 'required|date',
                'equipo_utilizado' => 'required|string',
                'intervalo_metodo' => 'required|string',
                'analista' => 'required|string',
                'items' => 'required|array',
                'items.*.codigo_interno' => 'nullable|string',
                'items.*.peso_muestra' => 'nullable|numeric',
                'items.*.pw' => 'nullable|numeric',
                'items.*.v_extractante' => 'nullable|numeric',
                'items.*.lectura_blanco' => 'nullable|numeric',
                'items.*.factor_dilucion' => 'nullable|numeric',
                'items.*.fosforo_disponible_mg_l' => 'nullable|numeric',
                'items.*.fosforo_disponible_mg_kg' => 'nullable|numeric',
                'items.*.observaciones_item' => 'nullable|string',
                'controles_analiticos' => 'required|array',
            ]);

            $analysis = Analysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->firstOrFail();

            // Guardar ítems de ensayo en phosphorus_analyses
            foreach ($request->items as $item) {
                \App\Models\PhosphorusAnalysis::create([
                    'consecutivo_no' => $request->consecutivo_no,
                    'analysis_id' => $analysis->id,
                    'fecha_analisis' => $request->fecha_analisis,
                    'user_id' => auth()->id(),
                    'equipo_utilizado' => $request->equipo_utilizado,
                    'intervalo_metodo' => $request->intervalo_metodo,
                    'analista' => $request->analista,
                    'codigo_interno' => $item['codigo_interno'] ?? null,
                    'peso_muestra' => $item['peso_muestra'] ?? null,
                    'pw' => $item['pw'] ?? null,
                    'v_extractante' => $item['v_extractante'] ?? null,
                    'lectura_blanco' => $item['lectura_blanco'] ?? null,
                    'factor_dilucion' => $item['factor_dilucion'] ?? null,
                    'fosforo_disponible_mg_l' => $item['fosforo_disponible_mg_l'] ?? null,
                    'fosforo_disponible_mg_kg' => $item['fosforo_disponible_mg_kg'] ?? null,
                    'observaciones_item' => $item['observaciones_item'] ?? null,
                ]);
            }

            // Guardar cada fila de controles analíticos en analytical_controls
            foreach ($request->controles_analiticos as $control) {
                \App\Models\AnalyticalControl::create([
                    'analysis_id' => $analysis->id,
                    'valor_referencia' => $control['identificacion'] ?? null,
                    'valor_obtenido' => $control['valor_leido'] ?? null,
                    'recuperacion' => $control['porcentaje_recuperacion'] ?? null,
                    'dpr' => $control['porcentaje_dpr'] ?? null,
                    'estado' => $control['aceptabilidad_error'] ?? null,
                    'observaciones' => null,
                    'analista' => $request->analista,
                ]);
            }

            // Guardar curva de calibración y duplicados si vienen en el request
            if ($request->has('curva_calibracion')) {
                \App\Models\AnalyticalControl::create([
                    'analysis_id' => $analysis->id,
                    'valor_referencia' => 'Curva de calibración',
                    'valor_obtenido' => $request->curva_calibracion,
                    'recuperacion' => null,
                    'dpr' => null,
                    'estado' => null,
                    'observaciones' => null,
                    'analista' => $request->analista,
                ]);
            }
            if ($request->has('duplicado_a')) {
                \App\Models\AnalyticalControl::create([
                    'analysis_id' => $analysis->id,
                    'valor_referencia' => 'Duplicado A',
                    'valor_obtenido' => $request->duplicado_a,
                    'recuperacion' => null,
                    'dpr' => $request->dpr_resultado ?? null,
                    'estado' => $request->dpr_aceptabilidad ?? null,
                    'observaciones' => null,
                    'analista' => $request->analista,
                ]);
            }
            if ($request->has('duplicado_b')) {
                \App\Models\AnalyticalControl::create([
                    'analysis_id' => $analysis->id,
                    'valor_referencia' => 'Duplicado B',
                    'valor_obtenido' => $request->duplicado_b,
                    'recuperacion' => null,
                    'dpr' => $request->dpr_resultado ?? null,
                    'estado' => $request->dpr_aceptabilidad ?? null,
                    'observaciones' => null,
                    'analista' => $request->analista,
                ]);
            }

            $analysis->update(['status' => 'completed']);

            Log::info('Controles analíticos guardados exitosamente:', [
                'user_id' => Auth::id(),
                'analysis_id' => $analysis->id,
            ]);

            return redirect()->route('phosphorus_analysis.index')
                           ->with('success', 'Controles analíticos guardados exitosamente');
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

    public function batchProcess(Request $request)
    {
        $analysisIds = $request->input('analysis_ids', []);
        if (count($analysisIds) === 0 || count($analysisIds) > 10) {
            return redirect()->route('phosphorus_analysis.index')->with('error', 'Debes seleccionar entre 1 y 10 análisis para procesar por lote.');
        }
        $pendingAnalyses = \App\Models\Analysis::with(['process'])
            ->whereIn('id', $analysisIds)
            ->get();
        return view('phosphorus_analyses.batch_process', compact('pendingAnalyses'));
    }

    public function storeBatchProcess(Request $request)
    {
        $analysisIds = $request->input('analysis_ids', []);
        if (count($analysisIds) === 0 || count($analysisIds) > 10) {
            return redirect()->route('phosphorus_analysis.index')->with('error', 'Debes seleccionar entre 1 y 10 análisis para procesar por lote.');
        }
        $itemsEnsayo = $request->input('items_ensayo', []);
        $controlesAnaliticos = $request->input('controles_analiticos', []);
        // Guardar controles analíticos y curva para cada análisis
        foreach ($analysisIds as $analysisId) {
            // Guardar controles analíticos (dos filas)
            foreach ($controlesAnaliticos as $control) {
                \App\Models\AnalyticalControl::create([
                    'analysis_id' => $analysisId,
                    'valor_referencia' => $control['identificacion'] ?? null,
                    'valor_obtenido' => $control['valor_leido'] ?? null,
                    'recuperacion' => $control['porcentaje_recuperacion'] ?? null,
                    'dpr' => $control['porcentaje_dpr'] ?? null,
                    'estado' => $control['aceptabilidad_dpr'] ?? null,
                    'observaciones' => null,
                    'analista' => $request->analista ?? null,
                ]);
            }
            // Guardar curva de calibración como control especial
            \App\Models\AnalyticalControl::create([
                'analysis_id' => $analysisId,
                'valor_referencia' => 'Curva de calibración',
                'valor_obtenido' => '0.995',
                'recuperacion' => null,
                'dpr' => $request->dpr_resultado ?? null,
                'estado' => $request->dpr_aceptabilidad ?? null,
                'observaciones' => null,
                'analista' => $request->analista ?? null,
            ]);
        }
        // Guardar ítems de ensayo
        foreach ($itemsEnsayo as $item) {
            $analysisId = $item['analysis_id'] ?? null;
            if ($analysisId && in_array($analysisId, $analysisIds)) {
                \App\Models\PhosphorusAnalysis::create([
                    'consecutivo_no' => $request->consecutivo_no ?? '',
                    'analysis_id' => $analysisId,
                    'fecha_analisis' => $request->fecha_analisis ?? now(),
                    'user_id' => auth()->id(),
                    'equipo_utilizado' => $request->equipo_utilizado ?? '',
                    'intervalo_metodo' => $request->intervalo_metodo ?? '',
                    'analista' => $request->analista ?? '',
                    'codigo_interno' => $item['codigo_interno'] ?? null,
                    'peso_muestra' => $item['peso_muestra'] ?? null,
                    'pw' => $item['pw'] ?? null,
                    'v_extractante' => $item['v_extractante'] ?? null,
                    'lectura_blanco' => $item['lectura_blanco'] ?? null,
                    'factor_dilucion' => $item['factor_dilucion'] ?? null,
                    'fosforo_disponible_mg_l' => $item['fosforo_disponible_mg_l'] ?? null,
                    'fosforo_disponible_mg_kg' => $item['fosforo_disponible_mg_kg'] ?? null,
                    'observaciones_item' => $item['observaciones_item'] ?? null,
                ]);
                \App\Models\Analysis::where('id', $analysisId)->update(['status' => 'completed']);
            }
        }
        return redirect()->route('phosphorus_analysis.index')->with('success', 'Análisis de Fósforo por lote guardados exitosamente.');
    }
}
