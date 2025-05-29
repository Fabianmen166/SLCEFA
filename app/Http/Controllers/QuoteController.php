<?php
namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\QuoteService;
use Illuminate\Support\Facades\DB;
use App\Models\Process;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['customer', 'user', 'quoteServices.service', 'quoteServices.servicePackage']);
    
        if ($request->filled('nit')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('nit', 'like', '%' . $request->nit . '%');
            })->orWhere('quote_id', 'like', '%' . $request->nit . '%');
        }
    
        $quotes = $query->orderBy('created_at', 'desc')->get();
    
        return view('cotizacion.index', compact('quotes'));
    }

    public function lista(Request $request)
    {
        return $this->index($request);
    }

    public function create()
    {
        $customers = Customer::all();
        $services = Service::all();
        $servicePackages = ServicePackage::all();
        return view('cotizacion.create', compact('customers', 'services', 'servicePackages'));
    }

  public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes estar autenticado para crear una cotización.');
        }

        Log::info('Datos recibidos para crear cotización:', $request->all());

        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|string|unique:quotes,quote_id',
            'customers_id' => 'required|exists:customers,customers_id',
            'units' => 'required|array|min:1',
            'units.*.services' => 'nullable|array',
            'units.*.services.*.service_id' => 'nullable|exists:services,services_id',
            'units.*.services.*.quantity' => 'nullable|integer|min:1',
            'units.*.packages' => 'nullable|array',
            'units.*.packages.*.package_id' => 'nullable|exists:service_packages,service_packages_id',
            'units.*.packages.*.quantity' => 'nullable|integer|min:1',
        ]);

        $hasServiceOrPackage = false;
        foreach ($request->input('units', []) as $unit) {
            $services = $unit['services'] ?? [];
            $packages = $unit['packages'] ?? [];
            foreach ($services as $service) {
                if (!empty($service['service_id']) && ($service['quantity'] ?? 1) > 0) {
                    $hasServiceOrPackage = true;
                    break 2;
                }
            }
            foreach ($packages as $package) {
                if (!empty($package['package_id']) && ($package['quantity'] ?? 1) > 0) {
                    $hasServiceOrPackage = true;
                    break 2;
                }
            }
        }

        if (!$hasServiceOrPackage) {
            $validator->errors()->add('units', 'Al menos una unidad debe tener un servicio o paquete seleccionado con cantidad válida.');
        }

        if ($validator->fails()) {
            Log::error('Errores de validación:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $validatedData = $validator->validated();
            $customer = Customer::with('customerType')->findOrFail($validatedData['customers_id']);
            $total = 0;

            DB::beginTransaction();

            $quote = Quote::create([
                'quote_id' => $validatedData['quote_id'],
                'customers_id' => $validatedData['customers_id'],
                'user_id' => Auth::id(),
                'total' => 0,
            ]);

            Log::info('Cotización creada con ID: ' . $quote->id . ', quote_id: ' . $quote->quote_id);

            foreach ($validatedData['units'] as $unitIndex => $unit) {
                $services = $unit['services'] ?? [];
                $packages = $unit['packages'] ?? [];

                foreach ($services as $serviceIndex => $service) {
                    if (!empty($service['service_id']) && ($service['quantity'] ?? 1) > 0) {
                        $quantity = $service['quantity'] ?? 1;
                        $serviceModel = Service::findOrFail($service['service_id']);
                        $subtotal = $serviceModel->precio * $quantity;
                        $total += $subtotal;

                        $quoteService = QuoteService::create([
                            'quote_id' => $quote->quote_id,
                            'services_id' => $service['service_id'],
                            'service_packages_id' => null,
                            'cantidad' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);

                        Log::info('Servicio registrado:', [
                            'quote_id' => $quote->quote_id,
                            'service_id' => $service['service_id'],
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'quote_service_id' => $quoteService->id,
                        ]);
                    }
                }

                foreach ($packages as $packageIndex => $package) {
                    if (!empty($package['package_id']) && ($package['quantity'] ?? 1) > 0) {
                        $quantity = $package['quantity'] ?? 1;
                        $packageModel = ServicePackage::findOrFail($package['package_id']);
                        $subtotal = $packageModel->precio * $quantity;
                        $total += $subtotal;

                        $quoteService = QuoteService::create([
                            'quote_id' => $quote->quote_id,
                            'services_id' => null,
                            'service_packages_id' => $package['package_id'],
                            'cantidad' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);

                        Log::info('Paquete registrado:', [
                            'quote_id' => $quote->quote_id,
                            'package_id' => $package['package_id'],
                            'quantity' => $quantity,
                            'subtotal' => $subtotal,
                            'quote_service_id' => $quoteService->id,
                        ]);
                    }
                }
            }

            $discount = $customer->customerType->discount_percentage / 100 ?? 0;
            if ($discount > 0) {
                $total = $total * (1 - $discount);
            }

            $quote->total = $total;
            $quote->save();

            Log::info('Cotización guardada con total: ' . $total);

            DB::commit();

            return redirect()->route('cotizacion.index')->with('success', 'Cotización creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cotización: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function show($quote_id)
    {
        $quote = Quote::with(['customer', 'user', 'quoteServices.service', 'quoteServices.servicePackage'])->findOrFail($quote_id);
        return view('cotizacion.show', compact('quote'));
    }

    public function edit(Quote $id)
    {
        try {
            $quote = $id->load(['quoteServices.service', 'quoteServices.servicePackage']);
            $customers = Customer::all();
            $services = Service::all();
            $servicePackages = ServicePackage::all();
            return view('cotizacion.edit', compact('quote', 'customers', 'services', 'servicePackages'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Cotización no encontrada para ID: {$id->quote_id}. Error: " . $e->getMessage());
            return redirect()->route('cotizacion.index')->with('error', 'No se encontró la cotización con ID ' . $id->quote_id . '. Verifica que exista.');
        } catch (\Exception $e) {
            Log::error("Error al cargar la cotización con ID: {$id->quote_id}. Error: " . $e->getMessage());
            return redirect()->route('cotizacion.index')->with('error', 'Error al cargar la cotización: ' . $e->getMessage());
        }
    }



    public function update(Request $request, $id)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes estar autenticado para actualizar una cotización.'
                ], 403);
            }
    
            Log::info('Updating quote with ID: ' . $id);
    
            $quote = Quote::where('quote_id', $id)->firstOrFail();
            Log::info('Quote found: ' . $quote->quote_id);
    
            $validator = Validator::make($request->all(), [
                'quote_id' => 'required|string|unique:quotes,quote_id,' . $quote->quote_id . ',quote_id',
                'customers_id' => 'required|exists:customers,customers_id',
                'services' => 'nullable|array',
                'services.*' => 'nullable|exists:services,services_id',
                'quantities' => 'nullable|array',
                'quantities.*' => 'nullable|integer|min:1',
                'service_packages' => 'nullable|array',
                'service_packages.*' => 'nullable|exists:service_packages,service_packages_id',
                'package_quantities' => 'nullable|array',
                'package_quantities.*' => 'nullable|integer|min:1',
            ]);
    
            $hasServiceOrPackage = false;
            $services = $request->input('services', []);
            $quantities = $request->input('quantities', []);
            $servicePackages = $request->input('service_packages', []);
            $packageQuantities = $request->input('package_quantities', []);
    
            foreach ($services as $index => $serviceId) {
                if ($serviceId && isset($quantities[$index]) && $quantities[$index] > 0) {
                    $hasServiceOrPackage = true;
                    break;
                }
            }
    
            foreach ($servicePackages as $index => $packageId) {
                if ($packageId && isset($packageQuantities[$index]) && $packageQuantities[$index] > 0) {
                    $hasServiceOrPackage = true;
                    break;
                }
            }
    
            if (!$hasServiceOrPackage) {
                $validator->errors()->add('services', 'Debes seleccionar al menos un servicio o un paquete de servicios con una cantidad válida.');
            }
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $validatedData = $validator->validated();
            $customer = Customer::with('customerType')->findOrFail($validatedData['customers_id']);
            $total = 0;
    
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
            $oldQuoteId = $quote->quote_id;
            $newQuoteId = $validatedData['quote_id'];
    
            // Update quote_id in quote_services first
            if ($oldQuoteId !== $newQuoteId) {
                QuoteService::where('quote_id', $oldQuoteId)->update(['quote_id' => $newQuoteId]);
            }
    
            // Update the quotes table
            $quote->quote_id = $newQuoteId;
            $quote->customers_id = $validatedData['customers_id'];
            $quote->user_id = Auth::id();
            $quote->save();
    
            // Delete existing quote services
            QuoteService::where('quote_id', $newQuoteId)->delete();
    
            // Save new services
            if (!empty($validatedData['services'])) {
                foreach ($validatedData['services'] as $unitIndex => $serviceId) {
                    $quantity = $validatedData['quantities'][$unitIndex] ?? 0;
                    if ($serviceId && $quantity > 0) {
                        $service = Service::findOrFail($serviceId);
                        $subtotal = $service->precio * $quantity;
                        $total += $subtotal;
                        QuoteService::create([
                            'quote_id' => $newQuoteId,
                            'services_id' => $serviceId,
                            'service_packages_id' => null,
                            'cantidad' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);
                    }
                }
            }
    
            // Save new packages
            if (!empty($validatedData['service_packages'])) {
                foreach ($validatedData['service_packages'] as $unitIndex => $packageId) {
                    $quantity = $validatedData['package_quantities'][$unitIndex] ?? 0;
                    if ($packageId && $quantity > 0) {
                        $package = ServicePackage::findOrFail($packageId);
                        $subtotal = $package->precio * $quantity;
                        $total += $subtotal;
                        QuoteService::create([
                            'quote_id' => $newQuoteId,
                            'services_id' => null,
                            'service_packages_id' => $packageId,
                            'cantidad' => $quantity,
                            'subtotal' => $subtotal,
                            'unit_index' => $unitIndex,
                        ]);
                    }
                }
            }
    
            $discount = $customer->customerType->discount_percentage / 100 ?? 0;
            if ($discount > 0) {
                $total = $total * (1 - $discount);
            }
    
            $quote->total = $total;
            $quote->save();
    
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
            return response()->json([
                'success' => true,
                'message' => 'Cotización actualizada exitosamente.'
            ], 200);
        } catch (\Exception $e) {
            // Ensure foreign key checks are re-enabled even if an error occurs
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            Log::error('Error al actualizar cotización: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cotización: ' . $e->getMessage()
            ], 500);
        }
    }

public function destroy($quote_id)
{
    try {
        $quote = Quote::findOrFail($quote_id);
        $quote->quoteServices()->delete();
        $quote->delete();
        return redirect()->route('cotizacion.index')->with('success', 'Cotización eliminada exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al eliminar cotización: ' . $e->getMessage());
        return back()->with('error', 'Error al eliminar la cotización: ' . $e->getMessage());
    }
}

public function comprobante($id)
{
    $quote = Quote::where('quote_id', $id)
        ->with(['customer.customerType', 'user', 'quoteServices.service', 'quoteServices.servicePackage'])
        ->firstOrFail();

    // Determine if any service or package is accredited
    $isAccredited = false;
    foreach ($quote->quoteServices as $quoteService) {
        if ($quoteService->service && $quoteService->service->acreditado) {
            $isAccredited = true;
            break;
        }
        if ($quoteService->servicePackage && $quoteService->servicePackage->acreditado) {
            $isAccredited = true;
            break;
        }
        // Check if any service within the package is accredited
        if ($quoteService->servicePackage) {
            $serviceIds = is_string($quoteService->servicePackage->included_services)
                ? json_decode($quoteService->servicePackage->included_services, true)
                : $quoteService->servicePackage->included_services;
            $services = Service::whereIn('id', $serviceIds ?? [])->get();
            foreach ($services as $service) {
                if ($service->acreditado) {
                    $isAccredited = true;
                    break 2;
                }
            }
        }
    }

    // Fetch the admin user
    $admin = User::where('role', 'admin')->first();
    if (!$admin) {
        $admin = new User();
        $admin->name = 'Administrador no encontrado';
    }

    // Determine client text based on customer type
    $tipoCliente = $quote->customer->tipo_cliente ?? ($quote->customer->customerType->name ?? 'externo');
    $clientText = $quote->customer->customerType->description ?? ($tipoCliente === 'interno'
        ? 'Cliente Interno: Este cliente es parte de la organización.'
        : 'Cliente Externo: Este cliente no pertenece a la organización.');

    $pdf = PDF::loadView('pdf.quote', compact('quote', 'isAccredited', 'admin', 'clientText'));
    return $pdf->download('cotizacion_' . $quote->quote_id . '.pdf');
}


public function showUploadForm($quote_id)
{
    $quote = Quote::with(['quoteServices', 'processes'])->findOrFail($quote_id);
    // Calcular el número de unidades (terrenos) a partir de los servicios agrupados por unidad
    // Si la cotización fue creada con el array 'units', cada grupo de servicios corresponde a una unidad
    // Aquí asumimos que cada QuoteService tiene un campo 'unit_index' o similar, si no, agrupamos por cantidad de unidades
    // Si no hay agrupación, simplemente contamos los grupos distintos de servicios asignados
    // Por compatibilidad, si no se puede determinar, usamos la cantidad de unidades según la estructura de la cotización
    $unitCount = 1;
    $unitIndexes = [];
    foreach ($quote->quoteServices as $qs) {
        if (isset($qs->unit_index)) {
            $unitIndexes[] = $qs->unit_index;
        }
    }
    if (count($unitIndexes) > 0) {
        $unitCount = count(array_unique($unitIndexes));
    } else {
        // fallback: si no hay unit_index, asumimos que cada servicio corresponde a una unidad
        $unitCount = $quote->quoteServices->count() > 0 ? $quote->quoteServices->count() : 1;
    }
    return view('cotizacion.upload', compact('quote', 'unitCount'));
}

public function upload(Request $request, $quote_id)
{
    $request->validate([
        'archivo' => 'required|file|mimes:pdf,jpg,png|max:2048',
    ]);

    try {
        $quote = Quote::findOrFail($quote_id);
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $filename = 'quote_' . $quote->quote_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/comprobantes', $filename);
            $quote->archivo = $filename;
            $quote->save();
        }
        return redirect()->route('cotizacion.index')->with('success', 'Comprobante de pago subido exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al subir archivo: ' . $e->getMessage());
        return back()->with('error', 'Error al subir el archivo: ' . $e->getMessage());
     }  
}}