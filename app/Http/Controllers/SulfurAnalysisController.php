<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Process;
use App\Models\SulfurAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SulfurAnalysisController extends Controller
{
    public function index()
    {
        try {
            $sulfurAnalyses = SulfurAnalysis::with(['analysis.process.quote.customer', 'user'])
                ->get()
                ->filter(function ($analysis) {
                    return $analysis->analysis->status === 'pending';
                });

            $processes = Process::where('status', 'pending')
                ->with([
                    'analyses' => function ($query) {
                        $query->where('status', 'pending')
                              ->whereHas('service', function ($serviceQuery) {
                                  $serviceQuery->where('descripcion', 'like', '%Azufre%');
                              })
                              ->with('service');
                    },
                ])
                ->get()
                ->filter(function ($process) {
                    return $process->analyses->isNotEmpty();
                });

            return view('sulfur_analyses.index', compact('sulfurAnalyses', 'processes'));
        } catch (\Exception $e) {
            Log::error('Error in SulfurAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('personal_tecnico.dashboard')
                           ->with('error', 'Error al cargar la gestión de análisis de azufre: ' . $e->getMessage());
        }
    }

    public function sulfurAnalysis($processId, $serviceId)
    {
        $process = Process::with(['quote.customer', 'analyses.service'])
            ->findOrFail($processId);
        $analysis = Analysis::where('process_id', $processId)
            ->where('service_id', $serviceId)
            ->firstOrFail();
        return view('sulfur_analyses.process', compact('process', 'analysis'));
    }

    public function storeSulfurAnalysis(Request $request, $processId, $serviceId)
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
                'items.*.sulfur_disponible_mg_l' => 'nullable|numeric',
                'items.*.sulfur_disponible_mg_kg' => 'nullable|numeric',
                'items.*.observaciones_item' => 'nullable|string',
            ]);

            $analysis = Analysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->firstOrFail();

            foreach ($request->items as $item) {
                SulfurAnalysis::create([
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
                    'sulfur_disponible_mg_l' => $item['sulfur_disponible_mg_l'] ?? null,
                    'sulfur_disponible_mg_kg' => $item['sulfur_disponible_mg_kg'] ?? null,
                    'observaciones_item' => $item['observaciones_item'] ?? null,
                ]);
            }

            $analysis->update(['status' => 'completed']);

            return redirect()->route('sulfur_analysis.index')
                           ->with('success', 'Ítems de ensayo de azufre guardados exitosamente');
        } catch (\Exception $e) {
            Log::error('Error in SulfurAnalysisController@storeSulfurAnalysis: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'process_id' => $processId,
                'service_id' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('sulfur_analysis.index')
                           ->with('error', 'Error al guardar el análisis de azufre: ' . $e->getMessage());
        }
    }

    public function batchProcess(Request $request)
    {
        $ids = $request->input('analysis_ids');
        if (!$ids) {
            return redirect()->route('sulfur_analysis.index')->with('error', 'No se seleccionaron análisis para procesar.');
        }
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $pendingAnalyses = \App\Models\Analysis::whereIn('id', $ids)->with(['process.quote.customer', 'service'])->get();
        if ($pendingAnalyses->isEmpty()) {
            return redirect()->route('sulfur_analysis.index')->with('error', 'No hay análisis de azufre pendientes para procesar.');
        }
        return view('sulfur_analyses.batch_process', compact('pendingAnalyses'));
    }

    public function storeBatchProcess(Request $request)
    {
        $request->validate([
            'consecutivo_no' => 'required|string',
            'fecha_analisis' => 'required|date',
            'equipo_utilizado' => 'required|string',
            'intervalo_metodo' => 'required|string',
            'analista' => 'required|string',
            'items_ensayo' => 'required|array',
            'items_ensayo.*.analysis_id' => 'required|integer',
            'items_ensayo.*.codigo_interno' => 'nullable|string',
            'items_ensayo.*.peso_muestra' => 'nullable|numeric',
            'items_ensayo.*.pw' => 'nullable|numeric',
            'items_ensayo.*.v_extractante' => 'nullable|numeric',
            'items_ensayo.*.lectura_blanco' => 'nullable|numeric',
            'items_ensayo.*.factor_dilucion' => 'nullable|numeric',
            'items_ensayo.*.sulfur_disponible_mg_l' => 'nullable|numeric',
            'items_ensayo.*.sulfur_disponible_mg_kg' => 'nullable|numeric',
            'items_ensayo.*.observaciones_item' => 'nullable|string',
        ]);
        try {
            foreach ($request->items_ensayo as $item) {
                SulfurAnalysis::create([
                    'consecutivo_no' => $request->consecutivo_no,
                    'analysis_id' => $item['analysis_id'],
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
                    'sulfur_disponible_mg_l' => $item['sulfur_disponible_mg_l'] ?? null,
                    'sulfur_disponible_mg_kg' => $item['sulfur_disponible_mg_kg'] ?? null,
                    'observaciones_item' => $item['observaciones_item'] ?? null,
                ]);
                // Marcar análisis como completado
                $analysis = \App\Models\Analysis::find($item['analysis_id']);
                if ($analysis) {
                    $analysis->update(['status' => 'completed']);
                }
            }
            return redirect()->route('sulfur_analysis.index')->with('success', 'Análisis de azufre por lote guardados exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error en storeBatchProcess de azufre: ' . $e->getMessage());
            return redirect()->route('sulfur_analysis.index')->with('error', 'Error al guardar el análisis de azufre por lote: ' . $e->getMessage());
        }
    }
} 