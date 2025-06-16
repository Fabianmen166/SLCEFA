@php
    $pendingPhAnalyses = [];
    foreach ($processes as $process) {
        $phAnalyses = $process->pendingPhAnalyses()->get();
        foreach ($phAnalyses as $analysis) {
            $pendingPhAnalyses[] = [
                'process_id' => $process->process_id,
                'service_id' => $analysis->service_id,
                'analysis_id' => $analysis->id,
                'descripcion' => $analysis->service->descripcion,
                'cantidad' => $analysis->cantidad,
            ];
        }
    }
@endphp

@if (empty($pendingPhAnalyses))
    <p>No hay análisis de pH pendientes.</p>
@else
    <form action="{{ route('ph_analysis.batch_process') }}" method="POST">
        @csrf
        <div class="alert alert-info">
            <p><strong>Instrucciones:</strong> Seleccione hasta 20 análisis de pH para procesar en un solo lote. Después de 20 análisis, se deben repetir los controles analíticos.</p>
        </div>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Proceso</th>
                    <th>Servicio</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingPhAnalyses as $index => $phAnalysis)
                    <tr>
                        <td>
                            <input type="checkbox" name="analyses[]" value="{{ $phAnalysis['analysis_id'] }}" class="ph-checkbox">
                        </td>
                        <td>{{ $phAnalysis['process_id'] }}</td>
                        <td>{{ $phAnalysis['descripcion'] }}</td>
                        <td>{{ $phAnalysis['cantidad'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary" id="process-batch-btn" disabled>
            Procesar Lote de Análisis de pH
        </button>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.ph-checkbox');
            const submitButton = document.getElementById('process-batch-btn');

            function updateButtonState() {
                const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                if (checkedCount > 0 && checkedCount <= 20) {
                    submitButton.disabled = false;
                } else {
                    submitButton.disabled = true;
                }

                if (checkedCount > 20) {
                    alert('No puede seleccionar más de 20 análisis de pH a la vez.');
                    Array.from(checkboxes).filter(cb => cb.checked).slice(20).forEach(cb => cb.checked = false);
                }
            }

            checkboxes.forEach(cb => cb.addEventListener('change', updateButtonState));
        });
    </script>
@endif 