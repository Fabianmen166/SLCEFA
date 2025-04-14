<?php

namespace App\Http\Controllers;

use App\Models\Customer; // Importar el modelo Customer
use App\Models\CustomerType;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomersController extends Controller
{
    public function index()
    {
        $customers = Customer::with('customerType')->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $customerTypes = CustomerType::all();
        return view('customers.create', compact('customerTypes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|unique:service_packages,nombre',
                'precio' => 'required|numeric|min:0',
                'acreditado' => 'boolean',
                'services' => 'nullable|array', // Cambia a 'services'
                'services.*' => 'exists:services,services_id', // Valida cada ID en la tabla services
            ]);
    
            $servicePackage = new ServicePackage();
            $servicePackage->nombre = $validated['nombre'];
            $servicePackage->precio = $validated['precio'];
            $servicePackage->acreditado = $request->has('acreditado');
            $servicePackage->included_services = $validated['services'] ?? []; // Asigna los servicios validados
            $servicePackage->save();
    
            $success = 'Paquete de servicios creado exitosamente.';
            return redirect()->route('service_packages.index')->with('success', $success);
        } catch (\Exception $e) {
            Log::error('Error al crear paquete de servicios: ' . $e->getMessage());
            $error = 'Error al crear el paquete de servicios: ' . $e->getMessage();
            return view('service_packages.create', compact('error'));
        }
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $customerTypes = CustomerType::all();
        return view('customers.edit', compact('customer', 'customerTypes'));
    }

    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $validatedData = $request->validate([
                'solicitante' => 'nullable|string|max:255',
                'contacto' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'nit' => 'required|string|max:50|unique:customers,nit,' . $id . ',customers_id',
                'correo' => 'nullable|email|max:255',
                'customer_type_id' => 'required|exists:customer_types,customer_type_id',
            ]);

            $customer->update($validatedData);

            return redirect()->route('listac')->with('success', 'Cliente actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente: ' . $e->getMessage());
            return redirect()->route('customers.edit', $id)->with('error', 'Error al actualizar el cliente: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // Verificar si el cliente tiene cotizaciones asociadas
            if ($customer->quotes()->count() > 0) {
                return redirect()->route('listac')->with('error', 'No se puede eliminar este cliente porque tiene cotizaciones asociadas.');
            }

            $customer->delete();

            return redirect()->route('listac')->with('success', 'Cliente eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar cliente: ' . $e->getMessage());
            return redirect()->route('listac')->with('error', 'Error al eliminar el cliente.');
        }
    }
}