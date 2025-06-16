<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\CationExchangeAnalysis;
use App\Models\Process;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CationExchangeAnalysisController extends Controller
{
    public function index()
    {
        try {
            Log::info('User accessing CationExchangeAnalysisController@index:', [
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'N/A',
            ]);

            // Obtener todos los servicios para verificar
            $services = Service::all();
            Log::info('All services:', ['services' => $services->toArray()]);

            // Temporalmente obtener todos los análisis para depuración
            $allAnalyses = Analysis::with(['process', 'service', 'cationExchangeAnalysis'])->get();

            Log::info('All analyses loaded for debugging:', ['count' => $allAnalyses->count()]);

            // Filtrar los análisis relevantes y loguear detalles
            $cationExchangeAnalyses = $allAnalyses->filter(function ($analysis) {
                $isCationExchange = $analysis->service && str_contains($analysis->service->descripcion, 'Intercambio Catiónico');

                if ($isCationExchange) {
                    Log::info('Found Cation Exchange Analysis (Debugging):', [
                        'analysis_id' => $analysis->id,
                        'process_id' => $analysis->process_id,
                        'service_id' => $analysis->service_id,
                        'service_description' => $analysis->service->descripcion,
                        'status' => $analysis->status,
                    ]);
                }

                // Filtrar solo los que están pendientes para la vista
                return $isCationExchange && $analysis->status === 'pending';
            });

            // Agrupar por proceso para la vista
            $processes = Process::whereIn('process_id', $cationExchangeAnalyses->pluck('process_id')->unique())->with([
                'quote.customer',
                'analyses' => function ($query) use ($cationExchangeAnalyses) {
                    $query->whereIn('id', $cationExchangeAnalyses->pluck('id'));
                },
            ])->get();

            Log::info('Processes filtered for view:', [
                'count' => $processes->count(),
                // 'processes' => $processes->toArray(), // Evitar loguear todos los datos del proceso si es muy grande
            ]);

            return view('cation_exchange_analyses.index', compact('processes'));
        } catch (\Exception $e) {
            Log::error('Error in CationExchangeAnalysisController@index: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('personal_tecnico.dashboard')
                           ->with('error', 'Error al cargar la gestión de análisis de intercambio catiónico: ' . $e->getMessage());
        }
    }

    public function batchProcess(Request $request)
    {
        try {
            $analysisIds = $request->input('analysis_ids');

            $query = Analysis::where('status', 'pending')
                ->whereHas('service', function ($serviceQuery) {
                    $serviceQuery->where('descripcion', 'like', '%Intercambio Catiónico%');
                })
                ->with(['service', 'process', 'cationExchangeAnalysis']);

            if (!empty($analysisIds)) {
                $query->whereIn('id', $analysisIds);
            }

            $pendingAnalyses = $query->get();

            Log::info('CationExchangeAnalysisController@batchProcess received analysis_ids:', ['analysis_ids' => $analysisIds]);

            if ($pendingAnalyses->isEmpty()) {
                return redirect()->route('cation_exchange_analysis.index')
                               ->with('error', 'No hay análisis de intercambio catiónico pendientes para procesar o los seleccionados ya fueron procesados.');
            }

            // Asegurarse de que cada análisis tenga al menos un item de ensayo por defecto si no existe o está vacío
            foreach ($pendingAnalyses as $analysis) {
                if (empty($analysis->cationExchangeAnalysis->items_ensayo) || collect($analysis->cationExchangeAnalysis->items_ensayo)->every(fn($i) => isset($i['valor_leido']) && $i['valor_leido'] !== '')) {
                    $analysis->items_ensayo = [
                        [
                            'identificacion' => 'Muestra ' . ($pendingAnalyses->search($analysis) + 1),
                            'peso' => '',
                            'vol_naoh_muestra' => '',
                            'vol_naoh_blanco' => '',
                            'humedad' => '',
                            'valor_leido' => '',
                            'observaciones' => '',
                        ]
                    ];
                } else {
                    $analysis->items_ensayo = $analysis->cationExchangeAnalysis->items_ensayo;
                }
            }

            return view('cation_exchange_analyses.batch_process', compact('pendingAnalyses'));
        } catch (\Exception $e) {
            Log::error('Error in CationExchangeAnalysisController@batchProcess: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('cation_exchange_analysis.index')
                           ->with('error', 'Error al cargar el formulario de análisis de intercambio catiónico: ' . $e->getMessage());
        }
    }

    public function storeBatchProcess(Request $request)
    {
        Log::info('CationExchangeAnalysisController@storeBatchProcess received analysis_ids:', ['analysis_ids' => $request->input('analysis_ids', [])]);
        try {
            // Validaciones de controles analíticos
            $request->validate([
                'blanco_valor_leido' => 'required|numeric|min:0|max:0.1',
                'duplicado_a_valor_leido' => 'required|numeric|min:0',
                'duplicado_b_valor_leido' => 'required|numeric|min:0',
                'veracidad_valor_esperado' => 'required|numeric|min:0.01',
                'veracidad_valor_leido' => 'required|numeric|min:0',
                'normalidad_naoh' => 'required|numeric|min:0',
                'items_ensayo.*.*.peso' => 'required|numeric|min:0.0001',
                'items_ensayo.*.*.vol_naoh_muestra' => 'required|numeric|min:0',
                'items_ensayo.*.*.vol_naoh_blanco' => 'required|numeric|min:0',
                'items_ensayo.*.*.humedad' => 'required|numeric|min:0|max:100',
                'muestra_referencia_certificada_valor_teorico' => 'required|numeric|min:0.0001',
                'muestra_referencia_certificada_valor_leido' => 'required|numeric|min:0',
                'muestra_referencia_valor_teorico' => 'required|numeric|min:0.0001',
                'muestra_referencia_valor_leido' => 'required|numeric|min:0',
                'dpr_replica_1' => 'required|numeric|min:0',
                'dpr_replica_2' => 'required|numeric|min:0',
            ], [
                'blanco_valor_leido.max' => 'El valor del blanco debe ser menor o igual a 0.1 mg/L.',
                'veracidad_valor_esperado.min' => 'El valor esperado de veracidad debe ser mayor a 0.',
                'normalidad_naoh.required' => 'La Normalidad de NaOH es requerida.',
                'items_ensayo.*.*.peso.min' => 'El peso debe ser mayor a 0.',
                'items_ensayo.*.*.humedad.max' => 'La humedad no puede ser mayor a 100%.',
                'muestra_referencia_certificada_valor_teorico.min' => 'El valor teórico de la muestra de referencia certificada debe ser mayor a 0.',
                'muestra_referencia_valor_teorico.min' => 'El valor teórico de la muestra de referencia debe ser mayor a 0.',
            ]);

            // Validación de duplicados (diferencia porcentual)
            $a = floatval($request->duplicado_a_valor_leido);
            $b = floatval($request->duplicado_b_valor_leido);
            $promedio = ($a + $b) / 2;
            $diferencia = abs($a - $b);
            $porcentaje = $promedio > 0 ? ($diferencia / $promedio) * 100 : 0;
            if ($porcentaje > 10) {
                return back()->with('error', 'La diferencia entre duplicados supera el 10% permitido.')->withInput();
            }

            // Validación de veracidad (recuperación)
            $esperado = floatval($request->veracidad_valor_esperado);
            $leido = floatval($request->veracidad_valor_leido);
            $recuperacion = $esperado > 0 ? ($leido / $esperado) * 100 : 0;
            if ($recuperacion < 70 || $recuperacion > 130) {
                return back()->with('error', 'La recuperación de veracidad debe estar entre 70% y 130%.')->withInput();
            }

            // Calculo y validación de Muestra Referencia Certificada (%ERROR)
            $mrc_valor_teorico = floatval($request->muestra_referencia_certificada_valor_teorico);
            $mrc_valor_leido = floatval($request->muestra_referencia_certificada_valor_leido);
            $mrc_error = $mrc_valor_teorico > 0 ? (abs($mrc_valor_leido - $mrc_valor_teorico) / $mrc_valor_teorico) * 100 : 0;
            $mrc_estado = ($mrc_error <= 10) ? 'Aceptable' : 'No Aceptable';
            if ($mrc_estado === 'No Aceptable') {
                return back()->with('error', 'El %ERROR de la Muestra de Referencia Certificada supera el 10% permitido.')->withInput();
            }

            // Calculo y validación de Muestra Referencia (%REC)
            $mr_valor_teorico = floatval($request->muestra_referencia_valor_teorico);
            $mr_valor_leido = floatval($request->muestra_referencia_valor_leido);
            $mr_recuperacion = $mr_valor_teorico > 0 ? ($mr_valor_leido / $mr_valor_teorico) * 100 : 0;
            $mr_estado = ($mr_recuperacion >= 90 && $mr_recuperacion <= 110) ? 'Aceptable' : 'No Aceptable';
            if ($mr_estado === 'No Aceptable') {
                return back()->with('error', 'La recuperación de la Muestra de Referencia debe estar entre 90% y 110%.')->withInput();
            }

            // Calculo y validación de DPR (%RPD)
            $dpr_replica_1 = floatval($request->dpr_replica_1);
            $dpr_replica_2 = floatval($request->dpr_replica_2);
            $dpr_promedio = ($dpr_replica_1 + $dpr_replica_2) / 2;
            $dpr_rpd = $dpr_promedio > 0 ? (abs($dpr_replica_1 - $dpr_replica_2) / $dpr_promedio) * 100 : 0;
            $dpr_estado = ($dpr_rpd <= 20) ? 'Aceptable' : 'No Aceptable';
            if ($dpr_estado === 'No Aceptable') {
                return back()->with('error', 'La Diferencia Porcentual Relativa (DPR) supera el 20% permitido.')->withInput();
            }

            // Validación de Blanco del Proceso
            $blanco_valor_leido = floatval($request->blanco_valor_leido);
            $blanco_estado = ($blanco_valor_leido <= 0.1) ? 'Aceptable' : 'No Aceptable';
            if ($blanco_estado === 'No Aceptable') {
                return back()->with('error', 'El valor del Blanco del Proceso supera el límite de detección de 0.1 mg/L.')->withInput();
            }

            $itemsEnsayoInput = $request->input('items_ensayo', []);
            $normalidadNaoh = floatval($request->normalidad_naoh);

            foreach ($request->input('analysis_ids', []) as $analysisId) {
                $analysis = Analysis::findOrFail($analysisId);

                // Filtrar los ítems de ensayo que corresponden a este análisis
                $analysisItems = $itemsEnsayoInput[$analysisId] ?? [];
                
                // Recalcular CIC para cada item de ensayo antes de guardar
                foreach ($analysisItems as $key => $item) {
                    $peso = floatval($item['peso']);
                    $volNaohMuestra = floatval($item['vol_naoh_muestra']);
                    $volNaohBlanco = floatval($item['vol_naoh_blanco']);
                    $humedad = floatval($item['humedad']);

                    $denominador = $peso * (1 - ($humedad / 100));
                    if ($denominador === 0) {
                        return back()->with('error', 'Error en el cálculo de CIC: división por cero para el item ' . ($key + 1) . ' del análisis ' . $analysisId)->withInput();
                    }
                    $cic = (($volNaohMuestra - $volNaohBlanco) * $normalidadNaoh * 100) / $denominador;
                    $analysisItems[$key]['valor_leido'] = round($cic, 4);
                }

                $controles = [
                    'blanco_valor_leido' => $request->blanco_valor_leido,
                    'blanco_observaciones' => $request->blanco_observaciones,
                    'blanco_estado' => $blanco_estado,
                ];
                $precision = [
                    'duplicado_a_valor_leido' => $request->duplicado_a_valor_leido,
                    'duplicado_b_valor_leido' => $request->duplicado_b_valor_leido,
                    'duplicado_observaciones' => $request->duplicado_observaciones,
                    'dpr_replica_1' => $request->dpr_replica_1,
                    'dpr_replica_2' => $request->dpr_replica_2,
                    'dpr_rpd_porcentaje' => round($dpr_rpd, 2),
                    'dpr_estado' => $dpr_estado,
                ];
                $veracidad = [
                    'valor_esperado' => $request->veracidad_valor_esperado,
                    'valor_leido' => $request->veracidad_valor_leido,
                    'recuperacion' => round($recuperacion, 2),
                    'veracidad_observaciones' => $request->veracidad_observaciones,
                    'mr_valor_teorico' => $request->muestra_referencia_valor_teorico,
                    'mr_valor_leido' => $request->muestra_referencia_valor_leido,
                    'mr_recuperacion_porcentaje' => round($mr_recuperacion, 2),
                    'mr_estado' => $mr_estado,
                ];

                $muestraReferenciaCertificada = [
                    'valor_teorico' => $request->muestra_referencia_certificada_valor_teorico,
                    'valor_leido' => $request->muestra_referencia_certificada_valor_leido,
                    'error_porcentaje' => round($mrc_error, 2),
                    'estado' => $mrc_estado,
                ];

                $cationExchangeAnalysis = CationExchangeAnalysis::updateOrCreate(
                    ['analysis_id' => $analysisId],
                    [
                        'consecutivo_no' => $request->consecutivo_no,
                        'fecha_analisis' => $request->fecha_analisis,
                        'user_id' => Auth::id(),
                        'process_id' => $analysis->process_id,
                        'service_id' => $analysis->service_id,
                        'normalidad_naoh' => $normalidadNaoh,
                        'controles_analiticos' => $controles,
                        'precision_analitica' => $precision,
                        'veracidad_analitica' => $veracidad,
                        'muestra_referencia_certificada_analitica' => $muestraReferenciaCertificada,
                        'items_ensayo' => array_values($analysisItems),
                        'observaciones' => $request->observaciones,
                        'review_status' => 'pending',
                    ]
                );

                $analysis->status = 'completed';
                $analysis->save();
            }

            return redirect()->route('process.technical_index')
                           ->with('success', 'Análisis de intercambio catiónico guardados exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error in CationExchangeAnalysisController@storeBatchProcess: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al guardar los análisis de intercambio catiónico: ' . $e->getMessage())->withInput();
        }
    }

    public function cationExchangeAnalysis($processId, $serviceId)
    {
        try {
            \Log::info('Accediendo a cationExchangeAnalysis', [
                'processId' => $processId,
                'serviceId' => $serviceId
            ]);

            // Obtener el proceso y el servicio
            $process = Process::with([
                'quote.customer',
                'analyses' => function($query) use ($serviceId) {
                    $query->where('service_id', $serviceId)
                          ->with(['service', 'cationExchangeAnalysis']);
                }
            ])->findOrFail($processId);

            $service = Service::findOrFail($serviceId);

            \Log::info('Datos encontrados', [
                'process' => $process->toArray(),
                'service' => $service->toArray()
            ]);

            // Obtener el análisis existente o crear uno nuevo
            $analysis = CationExchangeAnalysis::where('process_id', $processId)
                ->where('service_id', $serviceId)
                ->first();

            if (!$analysis) {
                $analysis = new CationExchangeAnalysis();
                $analysis->process_id = $processId;
                $analysis->service_id = $serviceId;
                $analysis->status = 'pending';
                // No guardar aquí si no hay datos iniciales para evitar entradas vacías
                 $analysis->save(); // Guardar para obtener un ID si es nuevo
            }

            \Log::info('Análisis encontrado/creado', [
                'analysis' => $analysis->toArray()
            ]);

            // Obtener los items de ensayo directamente del campo JSON
            $itemsEnsayo = $analysis->items_ensayo ?? [];

            // Filtrar los items que no tienen valor_leido o está vacío
            // Asumimos que un ítem está "pendiente" si le falta el valor_leido
            $pendingItems = collect($itemsEnsayo)->filter(function ($item) {
                return !isset($item['valor_leido']) || $item['valor_leido'] === '';
            });


            // Si no hay items pendientes, crear uno por defecto para el formulario
            if ($pendingItems->isEmpty() && ($analysis->status === 'pending' || is_null($analysis->items_ensayo))) {
                 $pendingItems = collect([
                    [
                        'identificacion' => 'Muestra 1',
                        'peso' => '',
                        'volumen_agua' => '',
                        'temperatura' => '',
                        'valor_leido' => '',
                        'observaciones' => ''
                    ]
                ]);
             } elseif ($pendingItems->isEmpty() && $analysis->status !== 'pending') {
                 // Si no hay items pendientes y el análisis no está pendiente, mostrar los items guardados
                 $pendingItems = collect($itemsEnsayo);
             }



            
            \Log::info('Items para la vista', [
                'pendingItemsCount' => $pendingItems->count(),
                'pendingItemsData' => $pendingItems->toArray()
            ]);

            return view('cation_exchange_analyses.process', compact('process', 'service', 'analysis', 'pendingItems'));
        } catch (\Exception $e) {
            \Log::error('Error en cationExchangeAnalysis: ' . $e->getMessage(), [
                'processId' => $processId,
                'serviceId' => $serviceId,
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('cation_exchange_analysis.index')
                ->with('error', 'Error al cargar el análisis: ' . $e->getMessage());
        }
    }

    public function storeCationExchangeAnalysis(Request $request)
    {
        try {
            // Validaciones de controles analíticos
            $request->validate([
                'blanco_valor_leido' => 'required|numeric|min:0|max:0.1', // Ejemplo: blanco debe ser <= 0.1
                'duplicado_a_valor_leido' => 'required|numeric|min:0',
                'duplicado_b_valor_leido' => 'required|numeric|min:0',
                'veracidad_valor_esperado' => 'required|numeric|min:0.01',
                'veracidad_valor_leido' => 'required|numeric|min:0',
                'normalidad_naoh' => 'required|numeric|min:0',
                'items_ensayo.*.peso' => 'required|numeric|min:0.0001',
                'items_ensayo.*.vol_naoh_muestra' => 'required|numeric|min:0',
                'items_ensayo.*.vol_naoh_blanco' => 'required|numeric|min:0',
                'items_ensayo.*.humedad' => 'required|numeric|min:0|max:100',
                'muestra_referencia_certificada_valor_teorico' => 'required|numeric|min:0.0001',
                'muestra_referencia_certificada_valor_leido' => 'required|numeric|min:0',
                'muestra_referencia_valor_teorico' => 'required|numeric|min:0.0001',
                'muestra_referencia_valor_leido' => 'required|numeric|min:0',
                'dpr_replica_1' => 'required|numeric|min:0',
                'dpr_replica_2' => 'required|numeric|min:0',
            ], [
                'blanco_valor_leido.max' => 'El valor del blanco debe ser menor o igual a 0.1 mg/L.',
                'veracidad_valor_esperado.min' => 'El valor esperado de veracidad debe ser mayor a 0.',
                'normalidad_naoh.required' => 'La Normalidad de NaOH es requerida.',
                'items_ensayo.*.peso.min' => 'El peso debe ser mayor a 0.',
                'items_ensayo.*.humedad.max' => 'La humedad no puede ser mayor a 100%.',
                'muestra_referencia_certificada_valor_teorico.min' => 'El valor teórico de la muestra de referencia certificada debe ser mayor a 0.',
                'muestra_referencia_valor_teorico.min' => 'El valor teórico de la muestra de referencia debe ser mayor a 0.',
            ]);

            // Validación de duplicados (diferencia porcentual)
            $a = floatval($request->duplicado_a_valor_leido);
            $b = floatval($request->duplicado_b_valor_leido);
            $promedio = ($a + $b) / 2;
            $diferencia = abs($a - $b);
            $porcentaje = $promedio > 0 ? ($diferencia / $promedio) * 100 : 0;
            if ($porcentaje > 10) {
                return back()->with('error', 'La diferencia entre duplicados supera el 10% permitido.')->withInput();
            }

            // Validación de veracidad (recuperación)
            $esperado = floatval($request->veracidad_valor_esperado);
            $leido = floatval($request->veracidad_valor_leido);
            $recuperacion = $esperado > 0 ? ($leido / $esperado) * 100 : 0;
            if ($recuperacion < 70 || $recuperacion > 130) {
                return back()->with('error', 'La recuperación de veracidad debe estar entre 70% y 130%.')->withInput();
            }

            // Calculo y validación de Muestra Referencia Certificada (%ERROR)
            $mrc_valor_teorico = floatval($request->muestra_referencia_certificada_valor_teorico);
            $mrc_valor_leido = floatval($request->muestra_referencia_certificada_valor_leido);
            $mrc_error = $mrc_valor_teorico > 0 ? (abs($mrc_valor_leido - $mrc_valor_teorico) / $mrc_valor_teorico) * 100 : 0;
            $mrc_estado = ($mrc_error <= 10) ? 'Aceptable' : 'No Aceptable';
            if ($mrc_estado === 'No Aceptable') {
                return back()->with('error', 'El %ERROR de la Muestra de Referencia Certificada supera el 10% permitido.')->withInput();
            }

            // Calculo y validación de Muestra Referencia (%REC)
            $mr_valor_teorico = floatval($request->muestra_referencia_valor_teorico);
            $mr_valor_leido = floatval($request->muestra_referencia_valor_leido);
            $mr_recuperacion = $mr_valor_teorico > 0 ? ($mr_valor_leido / $mr_valor_teorico) * 100 : 0;
            $mr_estado = ($mr_recuperacion >= 90 && $mr_recuperacion <= 110) ? 'Aceptable' : 'No Aceptable';
            if ($mr_estado === 'No Aceptable') {
                return back()->with('error', 'La recuperación de la Muestra de Referencia debe estar entre 90% y 110%.')->withInput();
            }

            // Calculo y validación de DPR (%RPD)
            $dpr_replica_1 = floatval($request->dpr_replica_1);
            $dpr_replica_2 = floatval($request->dpr_replica_2);
            $dpr_promedio = ($dpr_replica_1 + $dpr_replica_2) / 2;
            $dpr_rpd = $dpr_promedio > 0 ? (abs($dpr_replica_1 - $dpr_replica_2) / $dpr_promedio) * 100 : 0;
            $dpr_estado = ($dpr_rpd <= 20) ? 'Aceptable' : 'No Aceptable';
            if ($dpr_estado === 'No Aceptable') {
                return back()->with('error', 'La Diferencia Porcentual Relativa (DPR) supera el 20% permitido.')->withInput();
            }

            // Validación de Blanco del Proceso
            $blanco_valor_leido = floatval($request->blanco_valor_leido);
            $blanco_estado = ($blanco_valor_leido <= 0.1) ? 'Aceptable' : 'No Aceptable'; // Asumiendo LD = 0.1
            if ($blanco_estado === 'No Aceptable') {
                return back()->with('error', 'El valor del Blanco del Proceso supera el límite de detección de 0.1 mg/L.')->withInput();
            }

            $itemsEnsayo = $request->input('items_ensayo', []);
            $normalidadNaoh = floatval($request->normalidad_naoh);

            // Recalcular CIC para cada item de ensayo antes de guardar
            foreach ($itemsEnsayo as $key => $item) {
                $peso = floatval($item['peso']);
                $volNaohMuestra = floatval($item['vol_naoh_muestra']);
                $volNaohBlanco = floatval($item['vol_naoh_blanco']);
                $humedad = floatval($item['humedad']);

                $denominador = $peso * (1 - ($humedad / 100));
                if ($denominador === 0) {
                    // Manejar error de división por cero, o validar previamente que denominador no sea cero
                    return back()->with('error', 'Error en el cálculo de CIC: división por cero para el item ' . ($key + 1))->withInput();
                }
                $cic = (($volNaohMuestra - $volNaohBlanco) * $normalidadNaoh * 100) / $denominador;
                $itemsEnsayo[$key]['valor_leido'] = round($cic, 4); // Redondear a 4 decimales
            }

            $controles = [
                'blanco_valor_leido' => $request->blanco_valor_leido,
                'blanco_observaciones' => $request->blanco_observaciones,
                'blanco_estado' => $blanco_estado,
            ];
            $precision = [
                'duplicado_a_valor_leido' => $request->duplicado_a_valor_leido,
                'duplicado_b_valor_leido' => $request->duplicado_b_valor_leido,
                'duplicado_observaciones' => $request->duplicado_observaciones,
                'dpr_replica_1' => $request->dpr_replica_1,
                'dpr_replica_2' => $request->dpr_replica_2,
                'dpr_rpd_porcentaje' => round($dpr_rpd, 2),
                'dpr_estado' => $dpr_estado,
            ];
            $veracidad = [
                'valor_esperado' => $request->veracidad_valor_esperado,
                'valor_leido' => $request->veracidad_valor_leido,
                'recuperacion' => round($recuperacion, 2),
                'veracidad_observaciones' => $request->veracidad_observaciones,
                'mr_valor_teorico' => $request->muestra_referencia_valor_teorico,
                'mr_valor_leido' => $request->muestra_referencia_valor_leido,
                'mr_recuperacion_porcentaje' => round($mr_recuperacion, 2),
                'mr_estado' => $mr_estado,
            ];

            $muestraReferenciaCertificada = [
                'valor_teorico' => $request->muestra_referencia_certificada_valor_teorico,
                'valor_leido' => $request->muestra_referencia_certificada_valor_leido,
                'error_porcentaje' => round($mrc_error, 2),
                'estado' => $mrc_estado,
            ];

            foreach ($request->input('analysis_ids', []) as $analysisId) {
                $analysis = Analysis::findOrFail($analysisId);

                $analysisItems = array_filter($itemsEnsayo, function ($item) use ($analysisId) {
                    return (isset($item['analysis_id']) && $item['analysis_id'] == $analysisId);
                });

                $analysisItems = array_values($analysisItems);

                $cationExchangeAnalysis = CationExchangeAnalysis::updateOrCreate(
                    ['analysis_id' => $analysisId],
                    [
                        'consecutivo_no' => $request->consecutivo_no,
                        'fecha_analisis' => $request->fecha_analisis,
                        'user_id' => Auth::id(),
                        'process_id' => $analysis->process_id,
                        'service_id' => $analysis->service_id,
                        'normalidad_naoh' => $normalidadNaoh,
                        'controles_analiticos' => $controles,
                        'precision_analitica' => $precision,
                        'veracidad_analitica' => $veracidad,
                        'muestra_referencia_certificada_analitica' => $muestraReferenciaCertificada,
                        'items_ensayo' => $analysisItems,
                        'observaciones' => $request->observaciones,
                        'review_status' => 'pending',
                    ]
                );

                $analysis->status = 'completed';
                $analysis->save();
            }

            return redirect()->route('process.technical_index')
                           ->with('success', 'Análisis de intercambio catiónico guardados exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error in CationExchangeAnalysisController@storeCationExchangeAnalysis: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al guardar los análisis de intercambio catiónico: ' . $e->getMessage())->withInput();
        }
    }
} 