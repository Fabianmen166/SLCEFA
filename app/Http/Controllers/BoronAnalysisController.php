<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Process;
use App\Models\BoronAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BoronAnalysisController extends Controller
{
    public function index()
    {
        try {
            Log::info('User accessing boron analysis index:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);

            $boronAnalyses = BoronAnalysis::with(['analysis.process.quote.customer', 'user'])
                ->get()
                ->filter(function ($analysis) {
                    return $analysis->analysis->status === 'pending';
                });

            Log::info('Returned analyses loaded for index:', [
                'boronAnalyses_count' => $boronAnalyses->count(),
                'analysis_ids' => $boronAnalyses->pluck('id')->toArray(),
            ]);

            $processes = Process::where('status', 'pending')
                ->with([
                    'analyses' => function ($query) {
                        $query->where('status', 'pending')
                              ->whereHas('service', function ($serviceQuery) {
                                  $serviceQuery->where('descripcion', 'like', '%Boro%');
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

            return view('boron_analyses.index', compact('boronAnalyses', 'processes'));
        } catch (\Exception $e) {
            Log::error('Error in BoronAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('personal_tecnico.dashboard')
                           ->with('error', 'Error al cargar la gestión de análisis de boro: ' . $e->getMessage());
        }
    }

    public function boronAnalysis($processId, $serviceId, Request $request)
    {
        // Soporta selección múltiple por analysis_ids[] (POST o GET)
        $analysisIds = $request->input('analysis_ids', []);
        if (is_string($analysisIds)) {
            $analysisIds = json_decode($analysisIds, true);
        }
        if (!empty($analysisIds)) {
            $analyses = \App\Models\Analysis::with(['process'])
                ->whereIn('id', $analysisIds)
                ->where('status', 'pending')
                ->whereDoesntHave('boronAnalysis')
                ->get();
            if ($analyses->isEmpty()) {
                return redirect()->route('boron_analysis.index')->with('error', 'No hay análisis de Boro pendientes para procesar.');
            }
            return view('boron_analyses.process', ['analyses' => $analyses]);
        } else {
            // Flujo normal para uno solo
            $process = \App\Models\Process::with(['quote.customer', 'analyses.service'])
                ->findOrFail($processId);
            $analysis = \App\Models\Analysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->firstOrFail();
            return view('boron_analyses.process', compact('process', 'analysis'));
        }
    }

    public function storeBoronAnalysis(Request $request, $processId, $serviceId)
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
                'items.*.boron_disponible_mg_l' => 'nullable|numeric',
                'items.*.boron_disponible_mg_kg' => 'nullable|numeric',
                'items.*.observaciones_item' => 'nullable|string',
            ]);

            $analysis = Analysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->firstOrFail();

            // Guardar ítems de ensayo en boron_analyses
            foreach ($request->items as $item) {
                BoronAnalysis::create([
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
                    'boron_disponible_mg_l' => $item['boron_disponible_mg_l'] ?? null,
                    'boron_disponible_mg_kg' => $item['boron_disponible_mg_kg'] ?? null,
                    'observaciones_item' => $item['observaciones_item'] ?? null,
                ]);
            }

            $analysis->update(['status' => 'completed']);

            Log::info('Ítems de ensayo de boro guardados exitosamente:', [
                'user_id' => Auth::id(),
                'analysis_id' => $analysis->id,
            ]);

            return redirect()->route('boron_analysis.index')
                           ->with('success', 'Ítems de ensayo de boro guardados exitosamente');
        } catch (\Exception $e) {
            Log::error('Error in BoronAnalysisController@storeBoronAnalysis: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'service_id' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('boron_analysis.index')
                           ->with('error', 'Error al guardar el análisis de boro: ' . $e->getMessage());
        }
    }

    public function batchProcess(Request $request)
    {
        $analysisIds = $request->input('analysis_ids', []);
        if (count($analysisIds) === 0 || count($analysisIds) > 10) {
            return redirect()->route('boron_analysis.index')->with('error', 'Debes seleccionar entre 1 y 10 análisis para procesar por lote.');
        }
        $pendingAnalyses = \App\Models\Analysis::with(['process'])
            ->whereIn('id', $analysisIds)
            //->where('status', 'pending') // Quitado para permitir cualquier estado
            ->get();
        return view('boron_analyses.batch_process', compact('pendingAnalyses'));
    }

    public function storeBatchProcess(Request $request)
    {
        $analysisIds = $request->input('analysis_ids', []);
        if (count($analysisIds) === 0 || count($analysisIds) > 10) {
            return redirect()->route('boron_analysis.index')->with('error', 'Debes seleccionar entre 1 y 10 análisis para procesar por lote.');
        }
        $itemsEnsayo = $request->input('items_ensayo', []);
        $controlesAnaliticos = $request->input('controles_analiticos', []);
        foreach ($itemsEnsayo as $item) {
            $analysisId = $item['analysis_id'] ?? null;
            if ($analysisId && in_array($analysisId, $analysisIds)) {
                \App\Models\BoronAnalysis::create([
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
                    'v_aliquota' => $item['v_aliquota'] ?? null,
                    'factor_dilucion' => $item['factor_dilucion'] ?? null,
                    'boron_disponible_mg_l' => $item['boron_disponible_mg_l'] ?? null,
                    'boron_disponible_mg_kg' => $item['boron_disponible_mg_kg'] ?? null,
                    'observaciones_item' => $item['observaciones_item'] ?? null
                ]);
                \App\Models\Analysis::where('id', $analysisId)->update(['status' => 'completed']);
            }
        }
        return redirect()->route('boron_analysis.index')->with('success', 'Análisis de Boro por lote guardados exitosamente.');
    }
} 