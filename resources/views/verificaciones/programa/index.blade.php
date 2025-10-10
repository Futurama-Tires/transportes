{{-- resources/views/programa_verificacion/index.blade.php --}}
<x-app-layout>
    {{-- Si tu layout ya incluye Vite y app.js, puedes quitar esta línea --}}
    @vite(['resources/js/app.js'])

    {{-- ================= HEADER (estilo de cargas) ================= --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a>Inicio</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a>Panel</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Programa de verificaciones</li>
                    </ol>

                    <div class="col">
                        <h2 class="page-title mb-0">Programa de verificaciones</h2>
                    </div>

                    {{-- CTA principal (opcional) --}}
                    <div class="col-auto ms-auto">
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">
            {{-- Flash / errores --}}
            @if (session('success'))
                <div class="alert alert-success my-3">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger my-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ================= Toolbar de filtros ================= --}}
            <form method="get" action="{{ url()->current() }}" aria-label="Filtros del programa de verificaciones">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-auto">
                                <label class="form-label">Año</label>
                                <input type="number" min="2000" max="2999" name="anio" value="{{ $anio }}" class="form-control" />
                            </div>
                            <div class="col-12 col-md-auto">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    @foreach ($estadosDisponibles as $e)
                                        <option value="{{ $e }}" @selected($estado===$e)>{{ $e }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-auto">
                                <label class="form-label">Semestre</label>
                                <select name="semestre" class="form-select">
                                    <option value="" @selected(!$semestre)>Ambos</option>
                                    <option value="1" @selected($semestre==='1' || $semestre===1)>1er semestre</option>
                                    <option value="2" @selected($semestre==='2' || $semestre===2)>2º semestre</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-auto">
                                <button class="btn btn-primary">
                                    <i class="ti ti-filter me-1"></i> Aplicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- ================= Contenido ================= --}}
            @forelse ($dataSemestres as $s => $info)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">
                            {{ $s===1 ? '1er' : '2º' }} semestre {{ $anio }}
                        </h3>
                        <div class="card-subtitle">
                            Rango: {{ $info['rango']['desde']->toDateString() }} — {{ $info['rango']['hasta']->toDateString() }}
                        </div>
                    </div>

                    <div class="card-body">
                        @php
                            $terminaciones = array_keys($info['terminaciones']); sort($terminaciones);
                            $MES = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

                            $mesNombre = function($val) use ($MES) {
                                if (!$val) return null;
                                if (is_int($val) || ctype_digit((string)$val)) {
                                    $m = (int)$val; return $MES[$m] ?? null;
                                }
                                try {
                                    $c = \Illuminate\Support\Carbon::parse($val);
                                    return $MES[$c->month] ?? null;
                                } catch (\Throwable $e) {
                                    return null;
                                }
                            };

                            $extraePeriodo = function(array $grupo) {
                                // Regresa [desde, hasta] en el formato original disponible (Carbon|string|int mes)
                                $candDesde = $grupo['rango']['desde']    ?? $grupo['desde']           ?? $grupo['periodo']['desde'] ?? $grupo['periodo']['inicio'] ?? $grupo['bimestre']['desde'] ?? ($grupo['bimestre'][0] ?? null);
                                $candHasta = $grupo['rango']['hasta']    ?? $grupo['hasta']           ?? $grupo['periodo']['hasta'] ?? $grupo['periodo']['fin']    ?? $grupo['bimestre']['hasta'] ?? ($grupo['bimestre'][1] ?? null);
                                return [$candDesde, $candHasta];
                            };
                        @endphp

                        @if(empty($terminaciones))
                            <div class="text-muted">No hay periodos definidos para este semestre/estado.</div>
                        @else
                            <div class="row g-3">
                                @foreach ($terminaciones as $dig)
                                    @php
                                        $grupo       = $info['terminaciones'][$dig] ?? [];
                                        $pendientes  = $grupo['pendientes']  ?? [];
                                        $verificados = $grupo['verificados'] ?? [];

                                        // BIMESTRE de la terminación (siempre mostramos si viene en la estructura)
                                        [$desdeTerm, $hastaTerm] = $extraePeriodo($grupo);
                                        $mesDesde = $mesNombre($desdeTerm);
                                        $mesHasta = $mesNombre($hastaTerm);

                                        // Inferencia si no viene explícito
                                        if ((!$mesDesde || !$mesHasta) && !empty($pendientes)) {
                                            $mesDesde = $mesDesde ?: $mesNombre($pendientes[0]['desde'] ?? null);
                                            $mesHasta = $mesHasta ?: $mesNombre($pendientes[0]['hasta'] ?? null);
                                        } elseif ((!$mesDesde || !$mesHasta) && !empty($verificados)) {
                                            $firstGroupRows = collect($verificados)->first();
                                            if (is_array($firstGroupRows) && !empty($firstGroupRows)) {
                                                $mesDesde = $mesDesde ?: $mesNombre($firstGroupRows[0]['desde'] ?? null);
                                                $mesHasta = $mesHasta ?: $mesNombre($firstGroupRows[0]['hasta'] ?? null);
                                            }
                                        }
                                    @endphp

                                    <div class="col-12 col-md-3">
                                        <div class="card h-100">
                                            <div class="card-header py-2">
                                                <div class="w-100">
                                                    <div class="card-title mb-1 d-flex align-items-center justify-content-between">
                                                        <span class="me-2">Terminación <span class="badge bg-blue text-white">{{ $dig }}</span></span>
                                                    </div>
                                                    <div class="card-subtitle small text-secondary">
                                                        Periodo: {{ ($mesDesde && $mesHasta) ? ($mesDesde.' — '.$mesHasta) : '—' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body pt-2">
                                                {{-- Pendientes --}}
                                                <h4 class="mb-2 h5">
                                                    <i class="ti ti-alert-circle"></i> Pendientes
                                                    <span class="badge bg-yellow text-dark">{{ count($pendientes) }}</span>
                                                </h4>
                                                @if (count($pendientes)===0)
                                                    <div class="text-muted mb-2 small">Nada pendiente.</div>
                                                @else
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($pendientes as $item)
                                                            @php $v = $item['vehiculo']; @endphp
                                                            <li class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                                                <div class="small">
                                                                    <strong>{{ $v->unidad ?? 'Unidad' }}</strong>
                                                                    <span>— {{ $v->placa }}</span>
                                                                </div>
                                                                <div class="ms-2">
                                                                    <button class="btn btn-sm btn-outline-primary"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#modalMarcar"
                                                                        data-vehiculo="{{ $v->id }}"
                                                                        data-placa="{{ $v->placa }}"
                                                                        data-unidad="{{ $v->unidad ?? '' }}"
                                                                        data-estado="{{ $estado }}"
                                                                        data-desde="{{ $item['desde']->toDateString() }}"
                                                                        data-hasta="{{ $item['hasta']->toDateString() }}">
                                                                        <i class="ti ti-check"></i>
                                                                    </button>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif

                                                {{-- Verificados (agrupados por fecha) --}}
                                                <h4 class="mt-3 mb-2 h5">
                                                    <i class="ti ti-calendar-check"></i> Verificados
                                                </h4>
                                                @if (empty($verificados))
                                                    <div class="text-muted small">Aún no hay verificaciones registradas.</div>
                                                @else
                                                    @foreach (collect($verificados)->sortKeysDesc() as $fecha => $arr)
                                                        <div class="mb-1">
                                                            <div class="small fw-semibold">{{ $fecha }}</div>
                                                            <ul class="list-unstyled mb-1">
                                                                @foreach ($arr as $row)
                                                                    @php $v = $row['vehiculo']; $ver = $row['verificacion']; @endphp
                                                                    <li class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                                                        <div class="small">
                                                                            <strong>{{ $v->unidad ?? 'Unidad' }}</strong>
                                                                            <span>— {{ $v->placa }}</span>
                                                                            @if($ver->comentarios)
                                                                                <div class="small text-secondary text-truncate">{{ $ver->comentarios }}</div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="ms-2">
                                                                            <button class="btn btn-sm btn-outline-secondary"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#modalMarcar"
                                                                                data-vehiculo="{{ $v->id }}"
                                                                                data-placa="{{ $v->placa }}"
                                                                                data-unidad="{{ $v->unidad ?? '' }}"
                                                                                data-estado="{{ $estado }}"
                                                                                data-desde="{{ $row['desde']->toDateString() }}"
                                                                                data-hasta="{{ $row['hasta']->toDateString() }}"
                                                                                data-fecha="{{ \Illuminate\Support\Carbon::parse($ver->fecha_verificacion)->toDateString() }}"
                                                                                data-comentarios="{{ $ver->comentarios }}">
                                                                                <i class="ti ti-pencil"></i>
                                                                            </button>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">No hay datos que mostrar. Genera periodos primero.</div>
                    </div>
                </div>
            @endforelse

            {{-- Footer --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>
        </div>
    </div>

    {{-- Modal: Marcar como completada --}}
    <div class="modal modal-blur fade" id="modalMarcar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="post" action="{{ route('programa-verificacion.marcar') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-check"></i> Marcar verificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="vehiculo_id" id="mv-vehiculo-id">
                    <input type="hidden" name="estado" id="mv-estado">
                    <input type="hidden" name="desde" id="mv-desde">
                    <input type="hidden" name="hasta" id="mv-hasta">

                    <div class="mb-2">
                        <label class="form-label">Vehículo</label>
                        <input type="text" id="mv-vehiculo-label" class="form-control" disabled>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Fecha de verificación</label>
                        <input type="date" name="fecha" id="mv-fecha" class="form-control" required>
                        <small class="form-hint">
                            Puedes registrar una fecha fuera del periodo programado; se contará para este semestre.
                        </small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Comentarios</label>
                        <textarea name="comentarios" id="mv-comentarios" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</a>
                    <button class="btn btn-primary">
                        <i class="ti ti-device-floppy"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalMarcar');
        modal?.addEventListener('show.bs.modal', event => {
            const btn = event.relatedTarget;
            const vehiculo = btn.getAttribute('data-vehiculo');
            const placa = btn.getAttribute('data-placa') || '';
            const unidad = btn.getAttribute('data-unidad') || '';
            const estado = btn.getAttribute('data-estado');
            const desde = btn.getAttribute('data-desde');
            const hasta = btn.getAttribute('data-hasta');
            const fecha = btn.getAttribute('data-fecha') || '';
            const comentarios = btn.getAttribute('data-comentarios') || '';

            document.getElementById('mv-vehiculo-id').value = vehiculo;
            document.getElementById('mv-estado').value = estado;
            document.getElementById('mv-desde').value = desde;
            document.getElementById('mv-hasta').value = hasta;

            // Mostrar "Unidad — Placa" en el modal
            const label = (unidad ? unidad : 'Unidad') + ' — ' + placa;
            document.getElementById('mv-vehiculo-label').value = label;

            document.getElementById('mv-fecha').value = fecha;

            // Opcionales si existen en tu modal
            const desdeTextEl = document.getElementById('mv-desde-text');
            const hastaTextEl = document.getElementById('mv-hasta-text');
            if (desdeTextEl) desdeTextEl.innerText = desde;
            if (hastaTextEl) hastaTextEl.innerText = hasta;

            document.getElementById('mv-comentarios').value = comentarios;
        });
    </script>
</x-app-layout>
