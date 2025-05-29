<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\ConductivityAnalysis;
use App\Models\Process;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class ConductivityAnalysisController extends Controller
{


public function index()
{
    // Buscar todos los análisis pendientes de conductividad que NO tienen ConductivityAnalysis asociado
    $pendingConductivityAnalyses = Analysis::with(['process', 'service'])
        ->where('status', 'pending')
        ->whereHas('service', function ($query) {
            $query->where('descripcion', 'like', '%conductividad%');
        })
        ->whereDoesntHave('conductivityAnalysis')
        ->paginate(10);

    return view('conductivity.index', ['analyses' => $pendingConductivityAnalyses]);
}

    private function extractResult($conductivityAnalysis)
    {
        if (!$conductivityAnalysis || empty($conductivityAnalysis->items_ensayo)) return 'N/A';
        $firstItem = $conductivityAnalysis->items_ensayo[0];
        return $firstItem['valor_leido_dsm'] ?? $firstItem['valor_leido'] ?? 'N/A';
    }

    /**
     * Show the form for creating a new conductivity analysis, preselecting the process if provided.
     */
    public function create(Request $request)
    {
        $processes = Process::all();
        $services = Service::all();
        $selectedProcessId = $request->query('process_id');

        return view('conductivity.create', compact('processes', 'services', 'selectedProcessId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'process_id' => 'required|exists:processes,process_id',
            'service_id' => 'required|exists:services,services_id',
            'consecutivo_no' => 'required|string|unique:conductivity_analyses,consecutivo_no',
            'equipo_utilizado' => 'required|string',
            'resolucion_instrumental' => 'required|string',
            'items_ensayo.*.codigo_interno' => 'required|string',
            'items_ensayo.*.peso_muestra' => 'required|numeric|min:0',
            'items_ensayo.*.volumen_agua' => 'required|numeric|min:0',
            'items_ensayo.*.temperatura' => 'required|numeric',
            'items_ensayo.*.lectura_uscm' => 'required|numeric|min:0',
            'blanco_valor_leido' => 'required|numeric|min:0',
            'duplicado_a_valor_leido' => 'required|numeric|min:0',
            'duplicado_b_valor_leido' => 'required|numeric|min:0',
            'veracidad.*.valor_esperado' => 'required|numeric|min:0',
            'veracidad.*.valor_leido' => 'required|numeric|min:0',
        ]);

        $analysis = Analysis::create([
            'process_id' => $request->process_id,
            'service_id' => $request->service_id,
            'status' => 'completed',
            'approved' => false,
        ]);

        $itemsEnsayo = array_map(function ($item) use ($analysis) {
            $lecturaDsm = $item['lectura_uscm'] / 1000;
            return [
                'identificacion' => $item['codigo_interno'],
                'peso' => $item['peso_muestra'],
                'volumen_agua' => $item['volumen_agua'],
                'temperatura' => $item['temperatura'],
                'valor_leido' => $item['lectura_uscm'],
                'valor_leido_dsm' => $lecturaDsm,
                'observaciones' => $item['observaciones'] ?? null,
                'analysis_id' => $analysis->id,
            ];
        }, $request->items_ensayo);

        $blancoValorLeidoDsm = $request->blanco_valor_leido / 1000;
        $blancoAceptable = $blancoValorLeidoDsm <= 0.1 ? 'Aceptable' : 'No Aceptable';
        $controlesAnaliticos = [
            [
                'identificacion' => 'Blanco del proceso',
                'valor_leido' => $request->blanco_valor_leido,
                'valor_leido_dsm' => $blancoValorLeidoDsm,
                'aceptable' => $blancoAceptable,
                'observaciones' => $request->blanco_observaciones,
            ]
        ];

        $duplicadoAValorMsm = $request->duplicado_a_valor_leido / 1000;
        $duplicadoBValorMsm = $request->duplicado_b_valor_leido / 1000;
        $promedio = ($duplicadoAValorMsm + $duplicadoBValorMsm) / 2;
        $diferencia = abs($duplicadoAValorMsm - $duplicadoBValorMsm);
        $aceptable = $this->calcularAceptabilidadPrecision($promedio, $diferencia);
        $precisionAnalitica = [
            'duplicado_a' => [
                'identificacion' => $request->duplicado_identificacion ?? 'Duplicado A',
                'valor_leido' => $request->duplicado_a_valor_leido,
                'valor_leido_msm' => $duplicadoAValorMsm,
            ],
            'duplicado_b' => [
                'identificacion' => $request->duplicado_identificacion ?? 'Duplicado B',
                'valor_leido' => $request->duplicado_b_valor_leido,
                'valor_leido_msm' => $duplicadoBValorMsm,
            ],
            'promedio' => $promedio,
            'diferencia' => $diferencia,
            'aceptable' => $aceptable,
            'observaciones' => $request->duplicado_observaciones,
        ];

        $veracidadAnalitica = array_map(function ($veracidad) {
            $recuperacion = ($veracidad['valor_leido'] / $veracidad['valor_esperado']) * 100;
            $aceptable = ($recuperacion >= 70 && $recuperacion <= 130) ? 'Aceptable' : 'No Aceptable';
            return [
                'identificacion' => $veracidad['identificacion'],
                'valor_esperado' => $veracidad['valor_esperado'],
                'valor_leido_dsm' => $veracidad['valor_leido'],
                'recuperacion' => round($recuperacion, 2) . '%',
                'aceptable' => $aceptable,
                'observaciones' => $veracidad['observaciones'] ?? null,
            ];
        }, $request->veracidad);

        ConductivityAnalysis::create([
            'analysis_id' => $analysis->id,
            'consecutivo_no' => $request->consecutivo_no,
            'fecha_analisis' => now(),
            'user_id' => Auth::id(),
            'equipo_utilizado' => $request->equipo_utilizado,
            'resolucion_instrumental' => $request->resolucion_instrumental,
            'unidades_reporte' => 'µS/cm y dS/m',
            'intervalo_metodo' => '0 a 2000 µS/cm',
            'items_ensayo' => $itemsEnsayo,
            'controles_analiticos' => $controlesAnaliticos,
            'precision_analitica' => $precisionAnalitica,
            'veracidad_analitica' => $veracidadAnalitica,
            'observaciones' => $request->observaciones_analista,
            'review_status' => 'pending',
        ]);

        return redirect()->route('conductivity.create')
                         ->with('success', 'Análisis de conductividad registrado exitosamente.');
    }

    private function calcularAceptabilidadPrecision($promedio, $diferencia)
    {
        if ($promedio <= 50) {
            return $diferencia <= 5 ? 'Aceptable' : 'No Aceptable';
        } elseif ($promedio <= 200) {
            return $diferencia <= 20 ? 'Aceptable' : 'No Aceptable';
        } else {
            $porcentajeDiferencia = ($diferencia / $promedio) * 100;
            return $porcentajeDiferencia <= 10 ? 'Aceptable' : 'No Aceptable';
        }
    }
}