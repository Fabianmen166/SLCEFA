<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\HumidityAnalysis;
use App\Models\AnalyticalControl; // Cambiado a AnalyticalControl
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class HumidityAnalysisController extends Controller
{
    public function index()
    {
        try {
            Log::info('Usuario accede a index de análisis de humedad', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role ?? 'N/A'
            ]);

            $humidityAnalyses = Analysis::with(['process', 'service'])
                ->where('approved', 0)
                ->whereHas('service', function ($query) {
                    $query->where('descripcion', 'like', '%Humedad%');
                })
                ->whereHas('process')
                ->get();

            $processes = Process::where('status', 'pending')
                ->with(['analyses' => function ($query) {
                    $query->where('status', 'pending')
                        ->whereHas('service', function ($q) {
                            $q->where('descripcion', 'like', '%Humedad%');
                        })
                        ->with('service');
                }])->get();

            return view('humidity_analyses.index', compact('humidityAnalyses', 'processes'));
        } catch (\Exception $e) {
            Log::error('Error al cargar el índice de análisis de humedad: ' . $e->getMessage());
            return back()->with('error', 'No se pudo cargar el listado.');
        }
    }

    public function humidityAnalysis($processId, $serviceId)
    {
        try {
            $process = Process::with(['quote.customer', 'analyses.service'])
                ->findOrFail($processId);

            $humidityAnalysis = Analysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->firstOrFail();

            return view('humidity_analyses.process', [
                'process' => $process,
                'humidityAnalysis' => $humidityAnalysis,
                'serviceId' => $serviceId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar el formulario de análisis: ' . $e->getMessage());
            return back()->with('error', 'No se pudo cargar el formulario.');
        }
    }

    public function storeHumidityAnalysis(Request $request, $processId, $serviceId)
    {
        try {
            // Validación completa de todos los campos
            $validated = $request->validate([
                'fecha_analisis' => 'required|date',
                'hora_ingreso_horno' => 'required',
                'hora_salida_horno' => 'required',
                'temperatura_horno' => 'required|numeric',
                'nombre_metodo' => 'required|string|max:255',
                'intervalo_metodo' => 'required|string|max:255',
                'equipo_utilizado' => 'required|string|max:255',
                'unidades_reporte_equipo' => 'required|string|max:255',
                'resolucion_instrumental' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
                'fecha_fin_analisis' => 'nullable|date',
                'codigo_interno' => 'nullable|string|max:255',
                'peso_capsula' => 'nullable|numeric',
                'peso_muestra' => 'nullable|numeric',
                'peso_capsula_muestra_humedad' => 'nullable|numeric',
                'peso_capsula_muestra_seca' => 'nullable|numeric',
                'porcentaje_humedad' => 'nullable|numeric',
                'consecutivo_no' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
                
                // Validación para los controles analíticos
                'controles_analiticos' => 'nullable|array',
                'controles_analiticos.masa_suelo' => 'nullable|numeric',
                'controles_analiticos.masa_agua' => 'nullable|numeric',
                'controles_analiticos.masa_suelo_seco' => 'nullable|numeric',
                'controles_analiticos.humedad_fortificada_teorica' => 'nullable|numeric',
                'controles_analiticos.humedad_obtenida' => 'nullable|numeric',
                'controles_analiticos.humedad_fortificada' => 'nullable|numeric',
                'controles_analiticos.recuperacion' => 'nullable|numeric',
                'controles_analiticos.valor_referencia' => 'nullable|numeric',
                'controles_analiticos.valor_obtenido' => 'nullable|numeric',
                'controles_analiticos.blanco_metodo' => 'nullable|numeric',
                'controles_analiticos.resultado' => 'nullable|string|max:255',
                'controles_analiticos.lcm' => 'nullable|string|max:255',
                'controles_analiticos.rango_metodo' => 'nullable|string',
                'controles_analiticos.humedad_replica_1' => 'nullable|numeric',
                'controles_analiticos.humedad_replica_2' => 'nullable|numeric',
                'controles_analiticos.dpr' => 'nullable|numeric',
                'controles_analiticos.identificacion_mf' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
                'controles_analiticos.identificacion_mr' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
                'controles_analiticos.identificacion_dm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',
                'controles_analiticos.identificacion_bm' => 'nullable|regex:/^[A-Za-z0-9\-]+$/',

                'controles_analiticos.aceptable' => 'nullable|string|in:aceptable,no_aceptable',
                'controles_analiticos.observaciones' => 'nullable|string',
               
            ], [
                'fecha_analisis.required' => 'El campo Fecha del análisis es obligatorio.',
                'hora_ingreso_horno.required' => 'La Hora de ingreso al horno es obligatoria.',
                'hora_salida_horno.required' => 'La Hora de salida del horno es obligatoria.',
                'temperatura_horno.required' => 'La Temperatura del horno es obligatoria.',
                'temperatura_horno.numeric' => 'La Temperatura debe ser un número.',
                'nombre_metodo.required' => 'El Nombre del método es obligatorio.',
                'intervalo_metodo.required' => 'El Intervalo del método es obligatorio.',
                'equipo_utilizado.required' => 'El campo Equipo utilizado es obligatorio.',
                'unidades_reporte_equipo.required' => 'Las Unidades de reporte del equipo son obligatorias.',
            ]);

            // Iniciar transacción para asegurar la integridad de los datos
            DB::beginTransaction();

            try {
                // Obtener el análisis principal
                $analysis = Analysis::where('process_id', $processId)
                    ->where('service_id', $serviceId)
                    ->firstOrFail();

                // Preparar datos para humidity_analysis
                $humidityData = [
                    'analysis_id' => $analysis->id,
                    'user_id' => auth()->id(),
                    'consecutivo_no' => $validated['consecutivo_no'],
                    'fecha_analisis' => $validated['fecha_analisis'],
                    'hora_ingreso_horno' => $validated['hora_ingreso_horno'],
                    'hora_salida_horno' => $validated['hora_salida_horno'],
                    'temperatura_horno' => $validated['temperatura_horno'],
                    'nombre_metodo' => $validated['nombre_metodo'],
                    'intervalo_metodo' => $validated['intervalo_metodo'],
                    'equipo_utilizado' => $validated['equipo_utilizado'],
                    'unidades_reporte_equipo' => $validated['unidades_reporte_equipo'],
                    'resolucion_instrumental' => $validated['resolucion_instrumental'] ?? null,
                    'codigo_interno' => $validated['codigo_interno'] ?? null,
                    'peso_capsula' => $validated['peso_capsula'] ?? null,
                    'peso_muestra' => $validated['peso_muestra'] ?? null,
                    'peso_capsula_muestra_humedad' => $validated['peso_capsula_muestra_humedad'] ?? null,
                    'peso_capsula_muestra_seca' => $validated['peso_capsula_muestra_seca'] ?? null,
                    'porcentaje_humedad' => $validated['porcentaje_humedad'] ?? null,
                    'observaciones' => $validated['observaciones'] ?? null,
                ];

                // Crear el registro en humidity_analyses
                $humidityAnalysis = HumidityAnalysis::create($humidityData);

                // Crear controles analíticos si existen
                if (!empty($validated['controles_analiticos'])) {
                    $analyticalData = [
                        'analysis_id' => $analysis->id,
                        'masa_suelo' => $validated['controles_analiticos']['masa_suelo'] ?? null,
                        'masa_agua' => $validated['controles_analiticos']['masa_agua'] ?? null,
                        'masa_suelo_seco' => $validated['controles_analiticos']['masa_suelo_seco'] ?? null,
                        'humedad_fortificada_teorica' => $validated['controles_analiticos']['humedad_fortificada_teorica'] ?? null,
                        'humedad_obtenida' => $validated['controles_analiticos']['humedad_obtenida'] ?? null,
                        'humedad_fortificada' => $validated['controles_analiticos']['humedad_fortificada'] ?? null,
                        'recuperacion' => $validated['controles_analiticos']['recuperacion'] ?? null,
                        'valor_referencia' => $validated['controles_analiticos']['valor_referencia'] ?? null,
                        'valor_obtenido' => $validated['controles_analiticos']['valor_obtenido'] ?? null,
                        'blanco_metodo' => $validated['controles_analiticos']['blanco_metodo'] ?? null,
                        'resultado' => $validated['controles_analiticos']['resultado'] ?? null,
                        'limite_cuantificacion_metodo' => $validated['controles_analiticos']['lcm'] ?? null,
                        'rango_metodo' => $validated['controles_analiticos']['rango_metodo'] ?? null,
                        'humedad_replica_1' => $validated['controles_analiticos']['humedad_replica_1'] ?? null,
                        'humedad_replica_2' => $validated['controles_analiticos']['humedad_replica_2'] ?? null,
                        'dpr' => $validated['controles_analiticos']['dpr'] ?? null,
                        'identificacion_mf' => $validated['controles_analiticos']['identificacion_mf'] ?? null,
                        'identificacion_mr' => $validated['controles_analiticos']['identificacion_mr'] ?? null,
                        'identificacion_dm' => $validated['controles_analiticos']['identificacion_dm'] ?? null,
                        'identificacion_bm' => $validated['controles_analiticos']['identificacion_bm'] ?? null,
                       

                        'estado' => isset($validated['controles_analiticos']['aceptable']) ? 
                                      ($validated['controles_analiticos']['aceptable'] === 'aceptable' ? 'Aceptable' : 'No Aceptable') : null,
                        'observaciones' => $validated['controles_analiticos']['observaciones'] ?? null,
                    ];

                    // Crear el registro en analytical_controls usando el modelo correcto
                    AnalyticalControl::create($analyticalData);
                }

                // Confirmar la transacción
                DB::commit();

                return redirect()->route('humidity_analysis.index')
                    ->with('success', 'Análisis de humedad registrado correctamente.');

            } catch (\Exception $e) {
                // Revertir la transacción en caso de error
                DB::rollBack();
                Log::error('Error al guardar análisis de humedad (transacción): ' . $e->getMessage());
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error al guardar análisis de humedad: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Hubo un error al guardar el análisis de humedad: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $humidityAnalysis = HumidityAnalysis::findOrFail($id);
        return view('humidity_analyses.edit', compact('humidityAnalysis'));
    }

    public function update(Request $request, $id)
    {
        $humidityAnalysis = HumidityAnalysis::findOrFail($id);
        $humidityAnalysis->update($request->all());

        return redirect()->route('humidity_analysis.index')
            ->with('success', 'Análisis actualizado.');
    }

    public function review(Request $request, $id)
    {
        $humidityAnalysis = HumidityAnalysis::findOrFail($id);

        $humidityAnalysis->update([
            'review_status' => $request->review_status,
            'reviewed_by' => Auth::id(),
            'reviewer_role' => Auth::user()->role,
            'review_date' => now(),
            'review_observations' => $request->review_observations,
        ]);

        return redirect()->route('humidity_analysis.index')
            ->with('success', 'Revisión registrada.');
    }

    public function destroy($id)
    {
        $humidityAnalysis = HumidityAnalysis::findOrFail($id);
        $humidityAnalysis->delete();

        return back()->with('success', 'Análisis eliminado.');
    }
}