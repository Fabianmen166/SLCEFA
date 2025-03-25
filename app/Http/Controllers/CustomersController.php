<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize the query
        $query = Customers::query();

        // Filter by name (solicitante or contacto)
        if ($request->filled('nombre')) {
            $query->where('solicitante', 'like', '%' . $request->nombre . '%')
                  ->orWhere('contacto', 'like', '%' . $request->nombre . '%');
        }

        // Get the filtered customers
        $customers = $query->get();

        return view('listac', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('formuc');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'solicitante' => 'nullable|string|max:255',
            'contacto' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'nit' => 'required|string|max:255|unique:customers,nit',
            'correo' => 'nullable|email|max:255',
            'tipo_cliente' => 'required|in:externo,interno,trabajador',
        ]);

        $customers = new Customers();
        $customers->solicitante = $request->input('solicitante');
        $customers->contacto = $request->input('contacto');
        $customers->telefono = $request->input('telefono');
        $customers->nit = $request->input('nit');
        $customers->correo = $request->input('correo');
        $customers->tipo_cliente = $request->input('tipo_cliente');
        $customers->save();

        return redirect()->route('listac')->with('success', 'Cliente registrado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = Customers::findOrFail($id);
        return view('edit_customer', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customers = Customers::findOrFail($id);

        $request->validate([
            'solicitante' => 'nullable|string|max:255',
            'contacto' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'nit' => 'required|string|max:255|unique:customers,nit,' . $id . ',customers_id', // Asegurar que el NIT sea único, excepto para este cliente
            'correo' => 'nullable|email|max:255',
            'tipo_cliente' => 'required|in:externo,interno,trabajador',
        ]);

        $customers->solicitante = $request->input('solicitante');
        $customers->contacto = $request->input('contacto');
        $customers->telefono = $request->input('telefono');
        $customers->nit = $request->input('nit');
        $customers->correo = $request->input('correo');
        $customers->tipo_cliente = $request->input('tipo_cliente');
        $customers->save();

        return redirect()->route('listac')->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customers = Customers::findOrFail($id);
        $customers->delete();
        return redirect()->route('listac')->with('success', 'Cliente eliminado exitosamente.');
    }
}