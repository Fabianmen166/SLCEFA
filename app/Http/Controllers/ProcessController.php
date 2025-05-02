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

class ProcessController extends Controller
{
    public function index()
    {
        $processes = Process::with(['quote.quoteServices.service', 'quote.quoteServices.servicePackage'])
            ->where('status', 'pending')
            ->get();

        return view('cotizacion.process.index', compact('processes'));
    }

    public function show(Process $process)
    {
        try {
            if (!method_exists(Quote::class, 'quoteServices')) {
                throw new \Exception('La relación quoteServices no está definida en el modelo Quote.');
            }

            $process->load([
                'quote.customer.customerType',
                'quote.quoteServices.service',
                'quote.quoteServices.servicePackage',
                'analyses.service',
            ]);

            if (!$process->quote) {
                throw new \Exception('No se encontró la cotización asociada al proceso.');
            }

            $quoteServices = $process->quote->quoteServices;
            $quoteItems = collect();
            $servicesToDo = collect();

            foreach ($quoteServices as $quoteService) {
                if ($quoteService->services_id) {
                    $service = $quoteService->service;
                    if (!$service) {
                        continue;
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

            $pendingServices = $servicesToDo->filter(function ($service) {
                return !$service->analysis || $service->analysis->status === 'pending';
            });

            $completedServices = $servicesToDo->filter(function ($service) {
                return $service->analysis && $service->analysis->status === 'completed';
            });

            return view('processes.show', compact(
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
            // Find the quote by quote_id
            $quote = Quote::where('quote_id', $quote)->firstOrFail();
            Log::info('Starting process for quote:', ['quote_id' => $quote->quote_id]);

            $fechaRecepcion = now();
            $diasProcesar = (int) $request->dias_procesar;
            $fechaEntrega = $this->calculateDeliveryDate($fechaRecepcion, $diasProcesar);
            $processId = $request->item_code;

            // Create the process
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

            // Get the services associated with the quote from the quote_services table
            $quoteServices = $quote->quoteServices()->get();
            Log::info('Quote services retrieved:', ['quote_services' => $quoteServices->toArray()]);

            $serviceIds = [];
            foreach ($quoteServices as $quoteService) {
                if ($quoteService->services_id) {
                    // Individual service
                    $serviceIds[] = (int) $quoteService->services_id;
                    Log::info('Added individual service ID:', ['service_id' => $quoteService->services_id]);
                } elseif ($quoteService->service_packages_id) {
                    // Service package
                    $package = ServicePackage::find($quoteService->service_packages_id);
                    if ($package) {
                        // Use the included_service_ids accessor to get the array of service IDs
                        $includedServiceIds = $package->included_service_ids;

                        if (is_array($includedServiceIds) && !empty($includedServiceIds)) {
                            // Convert each service ID to an integer
                            $includedServiceIds = array_map('intval', $includedServiceIds);
                            // Validate that all service IDs exist
                            $validServiceIds = [];
                            foreach ($includedServiceIds as $serviceId) {
                                if (Service::where('services_id', $serviceId)->exists()) {
                                    $validServiceIds[] = $serviceId;
                                } else {
                                    Log::warning('Invalid service ID in package:', [
                                        'service_package_id' => $package->service_packages_id,
                                        'service_id' => $serviceId,
                                    ]);
                                }
                            }
                            // Check for duplicates in included_service_ids
                            if (count($validServiceIds) !== count(array_unique($validServiceIds))) {
                                Log::warning('Duplicate service IDs in package:', [
                                    'service_package_id' => $package->service_packages_id,
                                    'included_service_ids' => $validServiceIds,
                                ]);
                            }
                            $serviceIds = array_merge($serviceIds, $validServiceIds);
                            Log::info('Added service IDs from package:', ['included_service_ids' => $validServiceIds]);
                        } else {
                            Log::warning('included_service_ids is empty or not an array:', [
                                'service_package_id' => $package->service_packages_id,
                                'included_service_ids' => $includedServiceIds,
                            ]);
                        }
                    }
                }
            }

            // Remove duplicates
            $serviceIds = array_unique($serviceIds);
            Log::info('Final service IDs for process:', ['service_ids' => $serviceIds]);

            // Create an Analysis record for each service
            foreach ($serviceIds as $serviceId) {
                try {
                    if (Service::where('services_id', $serviceId)->exists()) {
                        // Check if an Analysis record already exists to avoid duplicates
                        $existingAnalysis = Analysis::where('process_id', $process->process_id)
                                                    ->where('service_id', $serviceId)
                                                    ->first();

                        if ($existingAnalysis) {
                            Log::info('Analysis record already exists, skipping:', [
                                'process_id' => $process->process_id,
                                'service_id' => $serviceId,
                            ]);
                            continue;
                        }

                        $analysis = Analysis::create([
                            'process_id' => $process->process_id,
                            'service_id' => $serviceId,
                            'status' => 'pending',
                        ]);
                        Log::info('Created analysis record:', [
                            'process_id' => $process->process_id,
                            'service_id' => $serviceId,
                            'analysis_id' => $analysis->id,
                        ]);
                    } else {
                        Log::warning('Service ID does not exist:', ['service_id' => $serviceId]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to create analysis record:', [
                        'process_id' => $process->process_id,
                        'service_id' => $serviceId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Redirect to the correct route
            return redirect()->route('processes.show', $processId)
                            ->with('success', 'Proceso iniciado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error starting process: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al iniciar el proceso: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Calculate the delivery date by adding processing days to the reception date.
     *
     * @param \Carbon\Carbon $fechaRecepcion
     * @param int $diasProcesar
     * @return \Carbon\Carbon
     */
    protected function calculateDeliveryDate(Carbon $fechaRecepcion, int $diasProcesar)
    {
        return $fechaRecepcion->copy()->addDays($diasProcesar);
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