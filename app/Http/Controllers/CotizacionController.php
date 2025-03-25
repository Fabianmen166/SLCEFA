<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Cotizacion::query();

        if ($request->filled('nombre')) {
            $query->where('nombre_persona', 'like', '%' . $request->nombre . '%')
                  ->orWhere('nombre_empresa', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('estado_de_pago')) {
            $query->where('estado_de_pago', $request->estado_de_pago);
        }

        $cotizaciones = $query->with('user')->orderBy('fecha', 'desc')->get();

        // Depuración temporal
        // dd($cotizaciones);

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
            'nombre_empresa' => 'nullable|string',
            'correo' => 'nullable|email',
        ]);

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
            'nombre_empresa' => $request->nombre_empresa,
            'nombre_persona' => $request->nombre_persona,
            'nit' => $request->nit,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
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
            'nombre_empresa' => 'nullable|string',
            'correo' => 'nullable|email|unique:cotizacion,correo,' . $cotizacion->id_cotizacion . ',id_cotizacion',
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
        $cotizacion = Cotizacion::where('id_cotizacion', $id)->firstOrFail();

        // Depuración temporal
        // dd([
        //     'id' => $id,
        //     'cotizacion' => $cotizacion,
        //     'request_data' => $request->all()
        // ]);

        $request->validate([
            'estado_de_pago' => 'required|in:pendiente,pagado',
            'archivo' => 'nullable|file|max:2048',
        ]);

        $cotizacion->estado_de_pago = $request->estado_de_pago;

        if ($request->hasFile('archivo')) {
            if ($cotizacion->archivo) {
                \Storage::delete('public/cotizaciones/' . $cotizacion->archivo);
            }
            $filename = time() . '_' . $request->file('archivo')->getClientOriginalName();
            $request->file('archivo')->storeAs('public/cotizaciones', $filename);
            $cotizacion->archivo = $filename;
        }

        $cotizacion->save();

        return redirect()->route('lista')->with('success', 'Estado de pago actualizado correctamente.');
    }
}