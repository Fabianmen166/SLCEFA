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
        $validatedData = $request->validate([
            'solicitante' => 'nullable|string|max:255',
            'contacto' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'nit' => 'required|string|max:50|unique:customers,nit',
            'correo' => 'nullable|email|max:255',
            'customer_type_id' => 'required|exists:customer_types,customer_type_id',
        ]);

        Customer::create($validatedData);

        return redirect()->route('listac')->with('success', 'Cliente creado exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al crear cliente: ' . $e->getMessage());
        $customerTypes = CustomerType::all(); // Definir $customerTypes aquí
        return view('customers.create', compact('customerTypes'))->with('error', 'Error al crear el cliente: ' . $e->getMessage())->withInput();
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