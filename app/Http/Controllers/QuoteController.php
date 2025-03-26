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
        $q->where('nit', 'like', '%' . $request->nit . '%')
          ->orWhere('quote_id', 'like', '%' . $request->nit . '%'); // Flecha correcta aquí
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
            case 'Proveedor':
                $total = 0;
                break;
            case 'interno':
                $total = $total * 0.75;
                break;
            case 'externo':
            default:
                break;
        }

        // Ajustar los subtotales en proporción al descuento aplicado
        if ($customer->tipo_cliente === 'Proveedor') {
            foreach ($servicesData as $serviceId => &$data) {
                $data['subtotal'] = 0;
            }
        } elseif ($customer->tipo_cliente === 'interno') {
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
            case 'Proveedor':
                $total = 0;
                break;
            case 'interno':
                $total = $total * 0.75;
                break;
            case 'externo':
            default:
                break;
        }

        // Ajustar los subtotales en proporción al descuento aplicado
        if ($customer->tipo_cliente === 'Proveedor') {
            foreach ($servicesData as $serviceId => &$data) {
                $data['subtotal'] = 0;
            }
        } elseif ($customer->tipo_cliente === 'interno') {
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
            'externo' => 'Si el cliente acepta esta oferta, manifestarlo a través del envío de un correo electrónico a la st-angostura@sena.edu.co, haciendo referencia al número de la cotización o, si lo prefiere de forma presencial o a través de una llamada al teléfono de contacto. La entrega de las muestras al laboratorio se realizará únicamente después de que el cliente presente la cotización firmada y pagada. El pago de la cotización se debe realizar en las instalaciones del punto de venta del Centro De Formación Agroindustrial "La Angostura", km 38 vía al sur de Neiva, de lunes a viernes de 08:00 h a 12:00 h y de 13:30 h a 16:00 h, o si prefiere, realizar consignación bancaria en Bancolombia con el recibo de pago generado en www.sena.edu.co, en el menú servicios al ciudadano/pagos en linea (en esta aplicación debe crear un usuario si lo hace por primera vez).',
            'interno' => 'Por ser cliente interno y de acuerdo a lo definido en el Comité de Precios de Servicios Tecnológicos 2024, usted no pagará/ pagará la mitad del el valor de esta cotización; esta se realiza con el objetivo de evidenciar la respectiva atención de la solicitud y, en caso de que esta sea aceptada y aprobada, cuantificar a modo de representanción el valor total brindado por parte del equipo de Servicios Tecnológicos como apoyo a los procesos misionales del Centro de Formación.Si el cliente acepta esta oferta debe gestionar la aprobacion del servicio por medio del Dinamizador SENNOVA / Coordinador Académico, quien enviará un correo confirmando la aprobación a la dirección xxxxx@sena.edu.co, haciendo referencia al número de la cotización. La entrega de las muestras al laboratorio se realizará unicamente después de recibir la aprobación del dinamizador SENNOVA/Coordinador Académico.',
            'Proveedor' => 'En caso de que la solicitud provenga del SENA, ya sea del mismo o de otro Centro de Formación, el servicio se debe tramitar a través de la estrategia SENA proveedor SENA, teniendo en cuenta el documento GRF-I-005 Instructivo Aplicación Estrategia SENA Proveedor SENA y SENA Autoconsumo disponible en la plataforma Compromiso. ',
            default => 'Tipo de cliente no especificado.',
        };

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'isAccredited', 'clientText'));
        return $pdf->download('cotizacion_' . $quote->quote_id . '.pdf');
    }
}