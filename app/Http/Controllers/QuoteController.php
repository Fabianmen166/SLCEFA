<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Customers;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['customer', 'services']);

        if ($request->filled('nit')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('nit', 'like', '%' . $request->nit . '%');
            });
        }

        $quotes = $query->get();

        return view('lista', compact('quotes'));
    }

    public function create()
    {
        $customers = Customers::all();
        $services = Services::all();
        return view('cotizacion', compact('customers', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quote_id' => 'required|string|unique:quote,quote_id', // Cambiado a string, permite cualquier carácter
            'customers_id' => 'required|exists:customers,customers_id',
            'services' => 'required|array',
            'services.*' => 'exists:services,services_id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|numeric|min:1',
        ]);

        // Obtener el cliente para determinar el tipo de cliente
        $customer = Customers::findOrFail($request->customers_id);

        // Calcular el total y los subtotales
        $total = 0;
        $servicesData = [];
        foreach ($request->services as $index => $serviceId) {
            $service = Services::findOrFail($serviceId);
            $quantity = $request->quantities[$index];
            $subtotal = $service->precio * $quantity;
            $total += $subtotal;
            $servicesData[$serviceId] = [
                'cantidad' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        // Aplicar descuentos según el tipo de cliente
        switch ($customer->tipo_cliente) {
            case 'interno':
                $total = 0;
                break;
            case 'trabajador':
                $total = $total * 0.75;
                break;
            case 'externo':
            default:
                break;
        }

        // Ajustar los subtotales en proporción al descuento aplicado
        if ($customer->tipo_cliente === 'interno') {
            foreach ($servicesData as $serviceId => &$data) {
                $data['subtotal'] = 0;
            }
        } elseif ($customer->tipo_cliente === 'trabajador') {
            foreach ($servicesData as $serviceId => &$data) {
                $data['subtotal'] = $data['subtotal'] * 0.75;
            }
        }

        // Crear la cotización
        $quote = new Quote();
        $quote->quote_id = $request->quote_id;
        $quote->customers_id = $request->customers_id;
        $quote->total = $total;
        $quote->id_user = Auth::id();
        $quote->save();

        // Asociar los servicios con sus cantidades y subtotales
        $quote->services()->sync($servicesData);

        return redirect()->route('lista')->with('success', 'Cotización creada exitosamente.');
    }

    public function edit($id)
    {
        $quote = Quote::with('services')->findOrFail($id);
        $customers = Customers::all();
        $services = Services::all();
        return view('cotizacion_edit', compact('quote', 'customers', 'services'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quote_id' => 'required|string|unique:quote,quote_id,' . $id . ',quote_id', // Cambiado a string
            'customers_id' => 'required|exists:customers,customers_id',
            'services' => 'required|array',
            'services.*' => 'exists:services,services_id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|numeric|min:1',
        ]);

        $quote = Quote::findOrFail($id);

        // Obtener el cliente para determinar el tipo de cliente
        $customer = Customers::findOrFail($request->customers_id);

        // Calcular el total y los subtotales
        $total = 0;
        $servicesData = [];
        foreach ($request->services as $index => $serviceId) {
            $service = Services::findOrFail($serviceId);
            $quantity = $request->quantities[$index];
            $subtotal = $service->precio * $quantity;
            $total += $subtotal;
            $servicesData[$serviceId] = [
                'cantidad' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        // Aplicar descuentos según el tipo de cliente
        switch ($customer->tipo_cliente) {
            case 'interno':
                $total = 0;
                break;
            case 'trabajador':
                $total = $total * 0.75;
                break;
            case 'externo':
            default:
                break;
        }

        // Ajustar los subtotales en proporción al descuento aplicado
        if ($customer->tipo_cliente === 'interno') {
            foreach ($servicesData as $serviceId => &$data) {
                $data['subtotal'] = 0;
            }
        } elseif ($customer->tipo_cliente === 'trabajador') {
            foreach ($servicesData as $serviceId => &$data) {
                $data['subtotal'] = $data['subtotal'] * 0.75;
            }
        }

        $quote->quote_id = $request->quote_id;
        $quote->customers_id = $request->customers_id;
        $quote->total = $total;
        $quote->save();

        $quote->services()->sync($servicesData);

        return redirect()->route('lista')->with('success', 'Cotización actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);
        $quote->services()->detach();
        $quote->delete();
        return redirect()->route('lista')->with('success', 'Cotización eliminada exitosamente.');
    }

    public function showUploadForm($id)
    {
        $quote = Quote::findOrFail($id);
        return view('comprobante', compact('quote'));
    }

    public function uploadFile(Request $request, $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $quote = Quote::findOrFail($id);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $filename = 'quote_' . $quote->quote_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/comprobantes', $filename);
            $quote->archivo = $filename;
            $quote->save();
        }

        return redirect()->route('lista')->with('success', 'Comprobante de pago subido exitosamente.');
    }

    public function generatePDF($id)
    {
        $quote = Quote::with(['customer', 'user', 'services'])->findOrFail($id);

        $isAccredited = $quote->services->contains('acreditado', true);

        $clientText = match ($quote->customer->tipo_cliente) {
            'externo' => 'Cliente Externo: Este cliente no pertenece a la organización.',
            'interno' => 'Cliente Interno: Este cliente es parte de la organización.',
            'trabajador' => 'Trabajador: Este cliente es un empleado directo.',
            default => 'Tipo de cliente no especificado.',
        };

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'isAccredited', 'clientText'));
        return $pdf->download('cotizacion_' . $quote->quote_id . '.pdf');
    }
}