<?php
namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\Service;
use App\Models\ServiceProcessDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Quote;
use Illuminate\Support\Facades\Log;
use App\Models\Analysis;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ProcessController extends Controller
{

       /**
     * Mostrar una lista de procesos abiertos (status = 'pending') para gestión de calidad.
     *
     * @return \Illuminate\View\View
     */
    // app/Http/Controllers/ProcessController.php
// app/Http/Controllers/ProcessController.php
public function index()
{
    $processes = Process::with(['quote.quoteServices.service', 'quote.quoteServices.servicePackage'])
        ->where('status', 'pending')
        ->get();

    return view('cotizacion.process.index', compact('processes'));
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
                'services' => function ($query) {
                    $query->withPivot('status', 'cantidad')
                          ->wherePivot('status', 'pending');
                },
                'completedServices' => function ($query) {
                    $query->withPivot('status', 'cantidad')
                          ->wherePivot('status', 'completed');
                },
                'serviceProcessDetails' => function ($query) {
                    $query->with('service');
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

    public function reviewAnalysis($analysis_id)
{
    $analysis = Analysis::with('process.quote.customer', 'process.quote.services', 'service')->findOrFail($analysis_id);
    $process = $analysis->process;

    // Obtener todos los servicios asociados al proceso
    $servicesToDo = $process->quote->services;

    // Obtener los servicios pendientes
    $pendingServices = $servicesToDo->filter(function ($service) use ($process) {
        $analysis = $process->analyses->firstWhere('service_id', $service->id);
        return !$analysis || $analysis->status === 'pending';
    });

    // Obtener los servicios realizados
    $completedServices = $servicesToDo->filter(function ($service) use ($process) {
        $analysis = $process->analyses->firstWhere('service_id', $service->id);
        return $analysis && $analysis->status === 'completed';
    });

    return view('process.review_analysis', compact('analysis', 'process', 'servicesToDo', 'pendingServices', 'completedServices'));
}

// app/Http/Controllers/ProcessController.php
public function technicalAnalysis($process_id, $service_type = null)
{
    try {
        $process = Process::with(['services', 'serviceDetails'])
            ->findOrFail($process_id);

        $pendingServices = $process->services->where('pivot.status', 'pending');
        $serviceTypeMap = [
            'ph_en_suelos' => 'pH en Suelos',
            'acidez_intercambiable_en_suelos' => 'Acidez Intercambiable en Suelos',
            'conductividad_electrica_en_suelos' => 'Conductividad Eléctrica en Suelos',
        ];

        $targetService = null;
        if ($service_type && isset($serviceTypeMap[$service_type])) {
            $targetService = $serviceTypeMap[$service_type];
        }

        $analysisForms = [];
        foreach ($pendingServices as $service) {
            if ($targetService && $service->descripcion !== $targetService) {
                continue;
            }
            $formType = match ($service->descripcion) {
                'pH en Suelos' => 'ph',
                'Acidez Intercambiable en Suelos' => 'acidez',
                'Conductividad Eléctrica en Suelos' => 'conductividad',
                default => null,
            };

            if ($formType) {
                $analysisForms[$service->services_id] = [
                    'service' => $service,
                    'form_type' => $formType,
                    'existing_analyses' => $process->serviceDetails->where('services_id', $service->services_id)->get(),
                ];
            }
        }

        if (empty($analysisForms) && $targetService) {
            return redirect()->route('process.technical_index')->with('warning', 'No hay análisis pendientes para ' . $targetService);
        }

        return view('processes.technical_analysis', compact('process', 'analysisForms', 'targetService'));
    } catch (\Exception $e) {
        \Log::error('Error en ProcessController@technicalAnalysis: ' . $e->getMessage());
        return back()->with('error', 'Error al cargar el análisis técnico: ' . $e->getMessage());
    }
}




public function storeAnalysis(Request $request, $process_id, $service_id)
{
    $request->validate([
        'form_type' => 'required|in:acidez,ph,conductividad',
        'analysis_date' => 'required|date',
        'analyst_name' => 'required|string',
        'samples' => 'required|array|max:20',
        'samples.*.internal_code' => 'required|string',
    ]);

    try {
        $process = Process::findOrFail($process_id);
        $service = Service::findOrFail($service_id);
        $formType = $request->form_type;
        $analysisDate = $request->analysis_date;
        $analystName = $request->analyst_name;
        $samples = $request->input('samples', []);

        $details = [];
        foreach ($samples as $index => $sample) {
            $sampleDetails = [
                'internal_code' => $sample['internal_code'],
                'analyst_name' => $analystName,
                'observations' => $sample['observations'] ?? '',
            ];

            if ($formType === 'ph') {
                $request->validate([
                    "samples.{$index}.weight" => 'required|numeric|min:0',
                    "samples.{$index}.water_volume" => 'required|numeric|min:0',
                    "samples.{$index}.temperature" => 'required|numeric|min:0',
                    "samples.{$index}.ph_value" => 'required|numeric|min:0|max:14',
                ]);
                $sampleDetails['weight'] = (float) $sample['weight'];
                $sampleDetails['water_volume'] = (float) $sample['water_volume'];
                $sampleDetails['temperature'] = (float) $sample['temperature'];
                $sampleDetails['ph_value'] = (float) $sample['ph_value'];
            }

            $details[] = $sampleDetails;
        }

        // Guardar en service_process_details
        ServiceProcessDetail::create([
            'process_id' => $process_id,
            'services_id' => $service_id,
            'analysis_date' => $analysisDate,
            'details' => json_encode($details),
            'status' => 'completed',
        ]);

        // Verificar si se completó el servicio
        $totalAnalyses = ServiceProcessDetail::where('process_id', $process_id)
            ->where('services_id', $service_id)
            ->count();
        $requiredAnalyses = $process->services()->where('services_id', $service_id)->first()->pivot->cantidad ?? 1;
        if ($totalAnalyses >= $requiredAnalyses) {
            $process->services()->updateExistingPivot($service_id, ['status' => 'completed']);
        }

        // Generar archivo Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Código Interno');
        $sheet->setCellValue('B1', 'Peso (g)');
        $sheet->setCellValue('C1', 'Volumen de Agua (ml)');
        $sheet->setCellValue('D1', 'Temperatura (°C)');
        $sheet->setCellValue('E1', 'pH');
        $sheet->setCellValue('F1', 'Observaciones');

        $row = 2;
        foreach ($details as $sample) {
            $sheet->setCellValue('A' . $row, $sample['internal_code']);
            $sheet->setCellValue('B' . $row, $sample['weight'] ?? '');
            $sheet->setCellValue('C' . $row, $sample['water_volume'] ?? '');
            $sheet->setCellValue('D' . $row, $sample['temperature'] ?? '');
            $sheet->setCellValue('E' . $row, $sample['ph_value'] ?? '');
            $sheet->setCellValue('F' . $row, $sample['observations'] ?? '');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "analisis_{$process_id}_{$service_id}_" . now()->format('YmdHis') . '.xlsx';
        $writer->save(storage_path('app/public/' . $fileName));

        // Generar PDF (opcional)
        $pdf = Pdf::loadView('pdf.analysis', compact('details', 'service', 'process'));
        $pdfFileName = "analisis_{$process_id}_{$service_id}_" . now()->format('YmdHis') . '.pdf';
        $pdf->save(storage_path('app/public/' . $pdfFileName));

        return redirect()->route('process.technical_index')
            ->with('success', 'Análisis registrado y archivos generados.')
            ->with('file', $fileName)
            ->with('pdf_file', $pdfFileName);
    } catch (\Exception $e) {
        \Log::error('Error al registrar análisis: ' . $e->getMessage());
        return back()->with('error', 'Error al registrar el análisis: ' . $e->getMessage())->withInput();
    }
}

    public function indexForReview()
    {
        $analyses = ServiceProcessDetail::with(['process', 'service'])
            ->whereIn('review_status', ['pending', 'approved', 'rejected'])
            ->orderBy('analysis_date', 'desc')
            ->paginate(10);

        return view('processes.review_index', compact('analyses'));
    }



    public function storeReview(Request $request, $analysis_id)
    {
        $request->validate([
            'review_status' => 'required|in:approved,rejected',
            'review_observations' => 'nullable|string',
        ]);

        try {
            $analysis = ServiceProcessDetail::findOrFail($analysis_id);
            $analysis->update([
                'reviewed_by' => Auth::user()->name,
                'reviewer_role' => Auth::user()->role,
                'review_date' => now(),
                'review_observations' => $request->review_observations,
                'review_status' => $request->review_status,
            ]);

            return redirect()->route('process.review_index')->with('success', 'Revisión registrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al registrar revisión: ' . $e->getMessage());
            return back()->with('error', 'Error al registrar la revisión: ' . $e->getMessage())->withInput();
        }
    }

    public function editAnalysis($analysis_id)
    {
        $analysis = ServiceProcessDetail::with(['process', 'service'])->findOrFail($analysis_id);

        if ($analysis->review_status !== 'rejected') {
            return redirect()->route('process.technical_analysis', $analysis->process_id)
                ->with('error', 'Solo se pueden corregir análisis rechazados.');
        }

        $formType = match ($analysis->service->description) {
            'Acidez Intercambiable en Suelos' => 'acidez',
            'pH en Suelos' => 'ph',
            'Conductividad Eléctrica en Suelos' => 'conductividad',
            default => null,
        };

        return view('processes.edit_analysis', compact('analysis', 'formType'));
    }

    public function updateAnalysis(Request $request, $analysis_id)
    {
        $request->validate([
            'form_type' => 'required|in:acidez,ph,conductividad',
            'analysis_date' => 'required|date',
            'analyst_name' => 'required|string',
            'internal_code' => 'required|string',
        ]);

        try {
            $analysis = ServiceProcessDetail::findOrFail($analysis_id);
            $formType = $request->form_type;
            $analysisDate = $request->analysis_date;
            $analystName = $request->analyst_name;

            $details = [
                'internal_code' => $request->internal_code,
                'analyst_name' => $analystName,
                'observations' => $request->observations ?? '',
            ];

            if ($formType === 'acidez') {
                $request->validate([
                    'weight' => 'required|numeric|min:0',
                    'naoh_blank' => 'required|numeric|min:0',
                    'humidity' => 'required|numeric|min:0',
                    'naoh_molarity' => 'required|numeric|min:0',
                    'naoh_sample' => 'required|numeric|min:0',
                ]);

                $details['weight'] = (float) $request->weight;
                $details['naoh_blank'] = (float) $request->naoh_blank;
                $details['humidity'] = (float) $request->humidity;
                $details['naoh_molarity'] = (float) $request->naoh_molarity;
                $details['naoh_sample'] = (float) $request->naoh_sample;

                // Calcular Acidez (cmol(+)/kg)
                $acidity = (($details['naoh_sample'] - $details['naoh_blank']) * $details['naoh_molarity'] * 100) / ($details['weight'] * (1 - $details['humidity'] / 100));
                $details['acidity'] = round($acidity, 2);
            } elseif ($formType === 'ph') {
                $request->validate([
                    'weight' => 'required|numeric|min:0',
                    'water_volume' => 'required|numeric|min:0',
                    'temperature' => 'required|numeric|min:0',
                    'ph_value' => 'required|numeric|min:0|max:14',
                ]);

                $details['weight'] = (float) $request->weight;
                $details['water_volume'] = (float) $request->water_volume;
                $details['temperature'] = (float) $request->temperature;
                $details['ph_value'] = (float) $request->ph_value;
            } elseif ($formType === 'conductividad') {
                $request->validate([
                    'weight' => 'required|numeric|min:0',
                    'water_volume' => 'required|numeric|min:0',
                    'temperature' => 'required|numeric|min:0',
                    'conductivity_uscm' => 'required|numeric|min:0',
                ]);

                $details['weight'] = (float) $request->weight;
                $details['water_volume'] = (float) $request->water_volume;
                $details['temperature'] = (float) $request->temperature;
                $details['conductivity_uscm'] = (float) $request->conductivity_uscm;
                $details['conductivity_dsm'] = $details['conductivity_uscm'] * 0.001; // Convertir µS/cm a dS/m
            }

            // Actualizar el análisis
            $analysis->update([
                'analysis_date' => $analysisDate,
                'details' => $details,
                'review_status' => 'pending', // Volver a pendiente para nueva revisión
                'reviewed_by' => null,
                'reviewer_role' => null,
                'review_date' => null,
                'review_observations' => null,
            ]);

            return redirect()->route('process.technical_analysis', $analysis->process_id)
                ->with('success', 'Análisis corregido exitosamente. Pendiente de nueva revisión.');
        } catch (\Exception $e) {
            Log::error('Error al corregir análisis: ' . $e->getMessage());
            return back()->with('error', 'Error al corregir el análisis: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Calcular la fecha de entrega sumando días hábiles a la fecha de recepción.
     *
     * @param Carbon $startDate Fecha de inicio (fecha_recepcion)
     * @param int $businessDays Número de días hábiles para sumar
     * @return Carbon Fecha de entrega
     */
    private function calculateDeliveryDate(Carbon $startDate, int $businessDays)
    {
        $holidays = [
            '2025-01-01', // Año Nuevo
            '2025-05-01', // Día del Trabajo
            '2025-07-20', // Día de la Independencia (ejemplo para Colombia)
            '2025-12-25', // Navidad
        ];

        $currentDate = $startDate->copy();
        $daysAdded = 0;

        while ($daysAdded < $businessDays) {
            $currentDate->addDay();

            $isWeekend = $currentDate->isSaturday() || $currentDate->isSunday();
            $isHoliday = in_array($currentDate->format('Y-m-d'), $holidays);

            if (!$isWeekend && !$isHoliday) {
                $daysAdded++;
            }
        }

        return $currentDate;
    }

    public function start(Request $request, $quote)
    {
        $request->validate([
            'item_code' => 'required|string|max:255|unique:processes,process_id',
            'comunicacion_cliente' => 'nullable|string',
            'dias_procesar' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
            'lugar_muestreo' => 'nullable|string|max:255',
            'fecha_muestreo' => 'nullable|date',
        ]);
    
        try {
            $quote = Quote::findOrFail($quote);
            Log::info('Starting process for quote:', ['quote_id' => $quote->quote_id]);
    
            $fechaRecepcion = now();
            $diasProcesar = (int) $request->dias_procesar;
            $fechaEntrega = $this->calculateDeliveryDate($fechaRecepcion, $diasProcesar);
            $processId = $request->item_code;
    
            $process = Process::create([
                'process_id' => $processId,
                'quote_id' => $quote->quote_id,
                'item_code' => $request->item_code,
                'comunicacion_cliente' => $request->comunicacion_cliente,
                'dias_procesar' => $diasProcesar,
                'fecha_recepcion' => $fechaRecepcion,
                'descripcion' => $request->descripcion,
                'lugar_muestreo' => $request->lugar_muestreo,
                'fecha_muestreo' => $request->fecha_muestreo,
                'responsable_recepcion' => Auth::id(),
                'fecha_entrega' => $fechaEntrega,
                'status' => 'pending',
            ]);
    
            $quote->load('quoteServices.service', 'quoteServices.servicePackage');
            $serviceIdsProcessed = [];
    
            foreach ($quote->quoteServices as $quoteService) {
                Log::info('Processing quote service:', [
                    'services_id' => $quoteService->services_id,
                    'service_packages_id' => $quoteService->service_packages_id,
                    'cantidad' => $quoteService->cantidad,
                ]);
    
                $cantidad = $quoteService->cantidad ?? 1;
    
                if ($quoteService->services_id) {
                    // Individual service
                    if (!in_array($quoteService->services_id, $serviceIdsProcessed)) {
                        Analysis::create([
                            'process_id' => $processId,
                            'service_id' => $quoteService->services_id,
                            'status' => 'pending',
                        ]);
                        \DB::table('process_service')->insert([
                            'process_id' => $processId,
                            'services_id' => $quoteService->services_id,
                            'cantidad' => $cantidad,
                            'status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $serviceIdsProcessed[] = $quoteService->services_id;
                    }
                } elseif ($quoteService->service_packages_id) {
                    // Package services
                    $package = $quoteService->servicePackage;
                    if (!$package) {
                        Log::warning('Service package not found:', ['service_packages_id' => $quoteService->service_packages_id]);
                        continue;
                    }
    
                    $serviceIds = [];
                    // Check if included_services is a Collection (from accessor)
                    if ($package->included_services instanceof \Illuminate\Database\Eloquent\Collection) {
                        $serviceIds = $package->included_services->pluck('services_id')->toArray();
                    }
                    // Check if included_services is a JSON string (raw database value)
                    elseif (is_string($package->included_services)) {
                        $serviceIds = json_decode($package->included_services, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            Log::error('Invalid JSON in included_services:', [
                                'service_packages_id' => $quoteService->service_packages_id,
                                'json_error' => json_last_error_msg(),
                            ]);
                            continue;
                        }
                    }
    
                    Log::info('Extracted service IDs:', ['service_ids' => $serviceIds]);
    
                    if (!is_array($serviceIds) || empty($serviceIds)) {
                        Log::warning('No valid service IDs found in package:', [
                            'service_packages_id' => $quoteService->service_packages_id,
                            'included_services' => $package->included_services,
                        ]);
                        continue;
                    }
    
                    foreach ($serviceIds as $serviceId) {
                        if (!in_array($serviceId, $serviceIdsProcessed)) {
                            $service = Service::find($serviceId);
                            if (!$service) {
                                Log::warning('Service not found:', ['service_id' => $serviceId]);
                                continue;
                            }
                            Analysis::create([
                                'process_id' => $processId,
                                'service_id' => $serviceId,
                                'status' => 'pending',
                            ]);
                            \DB::table('process_service')->insert([
                                'process_id' => $processId,
                                'services_id' => $serviceId,
                                'cantidad' => $cantidad,
                                'status' => 'pending',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $serviceIdsProcessed[] = $serviceId;
                        }
                    }
                }
            }
    
            return redirect()->route('cotizacion.process.show', $processId)
                            ->with('success', 'Proceso iniciado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error starting process: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al iniciar el proceso: ' . $e->getMessage())->withInput();
        }
    }
// app/Http/Controllers/ProcessController.php
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

// app/Http/Controllers/ProcessController.php
public function generateResultsPDF($process_id)
{
    try {
        $process = Process::with(['quote.quoteServices.service', 'quote.quoteServices.servicePackage', 'analyses'])->findOrFail($process_id);

        // Verificar si el usuario es admin
        if (!Auth::user()->hasRole('admin')) {
            return back()->with('error', 'Solo los administradores pueden generar el PDF de resultados.');
        }

        // Verificar si todos los servicios están completados
        $allCompleted = $process->analyses->every(function ($analysis) {
            return $analysis->status === 'completed';
        });

        if (!$allCompleted) {
            return back()->with('error', 'Todos los servicios deben estar completados para generar el PDF de resultados.');
        }

        // Verificar si todos los análisis están aprobados (podrías agregar un campo 'approved' en la tabla analyses)
        $allApproved = $process->analyses->every(function ($analysis) {
            return $analysis->approved ?? true; // Asumimos un campo 'approved'; ajusta según tu esquema
        });

        if (!$allApproved) {
            return back()->with('error', 'Todos los servicios deben estar aprobados para generar el PDF de resultados.');
        }

        // Generar el PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.process_results', compact('process'));
        return $pdf->download('resultados_proceso_' . $process->process_id . '.pdf');
    } catch (\Exception $e) {
        \Log::error('Error al generar PDF de resultados: ' . $e->getMessage());
        return back()->with('error', 'Error al generar el PDF de resultados: ' . $e->getMessage());
    }
}
public function show(Process $process)
{
    try {
        // Verificar si la relación quoteServices existe
        if (!method_exists(Quote::class, 'quoteServices')) {
            throw new \Exception('La relación quoteServices no está definida en el modelo Quote.');
        }

        // Cargar las relaciones
        $process->load([
            'quote.customer.customerType',
            'quote.quoteServices.service',
            'quote.quoteServices.servicePackage',
            'analyses'
        ]);

        // Verificar si quote está cargado
        if (!$process->quote) {
            throw new \Exception('No se encontró la cotización asociada al proceso.');
        }

        // Obtener todas las asignaciones de servicios y paquetes
        $quoteServices = $process->quote->quoteServices;

        // Construir la lista de ítems (servicios individuales y paquetes)
        $quoteItems = collect();
        $servicesToDo = collect();

        foreach ($quoteServices as $quoteService) {
            if ($quoteService->services_id) {
                // Servicio individual
                $service = $quoteService->service;
                if (!$service) {
                    continue; // Saltar si el servicio no existe
                }
                $analysis = $process->analyses->firstWhere('service_id', $service->services_id);
                $quoteItems->push((object) [
                    'type' => 'service',
                    'id' => $service->services_id,
                    'name' => $service->descripcion,
                    'price' => $service->precio,
                    'accredited' => $service->acreditado,
                    'quantity' => $quoteService->cantidad,
                    'subtotal' => $quoteService->subtotal,
                ]);
                $servicesToDo->push((object) [
                    'id' => $service->services_id,
                    'name' => $service->descripcion,
                    'price' => $service->precio,
                    'accredited' => $service->acreditado,
                    'quantity' => $quoteService->cantidad,
                    'subtotal' => $quoteService->subtotal,
                    'analysis' => $analysis,
                    'package_name' => null,
                ]);
            } elseif ($quoteService->service_packages_id) {
                // Paquete de servicios
                $package = $quoteService->servicePackage;
                if ($package) {
                    $includedServices = $package->getIncludedServiceObjects();
                    $quoteItems->push((object) [
                        'type' => 'package',
                        'id' => $package->service_packages_id,
                        'name' => $package->nombre,
                        'price' => $package->precio,
                        'accredited' => $package->acreditado,
                        'quantity' => $quoteService->cantidad,
                        'subtotal' => $quoteService->subtotal,
                        'services' => $includedServices->map(function ($service) use ($process) {
                            $analysis = $process->analyses->firstWhere('service_id', $service->services_id);
                            return [
                                'id' => $service->services_id,
                                'name' => $service->descripcion,
                                'price' => $service->precio,
                                'accredited' => $service->acreditado,
                                'analysis' => $analysis,
                            ];
                        })->toArray(),
                    ]);
                    // Añadir los servicios del paquete a servicesToDo
                    foreach ($includedServices as $service) {
                        $analysis = $process->analyses->firstWhere('service_id', $service->services_id);
                        $servicesToDo->push((object) [
                            'id' => $service->services_id,
                            'name' => $service->descripcion,
                            'price' => $service->precio,
                            'accredited' => $service->acreditado,
                            'quantity' => $quoteService->cantidad,
                            'subtotal' => null,
                            'analysis' => $analysis,
                            'package_name' => $package->nombre,
                        ]);
                    }
                }
            }
        }

        // Servicios pendientes (sin análisis o con análisis pendiente)
        $pendingServices = $servicesToDo->filter(function ($service) {
            return !$service->analysis || $service->analysis->status === 'pending';
        });

        // Servicios realizados (con análisis completado)
        $completedServices = $servicesToDo->filter(function ($service) {
            return $service->analysis && $service->analysis->status === 'completed';
        });

        return view('cotizacion.process.show', compact(
            'process',
            'quoteItems',
            'servicesToDo',
            'pendingServices',
            'completedServices'
        ));
    } catch (\Exception $e) {
        \Log::error('Error en ProcessController@show: ' . $e->getMessage());
        return back()->with('error', 'Error al cargar los detalles del proceso: ' . $e->getMessage());
    }

}
}