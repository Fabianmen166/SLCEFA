<?php

namespace App\Http\Controllers;

use App\Models\CustomerType; // Asegúrate de que esta importación sea correcta
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerTypeController extends Controller
{
    public function index()
    {
        try {
            $customerTypes = CustomerType::all();
            return view('customer_types.index', compact('customerTypes'));
        } catch (\Exception $e) {
            Log::error('Error al listar tipos de cliente: ' . $e->getMessage());
            return redirect()->route('gestion_calidad.dashboard')->with('error', 'Error al cargar la lista de tipos de cliente.');
        }
    }

    public function create()
    {
        return view('customer_types.create');
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|unique:customer_types,name',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'description' => 'nullable|string',
            ]);

            CustomerType::create($validatedData);

            return redirect()->route('customer_types.index')->with('success', 'Tipo de cliente creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear tipo de cliente: ' . $e->getMessage());
            return redirect()->route('customer_types.create')->with('error', 'Error al crear el tipo de cliente: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $customerType = CustomerType::findOrFail($id);
            return view('customer_types.edit', compact('customerType'));
        } catch (\Exception $e) {
            Log::error('Error al cargar tipo de cliente para edición: ' . $e->getMessage());
            return redirect()->route('customer_types.index')->with('error', 'Error al cargar el tipo de cliente para edición.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $customerType = CustomerType::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|unique:customer_types,name,' . $id . ',customer_type_id',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'description' => 'nullable|string',
            ]);

            $customerType->update($validatedData);

            return redirect()->route('customer_types.index')->with('success', 'Tipo de cliente actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar tipo de cliente: ' . $e->getMessage());
            return redirect()->route('customer_types.edit', $id)->with('error', 'Error al actualizar el tipo de cliente: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $customerType = CustomerType::findOrFail($id);

            // Verificar si el tipo de cliente está siendo usado por algún cliente
            if ($customerType->customers()->count() > 0) {
                return redirect()->route('customer_types.index')->with('error', 'No se puede eliminar este tipo de cliente porque está asociado a uno o más clientes.');
            }

            $customerType->delete();

            return redirect()->route('customer_types.index')->with('success', 'Tipo de cliente eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar tipo de cliente: ' . $e->getMessage());
            return redirect()->route('customer_types.index')->with('error', 'Error al eliminar el tipo de cliente.');
        }
    }
}