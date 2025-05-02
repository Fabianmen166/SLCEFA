<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\PhAnalysis;
use App\Models\Process;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Add this import
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill; // Add this import for styling

class PhAnalysisController extends Controller
{
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
            $analysis = $process->analyses->firstWhere('service_id', $service->id);
            return !$analysis || $analysis->status === 'pending';
        });
        $completedServices = $servicesToDo->filter(function ($service) use ($process) {
            $analysis = $process->analyses->firstWhere('service_id', $service->id);
            return $analysis && $analysis->status === 'completed';
        });

        return view('process.review_analysis', compact('analysis', 'process', 'servicesToDo', 'pendingServices', 'completedServices'));
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
            'revisado_por' => 'nullable|string|max:255',
            'fecha_revision' => 'nullable|date',
            'aprobado' => 'nullable|in:liberados,retenidos',
            'observaciones_revision' => 'nullable|string',
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
                'analista_id' => Auth::id(),
                'codigo_probeta' => $request->codigo_probeta,
                'codigo_equipo' => $request->codigo_equipo,
                'serial_electrodo' => $request->serial_electrodo,
                'serial_sonda_temperatura' => $request->serial_sonda_temperatura,
                'controles_analiticos' => $controles,
                'precision_analitica' => $precision,
                'items_ensayo' => $request->items_ensayo,
                'observaciones' => $request->observaciones,
                'revisado_por' => $request->revisado_por,
                'fecha_revision' => $request->fecha_revision,
                'aprobado' => $request->aprobado,
                'observaciones_revision' => $request->observaciones_revision,
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

    public function phAnalysis($processId, $serviceId)
    {
        $process = Process::findOrFail($processId);
        $service = Service::findOrFail($serviceId);
        $analysis = Analysis::where('process_id', $processId)
                            ->where('service_id', $serviceId)
                            ->firstOrFail();
        $phAnalysis = PhAnalysis::where('analysis_id', $analysis->id)->first();

        return view('processes.ph_analysis', compact('process', 'service', 'analysis', 'phAnalysis'));
    }

    public function storePhAnalysis(Request $request, $processId, $serviceId)
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
            'revisado_por' => 'nullable|string|nullable',
            'fecha_revision' => 'nullable|date|nullable',
            'aprobado' => 'nullable|in:liberados,retenidos|nullable',
            'observaciones_revision' => 'nullable|string|nullable',
        ]);

        $analysis = Analysis::where('process_id', $processId)
                            ->where('service_id', $serviceId)
                            ->firstOrFail();

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

        $phAnalysis = PhAnalysis::updateOrCreate(
            ['analysis_id' => $analysis->id],
            [
                'consecutivo_no' => $request->consecutivo_no,
                'fecha_analisis' => $request->fecha_analisis,
                'analista_id' => Auth::id(),
                'codigo_probeta' => $request->codigo_probeta,
                'codigo_equipo' => $request->codigo_equipo,
                'serial_electrodo' => $request->serial_electrodo,
                'serial_sonda_temperatura' => $request->serial_sonda_temperatura,
                'controles_analiticos' => $controles,
                'precision_analitica' => $precision,
                'items_ensayo' => $request->items_ensayo,
                'observaciones' => $request->observaciones,
                'revisado_por' => $request->revisado_por,
                'fecha_revision' => $request->fecha_revision,
                'aprobado' => $request->aprobado,
                'observaciones_revision' => $request->observaciones_revision,
                'review_status' => 'pending',
            ]
        );

        $analysis->status = 'completed';
        $analysis->save();

        return redirect()->route('process.technical_index')
                        ->with('success', 'Análisis de pH guardado exitosamente.');
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
            $sheet->setCellValue("D{$row}", $control['valor_esperado']);
            $sheet->setCellValue("E{$row}", $control['error']);
            $sheet->setCellValue("F{$row}", $control['aceptabilidad']);
            // Fixed the styling syntax
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
        // Fixed the styling syntax
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

        $row++;
        $sheet->setCellValue("A{$row}", 'Revisado y auditado');
        $sheet->setCellValue("B{$row}", 'Fecha');
        $sheet->setCellValue("C{$row}", 'Aprobado');
        $row++;
        $sheet->setCellValue("A{$row}", $phAnalysis->revisado_por ?? '');
        $sheet->setCellValue("B{$row}", $phAnalysis->fecha_revision ?? '');
        $sheet->setCellValue("C{$row}", $phAnalysis->aprobado ?? '');

        $writer = new Xlsx($spreadsheet);
        $filename = "Reporte_pH_{$phAnalysis->consecutivo_no}.xlsx";
        $writer->save($filename);
        return response()->download($filename)->deleteFileAfterSend(true);
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
        $row = $row + 2; // Avoid using += to prevent parsing issues
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'ph_analyses_report_' . now()->format('YmdHis') . '.xlsx';
    $writer->save(storage_path('app/public/' . $filename));

    $firstAnalysis = $analyses->first();
    return redirect()->route('ph_analysis.ph_analysis', [$firstAnalysis->process_id, $firstAnalysis->service_id])
                     ->with('success', 'Lote creado. Por favor, complete los análisis de pH.')
                     ->with('batch_file', $filename);
}
}