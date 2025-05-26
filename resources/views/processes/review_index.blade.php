@extends('layouts.master')

@section('contenido')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Revisión de Análisis (Admin)</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Análisis Pendientes de Revisión</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Proceso</th>
                                <th>Servicio</th>
                                <th>Fecha del Análisis</th>
                                <th>Código Interno</th>
                                <th>Resultado</th>
                                <th>Estado de Revisión</th>
                                <th>Historial de Revisión</th>
                                <th>Detalles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($analyses as $analysis)
                                <tr>
                                    <td>{{ $analysis->process->item_code ?? 'N/A' }}</td>
                                    <td>{{ $analysis->description }}</td>
                                    <td>{{ $analysis->analysis_date ?? 'N/A' }}</td>
                                    <td>{{ $analysis->details['internal_code'] }}</td>
                                    <td>{{ $analysis->result }}</td>
                                    <td>
                                        @if ($analysis->review_status === 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif ($analysis->review_status === 'approved')
                                            <span class="badge badge-success">Aprobado</span>
                                        @else
                                            <span class="badge badge-danger">Rechazado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($analysis->review_date)
                                            <p><strong>Revisado por:</strong> {{ $analysis->reviewed_by }} ({{ $analysis->reviewer_role }})</p>
                                            <p><strong>Fecha:</strong> {{ $analysis->review_date }}</p>
                                            <p><strong>Estado:</strong> {{ ucfirst($analysis->review_status) }}</p>
                                            <p><strong>Observaciones:</strong> {{ $analysis->review_observations ?? 'Ninguna' }}</p>
                                            @if ($analysis->review_status === 'rejected')
                                                <p><strong>Corregido:</strong> {{ $analysis->updated_at > $analysis->review_date ? 'Sí' : 'No' }}</p>
                                            @endif
                                        @else
                                            Sin revisión previa.
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailsModal-{{ $analysis->id }}">Ver Detalles</button>
                                        <!-- Modal -->
                                        <div class="modal fade" id="detailsModal-{{ $analysis->id }}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel-{{ $analysis->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailsModalLabel-{{ $analysis->id }}">Detalles del Análisis - {{ $analysis->details['internal_code'] }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h6>Controles Analíticos</h6>
                                                        @if (!empty($analysis->details['controles_analiticos']))
                                                            <ul>
                                                                @foreach ($analysis->details['controles_analiticos'] as $control)
                                                                    <li>
                                                                        <strong>Identificación:</strong> {{ $control['identificacion'] ?? 'N/A' }}<br>
                                                                        <strong>Valor Leído (µS/cm):</strong> {{ $control['valor_leido'] ?? 'N/A' }}<br>
                                                                        <strong>Valor Leído (dS/m):</strong> {{ $control['valor_leido_dsm'] ?? 'N/A' }}<br>
                                                                        <strong>Aceptable:</strong> {{ $control['aceptable'] ?? 'N/A' }}<br>
                                                                        <strong>Observaciones:</strong> {{ $control['observaciones'] ?? 'Ninguna' }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            No disponible.
                                                        @endif

                                                        <h6>Precisión Analítica</h6>
                                                        @if (!empty($analysis->details['precision_analitica']))
                                                            <p><strong>Duplicado A:</strong> {{ $analysis->details['precision_analitica']['duplicado_a']['valor_leido'] ?? 'N/A' }} (µS/cm), {{ $analysis->details['precision_analitica']['duplicado_a']['valor_leido_msm'] ?? 'N/A' }} (mS/m)</p>
                                                            <p><strong>Duplicado B:</strong> {{ $analysis->details['precision_analitica']['duplicado_b']['valor_leido'] ?? 'N/A' }} (µS/cm), {{ $analysis->details['precision_analitica']['duplicado_b']['valor_leido_msm'] ?? 'N/A' }} (mS/m)</p>
                                                            <p><strong>Promedio:</strong> {{ $analysis->details['precision_analitica']['promedio'] ?? 'N/A' }}</p>
                                                            <p><strong>Diferencia:</strong> {{ $analysis->details['precision_analitica']['diferencia'] ?? 'N/A' }}</p>
                                                            <p><strong>Aceptable:</strong> {{ $analysis->details['precision_analitica']['aceptable'] ?? 'N/A' }}</p>
                                                            <p><strong>Observaciones:</strong> {{ $analysis->details['precision_analitica']['observaciones'] ?? 'Ninguna' }}</p>
                                                        @else
                                                            No disponible.
                                                        @endif

                                                        @if ($analysis->details['type'] === 'conductivity' && !empty($analysis->details['veracidad_analitica']))
                                                            <h6>Veracidad Analítica</h6>
                                                            <ul>
                                                                @foreach ($analysis->details['veracidad_analitica'] as $veracidad)
                                                                    <li>
                                                                        <strong>Identificación:</strong> {{ $veracidad['identificacion'] ?? 'N/A' }}<br>
                                                                        <strong>Valor Esperado (dS/m):</strong> {{ $veracidad['valor_esperado'] ?? 'N/A' }}<br>
                                                                        <strong>Valor Leído (dS/m):</strong> {{ $veracidad['valor_leido_dsm'] ?? 'N/A' }}<br>
                                                                        <strong>% Recuperación:</strong> {{ $veracidad['recuperacion'] ?? 'N/A' }}<br>
                                                                        <strong>Aceptable:</strong> {{ $veracidad['aceptable'] ?? 'N/A' }}<br>
                                                                        <strong>Observaciones:</strong> {{ $veracidad['observaciones'] ?? 'Ninguna' }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif

                                                        <h6>Ítems de Ensayo</h6>
                                                        @if (!empty($analysis->details['items_ensayo']))
                                                            <ul>
                                                                @foreach ($analysis->details['items_ensayo'] as $item)
                                                                    <li>
                                                                        <strong>Identificación:</strong> {{ $item['identificacion'] ?? 'N/A' }}<br>
                                                                        <strong>Valor Leído:</strong> {{ $item['valor_leido'] ?? 'N/A' }} @if(isset($item['valor_leido_dsm'])) (µS/cm), <strong>Valor Leído (dS/m):</strong> {{ $item['valor_leido_dsm'] }} @endif<br>
                                                                        <strong>Peso:</strong> {{ $item['peso'] ?? 'N/A' }}<br>
                                                                        <strong>Temperatura:</strong> {{ $item['temperatura'] ?? 'N/A' }}<br>
                                                                        <strong>Volumen de Agua:</strong> {{ $item['volumen_agua'] ?? 'N/A' }}<br>
                                                                        <strong>Observaciones:</strong> {{ $item['observaciones'] ?? 'Ninguna' }}<br>
                                                                        <strong>Análisis ID:</strong> {{ $item['analysis_id'] ?? 'N/A' }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            No disponible.
                                                        @endif

                                                        <h6>Información Adicional</h6>
                                                        <p><strong>Analista:</strong> {{ $analysis->details['analyst_name'] }}</p>
                                                        <p><strong>Fecha de Análisis:</strong> {{ $analysis->analysis_date ?? 'N/A' }}</p>
                                                        @if ($analysis->details['type'] === 'conductivity')
                                                            <p><strong>Equipo Utilizado:</strong> {{ $analysis->conductivityAnalysis->equipo_utilizado ?? 'N/A' }}</p>
                                                            <p><strong>Resolución Instrumental:</strong> {{ $analysis->conductivityAnalysis->resolucion_instrumental ?? 'N/A' }}</p>
                                                            <p><strong>Unidades de Reporte:</strong> {{ $analysis->conductivityAnalysis->unidades_reporte ?? 'N/A' }}</p>
                                                            <p><strong>Intervalo del Método:</strong> {{ $analysis->conductivityAnalysis->intervalo_metodo ?? 'N/A' }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="{{ route('process.store_review', $analysis->id) }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <select name="review_status" class="form-control" required>
                                                    <option value="approved">Aprobar</option>
                                                    <option value="rejected">Rechazar</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <textarea name="review_observations" class="form-control" placeholder="Observaciones"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">No hay análisis pendientes de revisión.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $analyses->links() }}
                </div>
            </div>
        </div>
    </section>

    <!-- Incluir jQuery y Bootstrap JS para el modal -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection