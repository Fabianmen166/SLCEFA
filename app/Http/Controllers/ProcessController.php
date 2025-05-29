<?php
namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\Quote;
use App\Models\Service;
use App\Models\Analysis;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\ServiceProcessDetail;

class ProcessController extends Controller
{
    public function index()
    {
        $processes = Process::with(['quote.quoteServices.service', 'quote.quoteServices.servicePackage'])
            ->where('status', 'pending')
            ->get();

        return view('processes.index', compact('processes'));
    }

    public function show(Process $process)
    {
        $process->load(['serviceProcessDetails.quoteService']);
        return view('processes.show', [
            'process' => $process
                            ]);
                        }

/**
     * Display a listing of analyses pending review with inline approval.
     */
    public function indexForReview()
    {
        // Obtener todos los análisis completados pero no aprobados, con sus relaciones
        $analyses = Analysis::with(['process', 'service', 'phAnalysis.user', 'conductivityAnalysis.user'])
                          ->where('status', 'completed')
                          ->where('approved', false)
                          ->get()
                          ->map(function ($analysis) {
                              // Determinar qué tipo de análisis es (pH o Conductividad)
                              $subAnalysis = $analysis->phAnalysis ?? $analysis->conductivityAnalysis;

                              if ($subAnalysis) {
                                  $analysis->analysis_date = $subAnalysis->fecha_analisis;
                                  $analysis->details = [
                                      'internal_code' => $subAnalysis->consecutivo_no,
                                      'analyst_name' => $subAnalysis->user ? $subAnalysis->user->name : 'N/A',
                                      'controles_analiticos' => $subAnalysis->controles_analiticos ?? [],
                                      'precision_analitica' => $subAnalysis->precision_analitica ?? [],
                                      'veracidad_analitica' => $subAnalysis->veracidad_analitica ?? [],
                                      'items_ensayo' => $subAnalysis->items_ensayo ?? [],
                                      'type' => $analysis->phAnalysis ? 'ph' : 'conductivity', // Identificar el tipo
                                  ];
                                  $analysis->description = $analysis->service ? $analysis->service->description : 'N/A';
                                  $analysis->review_status = $subAnalysis->review_status ?? 'pending';
                                  $analysis->review_date = $subAnalysis->review_date;
                                  $analysis->reviewed_by = $subAnalysis->reviewed_by;
                                  $analysis->reviewer_role = $subAnalysis->reviewer_role;
                                  $analysis->review_observations = $subAnalysis->review_observations;
                                  $analysis->result = $this->extractResult($subAnalysis);
                              } else {
                                  $analysis->analysis_date = null;
                                  $analysis->details = [
                                      'internal_code' => 'N/A',
                                      'analyst_name' => 'N/A',
                                      'controles_analiticos' => [],
                                      'precision_analitica' => [],
                                      'veracidad_analitica' => [],
                                      'items_ensayo' => [],
                                      'type' => 'unknown',
                                  ];
                                  $analysis->description = $analysis->service ? $analysis->service->description : 'N/A';
                                  $analysis->review_status = 'pending';
                                  $analysis->review_date = null;
                                  $analysis->reviewed_by = null;
                                  $analysis->reviewer_role = null;
                                  $analysis->review_observations = null;
                                  $analysis->result = 'N/A';
                              }

                              return $analysis;
                          });

        // Log para depurar servicios
        \Log::info('Servicios encontrados: ', ['services' => $analyses->pluck('service.description')->unique()]);

        // Paginar manualmente
        $perPage = 10;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedAnalyses = new LengthAwarePaginator(
            $analyses->slice($offset, $perPage),
            $analyses->count(),
            $perPage,
            $page,
            ['path' => route('process.review_index')]
        );

        return view('processes.review_index', ['analyses' => $paginatedAnalyses]);
    }

    /**
     * Extract the result for the specific analysis.
     */
    private function extractResult($subAnalysis)
    {
        if (!$subAnalysis || empty($subAnalysis->items_ensayo)) return 'N/A';
        $firstItem = $subAnalysis->items_ensayo[0];
        // Para conductividad, preferir valor_leido_dsm; para pH, usar valor_leido
        return $firstItem['valor_leido_dsm'] ?? $firstItem['valor_leido'] ?? 'N/A';
    }

    /**
     * Store the review for an analysis (inline from the table).
     */
    public function storeReview(Request $request, $analysisId)
    {
        $request->validate([
            'review_status' => 'required|in:approved,rejected',
            'review_observations' => 'nullable|string',
        ]);

        $analysis = Analysis::findOrFail($analysisId);
        $subAnalysis = $analysis->phAnalysis ?? $analysis->conductivityAnalysis;

        if ($subAnalysis) {
            $subAnalysis->update([
                'review_status' => $request->review_status,
                'reviewed_by' => Auth::user()->name,
                'reviewer_role' => Auth::user()->role ?? 'Admin',
                'review_date' => now(),
                'review_observations' => $request->review_observations,
            ]);

            // Actualizar el estado de aprobación del análisis
            $analysis->update([
                'approved' => $request->review_status === 'approved',
            ]);

            return redirect()->route('process.review_index')
                             ->with('success', 'Revisión registrada exitosamente.');
        }

        return redirect()->route('process.review_index')
                         ->with('error', 'No se encontró el análisis.');
    }

    /**
     * Display a listing of completed processes ready for report generation.
     */
    public function completedProcesses()
    {
        // Obtener procesos cuyos análisis estén completados y aprobados
        $processes = Process::with(['analyses'])
                          ->get()
                          ->filter(function ($process) {
                              return $process->analyses->count() > 0 && $process->analyses->every(function ($analysis) {
                                  return $analysis->status === 'completed' && $analysis->approved;
                              });
                          });

        // Paginar manualmente
        $perPage = 10;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedProcesses = new \Illuminate\Pagination\LengthAwarePaginator(
            $processes->slice($offset, $perPage),
            $processes->count(),
            $perPage,
            $page,
            ['path' => route('process.completed')]
        );

        return view('processes.completed', ['processes' => $paginatedProcesses]);
    }

    /**
     * Generate a PDF report for a process.
     */
    public function generateReport($processId)
    {
        $process = Process::where('process_id', $processId)
                        ->with(['quote.customer', 'quote.quoteServices.service', 'quote.quoteServices.servicePackage', 'analyses'])
                        ->firstOrFail();

        // Verificar si todos los análisis están completados y aprobados
        $allApproved = $process->analyses->every(function ($analysis) {
            return $analysis->status === 'completed' && $analysis->approved;
        });

        if (!$allApproved) {
            return redirect()->route('process.completed')
                           ->with('error', 'Faltan análisis por completar o aprobar.');
        }

        // Generar el PDF usando la plantilla process_results.blade.php
        $pdf = Pdf::loadView('pdf.process_results', compact('process'));
        return $pdf->download('resultados_proceso_' . $process->process_id . '.pdf');
    }

    /**
     * Preview the PDF report for a process.
     */
    public function previewReport($processId)
    {
        $process = Process::where('process_id', $processId)
                        ->with(['quote.customer', 'quote.quoteServices.service', 'quote.quoteServices.servicePackage', 'analyses'])
                        ->firstOrFail();

        // Verificar si todos los análisis están completados y aprobados
        $allApproved = $process->analyses->every(function ($analysis) {
            return $analysis->status === 'completed' && $analysis->approved;
        });

        if (!$allApproved) {
            return redirect()->route('process.completed')
                           ->with('error', 'Faltan análisis por completar o aprobar.');
        }

        return view('pdf.process_results', compact('process'));
    }
    /**
     * Show the form to review a specific analysis.
     */
    public function reviewAnalysis($analysis_id)
    {
        $analysis = Analysis::with('process', 'service', 'phAnalysis')->findOrFail($analysis_id);

        // Preparar los datos para la vista
        $analysis->analysis_date = $analysis->phAnalysis ? $analysis->phAnalysis->fecha_analisis : null;
        $analysis->details = [
            'internal_code' => $analysis->phAnalysis ? $analysis->phAnalysis->consecutivo_no : 'N/A',
            'analyst_name' => $analysis->phAnalysis && $analysis->phAnalysis->user ? $analysis->phAnalysis->user->name : 'N/A',
        ];
        $analysis->review_status = $analysis->phAnalysis ? $analysis->phAnalysis->review_status : 'pending';
        $analysis->review_date = $analysis->phAnalysis ? $analysis->phAnalysis->review_date : null;
        $analysis->reviewed_by = $analysis->phAnalysis ? $analysis->phAnalysis->reviewed_by : null;
        $analysis->reviewer_role = $analysis->phAnalysis ? $analysis->phAnalysis->reviewer_role : null;
        $analysis->review_observations = $analysis->phAnalysis ? $analysis->phAnalysis->review_observations : null;

        return view('processes.review_analysis', compact('analysis'));
    }

   
    
    public function technicalIndex()
    {
        try {
            Log::info('User accessing technicalIndex:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);
    
            $processes = Process::where('status', 'pending')
                ->with([
                    'quote' => function ($query) {
                        $query->with('customer');
                    },
                    'analyses' => function ($query) {
                        $query->with('service'); // Removed the where('status', 'pending') filter
                    },
                ])
                ->get();
    
            Log::info('Processes loaded:', [
                'count' => $processes->count(),
                'processes' => $processes->toArray(),
            ]);
    
            return view('processes.technical_index', compact('processes'));
        } catch (\Exception $e) {
            Log::error('Error in technicalIndex: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('personal_tecnico.dashboard')
                           ->with('error', 'Error al cargar los procesos técnicos: ' . $e->getMessage());
        }
    }
public function start(Request $request, $quote_id)
{
    $quote = Quote::with(['quoteServices'])->where('quote_id', $quote_id)->firstOrFail();
    
    $unitCount = $request->input('unit_count');
    $services = $request->input('services', []); // Array de servicios seleccionados por terreno

    \Log::info('--- INICIO DE PROCESOS ---');
    \Log::info('unitCount', ['unitCount' => $unitCount]);
    \Log::info('services array recibido', $services);
    \Log::info('quoteServices en BD', $quote->quoteServices->map(function($qs){ return ['id'=>$qs->id, 'unit_index'=>$qs->unit_index]; })->toArray());

    for ($unitIndex = 0; $unitIndex < $unitCount; $unitIndex++) {
        $description = $request->input("descriptions.$unitIndex");
        $itemCode = $request->input("item_codes.$unitIndex");
        $selectedServiceIds = $services[$unitIndex] ?? [];
        if (is_string($selectedServiceIds)) {
            $selectedServiceIds = json_decode($selectedServiceIds, true) ?? [];
        }

        \Log::info('Terreno', ['unitIndex'=>$unitIndex, 'selectedServiceIds'=>$selectedServiceIds]);

        // Crear el proceso (terreno)
        $process = Process::create([
            'process_id' => 'PRC-' . time() . '-' . $unitIndex,
            'quote_id' => $quote_id,
            'item_code' => $itemCode,
            'status' => 'pending',
            'comunicacion_cliente' => $request->input('comunicacion_cliente'),
            'dias_procesar' => $request->input('dias_procesar'),
            'fecha_recepcion' => now(),
            'descripcion' => $description,
            'lugar_muestreo' => $request->input('lugar_muestreo'),
            'fecha_muestreo' => $request->input('fecha_muestreo'),
            'responsable_recepcion' => auth()->user()->user_id,
            'fecha_entrega' => now()->addDays($request->input('dias_procesar')),
        ]);

        // Asociar solo los servicios de este terreno (unit_index)
        foreach ($selectedServiceIds as $quoteServiceId) {
            $quoteService = $quote->quoteServices->first(function($qs) use ($quoteServiceId, $unitIndex) {
                return $qs->id == $quoteServiceId && $qs->unit_index == $unitIndex;
            });
            \Log::info('Asociando servicio a proceso', [
                'process_id' => $process->process_id,
                'unit_index' => $unitIndex,
                'quote_service_id' => $quoteServiceId,
                'encontrado' => $quoteService ? true : false,
                'unit_index_encontrado' => $quoteService->unit_index ?? null,
            ]);
            if ($quoteService) {
                $isService = $quoteService->services_id ? true : false;
                $spd = ServiceProcessDetail::create([
                    'process_id' => $process->process_id,
                    'quote_service_id' => $quoteService->id,
                    'type' => $isService ? 'service' : 'package',
                    'description' => $isService ? ($quoteService->service->descripcion ?? '') : ($quoteService->servicePackage->nombre ?? ''),
                    'status' => 'pending',
                ]);
                if ($isService) {
                    \App\Models\Analysis::create([
                        'process_id' => $process->process_id,
                        'service_id' => $quoteService->services_id,
                        'status' => 'pending',
                        'cantidad' => $quoteService->cantidad,
                        'approved' => false,
                    ]);
                } else {
                    // Es un paquete: crear análisis para cada servicio incluido
                    $includedServices = $quoteService->servicePackage ? $quoteService->servicePackage->included_services : collect();
                    foreach ($includedServices as $service) {
                        $serviceId = is_object($service) ? $service->services_id : $service;
                        \App\Models\Analysis::create([
                            'process_id' => $process->process_id,
                            'service_id' => $serviceId,
                            'status' => 'pending',
                            'cantidad' => 1, // O la cantidad que corresponda
                            'approved' => false,
                ]);
                    }
                }
            }
        }
    }

    return redirect()->route('cotizacion.index')->with('success', 'Procesos iniciados correctamente.');
}
/**
 * Calculate the delivery date excluding weekends and holidays.
 *
 * @param \Carbon\Carbon $startDate The starting date
 * @param int $businessDays The number of business days to add
 * @return \Carbon\Carbon The calculated delivery date
 */
protected function calculateDeliveryDate(\Carbon\Carbon $startDate, $businessDays)
{
    $currentDate = $startDate->copy();
    $holidays = [
        '2025-01-01', // Año Nuevo
        '2025-05-01', // Día del Trabajo
        '2025-12-25', // Navidad
        // Agrega más festivos según tu país o región
    ];
    $daysAdded = 0;

    while ($daysAdded < $businessDays) {
        $currentDate->addDay();
        // Check if the day is a weekday (Monday to Friday) and not a holiday
        if ($currentDate->isWeekday() && !in_array($currentDate->toDateString(), $holidays)) {
            $daysAdded++;
        }
    }

    return $currentDate;
}
   

    public function destroy($process_id)
    {
        try {
            $process = Process::findOrFail($process_id);
            $process->delete();
            return redirect()->route('cotizacion.process.index')->with('success', 'Proceso eliminado exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al eliminar proceso: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el proceso: ' . $e->getMessage());
        }
    }

    public function archive($process_id)
    {
        try {
            $process = Process::findOrFail($process_id);
            if ($process->status !== 'completed') {
                return back()->with('error', 'El proceso debe estar completado para poder archivarlo.');
            }
            $process->status = 'archived';
            $process->save();
            return redirect()->route('cotizacion.process.index')->with('success', 'Proceso archivado exitosamente.');
        } catch (\Exception $e) {
            \Log::error('Error al archivar proceso: ' . $e->getMessage());
            return back()->with('error', 'Error al archivar el proceso: ' . $e->getMessage());
        }
    }

    public function generateResultsPDF($process_id)
    {
        try {
            $process = Process::with(['quote.quoteServices.service', 'quote.quoteServices.servicePackage', 'analyses'])->findOrFail($process_id);

            if (!Auth::user()->hasRole('admin')) {
                return back()->with('error', 'Solo los administradores pueden generar el PDF de resultados.');
            }

            $allCompleted = $process->analyses->every(function ($analysis) {
                return $analysis->status === 'completed';
            });

            if (!$allCompleted) {
                return back()->with('error', 'Todos los servicios deben estar completados para generar el PDF de resultados.');
            }

            $allApproved = $process->analyses->every(function ($analysis) {
                return $analysis->approved ?? false;
            });

            if (!$allApproved) {
                return back()->with('error', 'Todos los servicios deben estar aprobados para generar el PDF de resultados.');
            }

            $process->status = 'completed';
            $process->save();

            $pdf = Pdf::loadView('pdf.process_results', compact('process'));
            return $pdf->download('resultados_proceso_' . $process->process_id . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de resultados: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el PDF de resultados: ' . $e->getMessage());
        }
    }
}