<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    public function index()
    {
        $cotizaciones = Cotizacion::orderBy('fecha', 'desc')->get();
        return view('lista', compact('cotizaciones'));
    }

    public function create()
    {
        return view('formu');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_persona' => 'required',
            'nit' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'precio' => 'required|numeric',
            'nombre_empresa' => 'nullable|string', // Opcional
            'correo' => 'nullable|email',          // Opcional
        ]);

        // Generar id_cotizacion automáticamente
        $ultimaCotizacion = Cotizacion::orderBy('id_cotizacion', 'desc')->first();
        if ($ultimaCotizacion) {
            $ultimoNumero = (int) substr($ultimaCotizacion->id_cotizacion, 4);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        $idCotizacion = 'COT-' . str_pad($nuevoNumero, 3, '0', STR_PAD_LEFT);

        Cotizacion::create([
            'id_cotizacion' => $idCotizacion,
            'id_user' => auth()->id(),
            'nombre_empresa' => $request->nombre_empresa, // Puede ser null
            'nombre_persona' => $request->nombre_persona,
            'nit' => $request->nit,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'correo' => $request->correo, // Puede ser null
            'precio' => $request->precio,
            'estado_de_pago' => 'pendiente',
        ]);

        return redirect()->route('lista')->with('success', 'Cotización creada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        $request->validate([
            'nombre_persona' => 'required',
            'nit' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'precio' => 'required|numeric',
            'nombre_empresa' => 'nullable|string', // Opcional
            'correo' => 'nullable|email|unique:cotizacion,correo,' . $cotizacion->id_cotizacion . ',id_cotizacion', // Opcional
        ]);

        $cotizacion->update($request->only([
            'nombre_empresa',
            'nombre_persona',
            'nit',
            'direccion',
            'telefono',
            'correo',
            'precio',
        ]));

        return redirect()->route('lista')->with('success', 'Cotización actualizada correctamente.');
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->delete();
        return redirect()->route('lista')->with('success', 'Cotización eliminada correctamente.');
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        $request->validate([
            'id_cotizacion' => 'required|string|unique:cotizacion,id_cotizacion,' . $cotizacion->id_cotizacion . ',id_cotizacion',
            'estado_de_pago' => 'required|in:pendiente,pagado',
            'archivo' => 'nullable|file|max:2048',
        ]);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/cotizaciones', $filename);
            $cotizacion->archivo = $filename;
        }

        $cotizacion->id_cotizacion = $request->id_cotizacion;
        $cotizacion->estado_de_pago = $request->estado_de_pago;
        $cotizacion->save();

        return redirect()->route('lista')->with('success', 'Estado de pago y archivo actualizados correctamente.');
    }
}