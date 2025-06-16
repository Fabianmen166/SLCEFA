<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\PhAnalysis;
use App\Models\Process;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PhAnalysisController extends Controller
{
/**
     * Display a listing of pH analyses for technical staff, including pending and returned (not approved) analyses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            Log::info('User accessing PhAnalysisController@index:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);

            // Fetch analyses that are explicitly rejected (approved = 0)
            $phAnalyses = Analysis::with(['process', 'service'])
                                ->where('approved', 0)
                                ->get()
                                ->filter(function ($analysis) {
                                    $hasProcess = $analysis->process !== null;
                                    $hasService = $analysis->service !== null;
                                    if (!$hasProcess || !$hasService) {
                                        Log::warning('Analysis with missing relation:', [
                                            'analysis_id' => $analysis->id,
                                            'process' => $analysis->process ? 'exists' : 'null',
                                            'service' => $analysis->service ? 'exists' : 'null',
                                        ]);
                                    }
                                    return $hasProcess && $hasService;
                                });

            Log::info('Returned analyses loaded for index:', [
                'phAnalyses_count' => $phAnalyses->count(),
                'analysis_ids' => $phAnalyses->pluck('id')->toArray(),
            ]);

            // Fetch processes with pending pH analyses
            $processes = Process::where('status', 'pending')
                ->with([
                    'analyses' => function ($query) {
                        $query->where('status', 'pending')
                              ->whereHas('service', function ($serviceQuery) {
                                  $serviceQuery->where('descripcion', 'like', '%pH%');
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

            return view('ph_analyses.index', compact('phAnalyses', 'processes'));
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('personal_tecnico.dashboard')
                           ->with('error', 'Error al cargar la gestión de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for processing all pending pH analyses across all processes.
     *
     * @return \Illuminate\View\View
     */
    public function processAll()
    {
        try {
            // Fetch all processes with pending pH analyses
            $processes = Process::where('status', 'pending')
                ->with([
                    'analyses' => function ($query) {
                        $query->where('status', 'pending')
                              ->whereHas('service', function ($serviceQuery) {
                                  $serviceQuery->where('descripcion', 'like', '%pH%');
                              })
                              ->with(['service', 'process', 'phAnalysis']);
                    },
                ])
                ->get()
                ->filter(function ($process) {
                    return $process->analyses->isNotEmpty();
                });

            if ($processes->isEmpty()) {
                return redirect()->route('ph_analysis.index')
                               ->with('error', 'No hay análisis de pH pendientes para procesar.');
            }

            // Initialize an array to hold all pending items across all analyses
            $pendingItems = [];
            $pendingAnalyses = collect();
            foreach ($processes as $process) {
                $analyses = $process->analyses;
                foreach ($analyses as $analysis) {
                    $pendingAnalyses->push($analysis);
                    $phAnalysis = $analysis->phAnalysis;
                    if ($phAnalysis && isset($phAnalysis->items_ensayo)) {
                        foreach ($phAnalysis->items_ensayo as $index => $item) {
                            if (!isset($item['valor_leido']) || $item['valor_leido'] === '') {
                                $pendingItems[] = $item + ['analysis_id' => $analysis->id];
                            }
                        }
                    } else {
                        // Add default item if no PhAnalysis exists
                        $pendingItems[] = [
                            'identificacion' => 'Muestra ' . (count($pendingItems) + 1),
                            'peso' => '',
                            'volumen_agua' => '',
                            'temperatura' => '',
                            'valor_leido' => '',
                            'observaciones' => '',
                            'analysis_id' => $analysis->id,
                        ];
                    }
                }
            }

            Log::info('PhAnalysisController@processAll loaded data:', [
                'pending_analyses_count' => $pendingAnalyses->count(),
                'pending_items_count' => count($pendingItems),
            ]);

            return view('ph_analyses.process', compact('pendingAnalyses', 'pendingItems'));
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@processAll: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('ph_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for processing a specific pH analysis with pending items.
     *
     * @param string $processId
     * @param int $serviceId
     * @return \Illuminate\View\View
     */
    public function phAnalysis($processId, $serviceId)
    {
        try {
            $process = Process::where('process_id', $processId)->firstOrFail();
            $service = Service::findOrFail($serviceId);
            $analysis = Analysis::where('process_id', $processId)
                              ->where('service_id', $serviceId)
                              ->firstOrFail();
            $phAnalysis = PhAnalysis::where('analysis_id', $analysis->id)->first();

            // Filtrar ítems pendientes (asumimos que los ítems están en items_ensayo y un ítem está pendiente si no tiene valor_leido)
            $pendingItems = [];
            if ($phAnalysis && isset($phAnalysis->items_ensayo)) {
                foreach ($phAnalysis->items_ensayo as $index => $item) {
                    if (!isset($item['valor_leido']) || $item['valor_leido'] === '') {
                        $pendingItems[$index] = $item;
                    }
                }
            }

            Log::info('PhAnalysisController@phAnalysis loaded data:', [
                'process_id' => $process->id ?? null,
                'process_process_id' => $process->process_id,
                'service_services_id' => $service->services_id,
                'analysis_id' => $analysis->id,
                'phAnalysis_exists' => !is_null($phAnalysis),
                'pending_items_count' => count($pendingItems),
            ]);

            return view('ph_analyses.process', compact('process', 'service', 'analysis', 'phAnalysis', 'pendingItems'));
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@phAnalysis: ' . $e->getMessage(), [
                'processId' => $processId,
                'serviceId' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('ph_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de pH: ' . $e->getMessage());
        }
    }
/**
 * Store a newly created pH analysis in storage.
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function storePhAnalysis(Request $request, $processId, $serviceId)
{
    try {
        Log::info('Storing pH analysis:', [
            'process_id' => $processId,
            'service_id' => $serviceId,
            'user_id' => Auth::id()
        ]);

        $request->validate([
            'items' => 'required|array',
            'items.*.identificacion' => 'required|string',
            'items.*.peso' => 'required|numeric|min:0',
            'items.*.volumen_agua' => 'required|numeric|min:0',
            'items.*.temperatura' => 'required|numeric',
            'items.*.valor_leido' => 'required|numeric',
            'items.*.observaciones' => 'nullable|string'
        ]);

        $process = Process::where('process_id', $processId)->firstOrFail();
        $service = Service::findOrFail($serviceId);
        $analysis = Analysis::where('process_id', $processId)
                          ->where('service_id', $serviceId)
                          ->firstOrFail();

        // Calcular el promedio de los valores leídos
        $valores = collect($request->items)->pluck('valor_leido');
        $promedio = $valores->avg();

        // Crear o actualizar el análisis de pH
        $phAnalysis = PhAnalysis::updateOrCreate(
            ['analysis_id' => $analysis->id],
            [
                'items_ensayo' => $request->items,
                'promedio' => $promedio,
                'status' => 'pending'
            ]
        );

        // Actualizar el estado del análisis
        $analysis->update([
            'status' => 'pending',
            'approved' => 0
        ]);

        Log::info('pH analysis stored successfully:', [
            'analysis_id' => $analysis->id,
            'ph_analysis_id' => $phAnalysis->id,
            'promedio' => $promedio
        ]);

        return redirect()->route('ph_analysis.index')
                       ->with('success', 'Análisis de pH guardado exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error storing pH analysis: ' . $e->getMessage(), [
            'process_id' => $processId,
            'service_id' => $serviceId,
            'stack_trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()
                       ->with('error', 'Error al guardar el análisis de pH: ' . $e->getMessage())
                       ->withInput();
    }
}
    public function downloadPhReport($analysisId)
    {
        $phAnalysis = PhAnalysis::where('analysis_id', $analysisId)->firstOrFail();
        $analyst = \App\Models\User::find($phAnalysis->analista_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LABORATORIO DE CIENCIAS BÁSICAS');
        $sheet->setCellValue('A2', 'PROCEDIMIENTO DETERMINACIÓN DE pH EN SUELOS');
        $sheet->setCellValue('A3', 'FORMATO REPORTE RESULTADOS pH EN SUELOS');
        $sheet->setCellValue('D3', 'Versión: 6');
        $sheet->setCellValue('D4', 'Código: F-PSS-001');
        $sheet->setCellValue('D5', 'Página: 1 de 1');

        $sheet->setCellValue('A6', 'Consecutivo No.:');
        $sheet->setCellValue('B6', $phAnalysis->consecutivo_no);
        $sheet->setCellValue('A7', 'Fecha del análisis:');
        $sheet->setCellValue('B7', $phAnalysis->fecha_analisis);
        $sheet->setCellValue('A8', 'Nombre Analista:');
        $sheet->setCellValue('B8', $analyst->name);
        $sheet->setCellValue('A9', 'Código de la probeta:');
        $sheet->setCellValue('B9', $phAnalysis->codigo_probeta);
        $sheet->setCellValue('A10', 'Código Equipo potenciométrico:');
        $sheet->setCellValue('B10', $phAnalysis->codigo_equipo);
        $sheet->setCellValue('A11', 'Serial del electrodo:');
        $sheet->setCellValue('B11', $phAnalysis->serial_electrodo);
        $sheet->setCellValue('A12', 'Serial sonda de temperatura:');
        $sheet->setCellValue('B12', $phAnalysis->serial_sonda_temperatura);

        $sheet->setCellValue('A14', 'Controles analíticos');
        $sheet->setCellValue('A15', 'Identificación');
        $sheet->setCellValue('B15', 'Lote de identificación');
        $sheet->setCellValue('C15', 'Valor leído (Unidades de pH)');
        $sheet->setCellValue('D15', 'Valor esperado (Unidades de pH)');
        $sheet->setCellValue('E15', '% Error');
        $sheet->setCellValue('F15', 'Aceptabilidad del control');
        $sheet->setCellValue('G15', 'Observaciones');

        $row = 16;
        foreach ($phAnalysis->controles_analiticos as $control) {
            $sheet->setCellValue("A{$row}", $control['identificacion']);
            $sheet->setCellValue("B{$row}", $control['lote']);
            $sheet->setCellValue("C{$row}", $control['valor_leido']);
            $sheet->setCellValue("D{$row}", $control['valor_leido']); // This was changed from valor_esperado to valor_leido
            $sheet->setCellValue("E{$row}", $control['error']);
            $sheet->setCellValue("F{$row}", $control['aceptabilidad']);
            if ($control['aceptabilidad'] == 'No aceptable') {
                $sheet->getStyle("F{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFFF0000'],
                    ],
                ]);
            }
            $row++;
        }

        $row++;
        $sheet->setCellValue("A{$row}", 'Precisión analítica');
        $row++;
        $sheet->setCellValue("A{$row}", 'Identificación de los duplicados A-B');
        $sheet->setCellValue("B{$row}", 'Valor leído (Unidades de pH)');
        $sheet->setCellValue("C{$row}", 'Promedio');
        $sheet->setCellValue("D{$row}", 'Diferencia');
        $sheet->setCellValue("E{$row}", 'Aceptabilidad del control');
        $sheet->setCellValue("F{$row}", 'Observaciones');
        $row++;
        $precision = $phAnalysis->precision_analitica;
        $sheet->setCellValue("A{$row}", $precision['duplicado_a']['identificacion']);
        $sheet->setCellValue("B{$row}", $precision['duplicado_a']['valor_leido']);
        $sheet->setCellValue("C{$row}", $precision['promedio']);
        $sheet->setCellValue("D{$row}", $precision['diferencia']);
        $sheet->setCellValue("E{$row}", $precision['aceptabilidad']);
        if ($precision['aceptabilidad'] == 'No aceptable') {
            $sheet->getStyle("E{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFFF0000'],
                ],
            ]);
        }
        $row++;
        $sheet->setCellValue("A{$row}", $precision['duplicado_b']['identificacion']);
        $sheet->setCellValue("B{$row}", $precision['duplicado_b']['valor_leido']);

        $row++;
        $sheet->setCellValue("A{$row}", 'Ítem de ensayo');
        $row++;
        $sheet->setCellValue("A{$row}", 'Identificación');
        $sheet->setCellValue("B{$row}", 'Peso (g)');
        $sheet->setCellValue("C{$row}", 'Volumen H₂O destilada (mL)');
        $sheet->setCellValue("D{$row}", 'Temperatura (°C)');
        $sheet->setCellValue("E{$row}", 'Valor leído (Unidades de pH)');
        $sheet->setCellValue("F{$row}", 'Observaciones');
        $row++;
        foreach ($phAnalysis->items_ensayo as $item) {
            $sheet->setCellValue("A{$row}", $item['identificacion']);
            $sheet->setCellValue("B{$row}", $item['peso']);
            $sheet->setCellValue("C{$row}", $item['volumen_agua']);
            $sheet->setCellValue("D{$row}", $item['temperatura']);
            $sheet->setCellValue("E{$row}", $item['valor_leido']);
            $sheet->setCellValue("F{$row}", $item['observaciones'] ?? '');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "Reporte_pH_{$phAnalysis->consecutivo_no}.xlsx";
        $writer->save($filename);
        return response()->download($filename)->deleteFileAfterSend(true);
    }
   
    public function indexForReview()
    {
        $phAnalyses = PhAnalysis::with(['analysis.process', 'analysis.service'])
                              ->whereIn('review_status', ['pending', 'approved', 'rejected'])
                              ->orderBy('fecha_analisis', 'desc')
                              ->paginate(10);

        return view('processes.review_index', compact('phAnalyses'));
    }

    public function reviewAnalysis($analysis_id)
    {
        $analysis = Analysis::with('process.quote.customer', 'process.quote.services', 'service', 'phAnalysis')
                          ->findOrFail($analysis_id);
        $process = $analysis->process;

        $servicesToDo = $process->quote->services;
        $pendingServices = $servicesToDo->filter(function ($service) use ($process) {
            $analysis = $process->analyses->firstWhere('service_id', $service->services_id);
            return !$analysis || $analysis->status === 'pending';
        });
        $completedServices = $servicesToDo->filter(function ($service) use ($process) {
            $analysis = $process->analyses->firstWhere('service_id', $service->services_id);
            return $analysis && $analysis->status === 'completed';
        });

        return view('processes.review_analysis', compact('analysis', 'process', 'servicesToDo', 'pendingServices', 'completedServices'));
    }

    public function storeReview(Request $request, $phAnalysisId)
    {
        $request->validate([
            'review_status' => 'required|in:approved,rejected',
            'review_observations' => 'nullable|string',
        ]);

        try {
            $phAnalysis = PhAnalysis::findOrFail($phAnalysisId);
            $phAnalysis->update([
                'review_status' => $request->review_status,
                'reviewed_by' => Auth::user()->name,
                'reviewer_role' => Auth::user()->role,
                'review_date' => now(),
                'review_observations' => $request->review_observations,
            ]);

            if ($request->review_status === 'approved') {
                $phAnalysis->analysis->update(['approved' => true]);
            }

            return redirect()->route('ph_analysis.review_index')->with('success', 'Revisión registrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al registrar revisión: ' . $e->getMessage());
            return back()->with('error', 'Error al registrar la revisión: ' . $e->getMessage())->withInput();
        }
    }

    public function editAnalysis($phAnalysisId)
    {
        $phAnalysis = PhAnalysis::with(['analysis.process', 'analysis.service'])->findOrFail($phAnalysisId);

        if ($phAnalysis->review_status !== 'rejected') {
            return redirect()->route('process.technical_index')
                            ->with('error', 'Solo se pueden corregir análisis rechazados.');
        }

        return view('processes.edit_ph_analysis', compact('phAnalysis'));
    }

    public function updateAnalysis(Request $request, $phAnalysisId)
    {
        $request->validate([
            'consecutivo_no' => 'required|string|max:255',
            'fecha_analisis' => 'required|date',
            'codigo_probeta' => 'required|string|max:255',
            'codigo_equipo' => 'required|string|max:255',
            'serial_electrodo' => 'required|string|max:255',
            'serial_sonda_temperatura' => 'required|string|max:255',
            'controles_analiticos' => 'required|array',
            'precision_analitica' => 'required|array',
            'items_ensayo' => 'required|array',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $phAnalysis = PhAnalysis::findOrFail($phAnalysisId);

            $controles = $request->controles_analiticos;
            foreach ($controles as &$control) {
                $valorLeido = floatval($control['valor_leido']);
                $valorEsperado = floatval($control['valor_esperado']);
                $control['error'] = ($valorEsperado != 0) ? abs(($valorLeido - $valorEsperado) / $valorEsperado) * 100 : 0;
                $control['aceptabilidad'] = ($control['error'] <= 5) ? 'Aceptable' : 'No aceptable';
            }

            $precision = $request->precision_analitica;
            $duplicadoA = floatval($precision['duplicado_a']['valor_leido']);
            $duplicadoB = floatval($precision['duplicado_b']['valor_leido']);
            $precision['promedio'] = ($duplicadoA + $duplicadoB) / 2;
            $precision['diferencia'] = abs($duplicadoA - $duplicadoB);
            $precision['aceptabilidad'] = ($precision['diferencia'] <= 0.5) ? 'Aceptable' : 'No aceptable';

            $phAnalysis->update([
                'consecutivo_no' => $request->consecutivo_no,
                'fecha_analisis' => $request->fecha_analisis,
                'user_id' => Auth::id(),
                'codigo_probeta' => $request->codigo_probeta,
                'codigo_equipo' => $request->codigo_equipo,
                'serial_electrodo' => $request->serial_electrodo,
                'serial_sonda_temperatura' => $request->serial_sonda_temperatura,
                'controles_analiticos' => $controles,
                'precision_analitica' => $precision,
                'items_ensayo' => $request->items_ensayo,
                'observaciones' => $request->observaciones,
                'review_status' => 'pending',
                'reviewed_by' => null,
                'reviewer_role' => null,
                'review_date' => null,
                'review_observations' => null,
            ]);

            return redirect()->route('process.technical_index')
                            ->with('success', 'Análisis corregido exitosamente. Pendiente de nueva revisión.');
        } catch (\Exception $e) {
            Log::error('Error al corregir análisis: ' . $e->getMessage());
            return back()->with('error', 'Error al corregir el análisis: ' . $e->getMessage())->withInput();
        }
    }

    
   
    public function batchPhAnalysis(Request $request)
    {
        $request->validate([
            'analyses' => 'required|array|max:20',
            'analyses.*' => 'exists:analyses,id',
        ]);

        $analysisIds = $request->input('analyses');
        $analyses = Analysis::whereIn('id', $analysisIds)
                          ->with(['process', 'service'])
                          ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LABORATORIO DE CIENCIAS BÁSICAS');
        $sheet->setCellValue('A2', 'REPORTE DE ANÁLISIS DE pH EN SUELOS - LOTE');
        $sheet->setCellValue('D2', 'Versión: 6');
        $sheet->setCellValue('D3', 'Código: F-PSS-001');

        $row = 5;
        foreach ($analyses as $index => $analysis) {
            $sheet->setCellValue('A' . $row, 'Análisis #' . ($index + 1));
            $sheet->setCellValue('B' . $row, 'Proceso: ' . $analysis->process->process_id);
            $sheet->setCellValue('C' . $row, 'Servicio: ' . $analysis->service->descripcion);
            $sheet->setCellValue('D' . $row, 'Estado: Pendiente de análisis');
            $row = $row + 2;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'ph_analyses_report_' . now()->format('YmdHis') . '.xlsx';
        $writer->save(storage_path('app/public/' . $filename));

        $firstAnalysis = $analyses->first();
        return redirect()->route('ph_analysis.ph_analysis', [$firstAnalysis->process_id, $firstAnalysis->service_id])
                       ->with('success', 'Lote creado. Por favor, complete los análisis de pH.')
                       ->with('batch_file', $filename);
    }
    
    public function batchProcess(Request $request)
    {
        try {
            $analysisIds = $request->input('analysis_ids');

            $query = Analysis::where('status', 'pending')
                ->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%pH%');
                })
                ->with(['service', 'process', 'phAnalysis']);

            if (!empty($analysisIds)) {
                $query->whereIn('id', $analysisIds);
            }

            $pendingAnalyses = $query->get();

            if ($pendingAnalyses->isEmpty()) {
                return redirect()->route('ph_analysis.index')
                               ->with('error', 'No hay análisis de pH pendientes para procesar o los seleccionados ya fueron procesados.');
            }

            $pendingItems = collect(); // Initialize an empty collection for pending items

            // Asegurarse de que cada análisis tenga al menos un item de ensayo por defecto si no existe o está vacío
            foreach ($pendingAnalyses as $analysis) {
                if (empty($analysis->phAnalysis->items_ensayo) || collect($analysis->phAnalysis->items_ensayo)->every(fn($i) => isset($i['valor_leido']) && $i['valor_leido'] !== '')) {
                    // Add a default item for this analysis and attach its analysis_id
                    $pendingItems->push([
                        'identificacion' => 'Muestra ' . ($pendingAnalyses->search($analysis) + 1),
                        'peso' => '',
                        'volumen_agua' => '',
                        'temperatura' => '',
                        'valor_leido' => '',
                        'observaciones' => '',
                        'analysis_id' => $analysis->id, // Important for linking back
                    ]);
                } else {
                    // Collect existing items from this analysis and attach its analysis_id
                    foreach ($analysis->phAnalysis->items_ensayo as $item) {
                        $pendingItems->push($item + ['analysis_id' => $analysis->id]);
                    }
                }
            }

            Log::info('PhAnalysisController@batchProcess loaded data:', [
                'pending_analyses_count' => $pendingAnalyses->count(),
                'pending_items_count' => $pendingItems->count(),
                'analysis_ids_requested' => $analysisIds,
            ]);

            return view('ph_analyses.process', compact('pendingAnalyses', 'pendingItems'));
        } catch (\Exception $e) {
            Log::error('Error in PhAnalysisController@batchProcess: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('ph_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de pH: ' . $e->getMessage());
        }
    }

    /**
     * Store a batch of pH analyses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeBatch(Request $request)
    {
        try {
            Log::info('Storing batch pH analyses:', ['user_id' => Auth::id()]);

            $request->validate([
                'analyses' => 'required|array',
                'analyses.*.analysis_id' => 'required|exists:analyses,id',
                'consecutivo_no' => 'required|string|max:255',
                'fecha_analisis' => 'required|date',
                'codigo_probeta' => 'required|string|max:255',
                'codigo_equipo' => 'required|string|max:255',
                'serial_electrodo' => 'required|string|max:255',
                'serial_sonda_temperatura' => 'required|string|max:255',
                'controles_analiticos' => 'required|array',
                'controles_analiticos.*.identificacion' => 'required|string',
                'controles_analiticos.*.lote' => 'required|string',
                'controles_analiticos.*.valor_leido' => 'required|numeric',
                'controles_analiticos.*.valor_esperado' => 'required|numeric',
                'controles_analiticos.*.observaciones' => 'nullable|string',
                'items' => 'required|array',
                'items.*.identificacion' => 'required|string',
                'items.*.peso' => 'required|numeric|min:0',
                'items.*.volumen_agua' => 'required|numeric|min:0',
                'items.*.temperatura' => 'required|numeric',
                'items.*.valor_leido' => 'required|numeric',
                'items.*.observaciones' => 'nullable|string'
            ]);

            $consecutivo_no = $request->input('consecutivo_no');
            $fecha_analisis = $request->input('fecha_analisis');
            $codigo_probeta = $request->input('codigo_probeta');
            $codigo_equipo = $request->input('codigo_equipo');
            $serial_electrodo = $request->input('serial_electrodo');
            $serial_sonda_temperatura = $request->input('serial_sonda_temperatura');
            $controles_analiticos = $request->input('controles_analiticos');
            $items_ensayo = $request->input('items');
            $user_id = Auth::id();

            // Calculate error and acceptability for analytical controls
            foreach ($controles_analiticos as &$control) {
                $valorLeido = floatval($control['valor_leido']);
                $valorEsperado = floatval($control['valor_esperado']);
                $control['error'] = ($valorEsperado != 0) ? abs(($valorLeido - $valorEsperado) / $valorEsperado) * 100 : 0;
                $control['aceptabilidad'] = ($control['error'] <= 5) ? 'Aceptable' : 'No aceptable';
            }

            // Calculate average of sample values
            $promedio_muestra = collect($items_ensayo)->avg('valor_leido');

            foreach ($request->input('analyses') as $analysisData) {
                $analysisId = $analysisData['analysis_id'];
                $analysis = Analysis::findOrFail($analysisId);

                PhAnalysis::updateOrCreate(
                    ['analysis_id' => $analysisId],
                    [
                        'consecutivo_no' => $consecutivo_no,
                        'fecha_analisis' => $fecha_analisis,
                        'user_id' => Auth::id(),
                        'codigo_probeta' => $codigo_probeta,
                        'codigo_equipo' => $codigo_equipo,
                        'serial_electrodo' => $serial_electrodo,
                        'serial_sonda_temperatura' => $serial_sonda_temperatura,
                        'controles_analiticos' => $controles_analiticos,
                        'precision_analitica' => [
                            'duplicado_a' => [
                                'identificacion' => 'Duplicado A',
                                'valor_leido' => 0,
                            ],
                            'duplicado_b' => [
                                'identificacion' => 'Duplicado B',
                                'valor_leido' => 0,
                            ],
                            'promedio' => 0,
                            'diferencia' => 0,
                            'aceptabilidad' => 'Aceptable'
                        ],
                        'items_ensayo' => $items_ensayo,
                        'promedio_muestra' => $promedio_muestra,
                        'observaciones_generales' => $request->input('observaciones_generales'),
                        'status' => 'completed',
                        'review_status' => 'pending',
                    ]
                );

                $analysis->update([
                    'status' => 'completed',
                    'approved' => 0,
                ]);
            }

            Log::info('Batch pH analyses stored successfully.');
            return redirect()->route('ph_analysis.index')->with('success', 'Lote de análisis de pH guardado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error storing batch pH analyses: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return redirect()->back()->with('error', 'Error al guardar el lote de análisis de pH: ' . $e->getMessage())->withInput();
        }
    }
}