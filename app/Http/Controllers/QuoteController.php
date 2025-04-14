<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['customer', 'services', 'servicePackages', 'user']);

        if ($request->filled('nit')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('nit', 'like', '%' . $request->nit . '%');
            })->orWhere('quote_id', 'like', '%' . $request->nit . '%');
        }

        $quotes = $query->get();
        return view('cotizacion.index', compact('quotes')); // Ajusta la vista según tu estructura
    }

    public function lista(Request $request) // Añadido para 'cotizacion.lista'
    {
        return $this->index($request); // Reutiliza la lógica de index
    }

    public function create()
    {
        $customers = Customer::all();
        $services = Service::all();
        $servicePackages = ServicePackage::all();
        return view('cotizacion.create', compact('customers', 'services', 'servicePackages')); // Ajusta la vista
    }

    public function store(Request $request)
{
    if (!Auth::check()) {
        return back()->with('error', 'Debes estar autenticado para crear una cotización.');
    }

    $validator = Validator::make($request->all(), [
        'quote_id' => 'required|string|unique:quotes,quote_id',
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

    if (empty($request->input('services', [])) && empty($request->input('service_packages', []))) {
        $validator->errors()->add('services', 'Debes seleccionar al menos un servicio o un paquete de servicios.');
    }

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        $validatedData = $validator->validated();
        $customer = Customer::with('customerType')->findOrFail($validatedData['customers_id']);
        $total = 0;
        $servicesData = [];
        $servicePackagesData = [];

        // Procesar servicios
        if (!empty($validatedData['services'])) {
            foreach ($validatedData['services'] as $index => $serviceId) {
                if ($serviceId && isset($validatedData['quantities'][$index]) && $validatedData['quantities'][$index] > 0) {
                    $service = Service::findOrFail($serviceId);
                    $quantity = (int) $validatedData['quantities'][$index];
                    $subtotal = $service->precio * $quantity;
                    $total += $subtotal;
                    $servicesData[$serviceId] = [
                        'cantidad' => $quantity,
                        'subtotal' => $subtotal,
                        'service_packages_id' => null,
                    ];
                }
            }
        }

        // Procesar paquetes
        if (!empty($validatedData['service_packages'])) {
            foreach ($validatedData['service_packages'] as $index => $packageId) {
                if ($packageId && isset($validatedData['package_quantities'][$index]) && $validatedData['package_quantities'][$index] > 0) {
                    $package = ServicePackage::findOrFail($packageId);
                    $quantity = (int) $validatedData['package_quantities'][$index];
                    $subtotal = $package->precio * $quantity;
                    $total += $subtotal;
                    $servicePackagesData[$packageId] = [
                        'cantidad' => $quantity,
                        'subtotal' => $subtotal,
                        'services_id' => null,
                    ];
                }
            }
        }

        $discount = $customer->customerType->discount_percentage / 100 ?? 0;
        if ($discount > 0) {
            $total = $total * (1 - $discount);
            foreach ($servicesData as &$data) {
                $data['subtotal'] = $data['subtotal'] * (1 - $discount);
            }
            foreach ($servicePackagesData as &$data) {
                $data['subtotal'] = $data['subtotal'] * (1 - $discount);
            }
        }

        $quote = new Quote();
        $quote->quote_id = $validatedData['quote_id'];
        $quote->customers_id = $validatedData['customers_id'];
        $quote->total = $total;
        $quote->user_id = Auth::id();
        $quote->save();

        if (!empty($servicesData)) {
            $quote->services()->sync($servicesData);
        }

        if (!empty($servicePackagesData)) {
            $quote->servicePackages()->sync($servicePackagesData);
        }

        return redirect()->route('cotizacion.index')->with('success', 'Cotización creada exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al crear cotización: ' . $e->getMessage());
        return back()->with('error', 'Error al crear la cotización: ' . $e->getMessage())->withInput();
    }
}

    public function show($quote_id)
    {
        $quote = Quote::with(['customer', 'services', 'servicePackages', 'user'])->findOrFail($quote_id);
        return view('cotizaciones.show', compact('quote')); // Añade esta vista si la necesitas
    }

    public function edit($quote_id)
    {
        $quote = Quote::with(['services', 'servicePackages'])->findOrFail($quote_id);
        $customers = Customer::all();
        $services = Service::all();
        $servicePackages = ServicePackage::all();
        return view('cotizacion.edit', compact('quote', 'customers', 'services', 'servicePackages'));
    }

    public function update(Request $request, $quote_id)
    {
        $validator = Validator::make($request->all(), [
            'quote_id' => 'required|string|unique:quotes,quote_id,' . $quote_id . ',quote_id',
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
    
        if (empty($request->input('services', [])) && empty($request->input('service_packages', []))) {
            $validator->errors()->add('services', 'Debes seleccionar al menos un servicio o un paquete de servicios.');
        }
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
    
        try {
            $validatedData = $validator->validated();
            $quote = Quote::findOrFail($quote_id);
            $customer = Customer::with('customerType')->findOrFail($validatedData['customers_id']);
            $total = 0;
            $servicesData = [];
            $servicePackagesData = [];
    
            // Procesar servicios
            if (!empty($validatedData['services'])) {
                foreach ($validatedData['services'] as $serviceId) {
                    if ($serviceId && isset($validatedData['quantities'][$serviceId]) && $validatedData['quantities'][$serviceId] > 0) {
                        $service = Service::findOrFail($serviceId);
                        $quantity = (int) $validatedData['quantities'][$serviceId];
                        $subtotal = $service->precio * $quantity;
                        $total += $subtotal;
                        $servicesData[$serviceId] = [
                            'cantidad' => $quantity,
                            'subtotal' => $subtotal,
                            'service_packages_id' => null,
                        ];
                    }
                }
            }
    
            // Procesar paquetes
            if (!empty($validatedData['service_packages'])) {
                foreach ($validatedData['service_packages'] as $packageId) {
                    if ($packageId && isset($validatedData['package_quantities'][$packageId]) && $validatedData['package_quantities'][$packageId] > 0) {
                        $package = ServicePackage::findOrFail($packageId);
                        $quantity = (int) $validatedData['package_quantities'][$packageId];
                        $subtotal = $package->precio * $quantity;
                        $total += $subtotal;
                        $servicePackagesData[$packageId] = [
                            'cantidad' => $quantity,
                            'subtotal' => $subtotal,
                            'services_id' => null,
                        ];
                    }
                }
            }
    
            $discount = $customer->customerType->discount_percentage / 100 ?? 0;
            if ($discount > 0) {
                $total = $total * (1 - $discount);
                foreach ($servicesData as &$data) {
                    $data['subtotal'] = $data['subtotal'] * (1 - $discount);
                }
                foreach ($servicePackagesData as &$data) {
                    $data['subtotal'] = $data['subtotal'] * (1 - $discount);
                }
            }
    
            $quote->quote_id = $validatedData['quote_id'];
            $quote->customers_id = $validatedData['customers_id'];
            $quote->total = $total;
            $quote->save();
    
            if (!empty($servicesData)) {
                $quote->services()->sync($servicesData);
            } else {
                $quote->services()->detach();
            }
    
            if (!empty($servicePackagesData)) {
                $quote->servicePackages()->sync($servicePackagesData);
            } else {
                $quote->servicePackages()->detach();
            }
    
            return redirect()->route('cotizacion.index')->with('success', 'Cotización actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar cotización: ' . $e->getMessage());
            return back()->with('error', 'Error al actualizar la cotización: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($quote_id)
    {
        try {
            $quote = Quote::findOrFail($quote_id);
            $quote->services()->detach();
            $quote->servicePackages()->detach();
            $quote->delete();
            return redirect()->route('cotizacion.index')->with('success', 'Cotización eliminada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar cotización: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar la cotización: ' . $e->getMessage());
        }
    }

    public function createMinima() // Corrige 'createMinima' para que coincida con la ruta
    {
        $customers = Customer::all();
        $services = Service::all();
        $servicePackages = ServicePackage::all();
        return view('cotizaciones.create_minima', compact('customers', 'services', 'servicePackages'));
    }

    public function upload(Request $request, $quote_id) // Corrige 'upload' para que coincida con la ruta
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
    }

    public function comprobante($quote_id) // Corrige 'comprobante' para que coincida con la ruta
    {
        try {
            $quote = Quote::with(['customer.customerType', 'user', 'services', 'servicePackages'])->findOrFail($quote_id);
            $isAccredited = $quote->services->contains('acreditado', true) || $quote->servicePackages->contains('acreditado', true);
            $clientText = $quote->customer->customerType->additional_info ?? 'Información no especificada.';
            $pdf = Pdf::loadView('pdf.quote', compact('quote', 'isAccredited', 'clientText'));
            return $pdf->download('cotizacion_' . $quote->quote_id . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            return redirect()->route('cotizacion.index')->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
}