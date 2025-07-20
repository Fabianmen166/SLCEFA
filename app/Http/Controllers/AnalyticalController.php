<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnalyticalControl;
use App\Models\HumidityAnalysis;

class AnalyticalControlController extends Controller
{
    public function index()
    {
        $controles = AnalyticalControl::all();
        return view('analytical_controls.index', compact('controles'));
    }

    public function create()
    {
        return view('analytical_controls.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // Normalizar valores con coma como decimal
        foreach (['humedad_fortificada_teorica', 'recuperacion', 'dpr'] as $campo) {
            if (isset($data[$campo])) {
                $data[$campo] = str_replace(',', '.', $data[$campo]);
            }
        }

        AnalyticalControl::create($data);

        return redirect()->route('analytical_controls.index')->with('success', 'Control analítico guardado correctamente.');
    }

    public function show($id)
    {
        $control = AnalyticalControl::findOrFail($id);
        return view('analytical_controls.show', compact('control'));
    }

    public function edit($id)
    {
        $control = AnalyticalControl::findOrFail($id);
        return view('analytical_controls.edit', compact('control'));
    }

    public function update(Request $request, $id)
    {
        $control = AnalyticalControl::findOrFail($id);

        $data = $request->all();
        foreach (['humedad_fortificada_teorica', 'recuperacion', 'dpr'] as $campo) {
            if (isset($data[$campo])) {
                $data[$campo] = str_replace(',', '.', $data[$campo]);
            }
        }

        $control->update($data);

        return redirect()->route('analytical_controls.index')->with('success', 'Control actualizado correctamente.');
    }

    public function destroy($id)
    {
        $control = AnalyticalControl::findOrFail($id);
        $control->delete();

        return redirect()->route('analytical_controls.index')->with('success', 'Control eliminado.');
    }
}
