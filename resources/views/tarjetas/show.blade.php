{{-- resources/views/tarjetas/show.blade.php --}}
<x-app-layout>
    @php
        // Formateo de número de tarjeta en grupos de 4
        if ($tarjeta->numero_tarjeta) {
            $digits = preg_replace('/\D/', '', $tarjeta->numero_tarjeta);
            $numeroFormateado = strlen($digits) === 16
                ? implode(' ', str_split($digits, 4))
                : $tarjeta->numero_tarjeta; // si no son 16 dígitos, mostrar tal cual
        } else {
            $numeroFormateado = '—';
        }

        // Mes/Año del vencimiento, tolerante a string o Carbon
        $mesAnio = $tarjeta->fecha_vencimiento
            ? (method_exists($tarjeta->fecha_vencimiento, 'format')
                ? $tarjeta->fecha_vencimiento->format('m/Y')
                : \Carbon\Carbon::parse($tarjeta->fecha_vencimiento)->format('m/Y'))
            : '—';

        $perPage = request('per_page', 15);

        // Mapeo de colores para tipo_combustible
        function tipoColor($tipo) {
            return match ($tipo) {
                'Magna'  => 'bg-green-lt',
                'Premium'=> 'bg-red-lt',
                'Diesel' => 'bg-yellow-lt',
                default  => 'bg-secondary-lt',
            };
        }
    @endphp

    {{-- HEADER estilo Tabler --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">Tarjetas SiVale</div>
                        <h2 class="page-title">
                            Tarjeta  <span class="font-monospace">{{ $numeroFormateado }}</span>
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('tarjetas.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left"></i>
                                Volver
                            </a>
                            <a href="{{ route('tarjetas.edit', $tarjeta) }}" class="btn btn-primary">
                                <i class="ti ti-edit"></i>
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="container-xl">

            {{-- KPIs / Métricas --}}
            <div class="row row-cards">
                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar me-3">
                                <i class="ti ti-gas-station"></i>
                            </span>
                            <div class="row align-items-center flex-fill">
                                <div class="col">
                                    <div class="font-weight-medium">Total de cargas</div>
                                    <div class="text-secondary">{{ number_format($stats['total_cargas'] ?? 0) }}</div>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-blue-lt">#</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar me-3">
                                <i class="ti ti-tank"></i>
                            </span>
                            <div class="row align-items-center flex-fill">
                                <div class="col">
                                    <div class="font-weight-medium">Litros cargados</div>
                                    <div class="text-secondary">{{ number_format($stats['total_litros'] ?? 0, 3) }} L</div>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-green-lt">L</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="card card-sm">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar me-3">
                                <i class="ti ti-currency-dollar"></i>
                            </span>
                            <div class="row align-items-center flex-fill">
                                <div class="col">
                                    <div class="font-weight-medium">Monto total</div>
                                    <div class="text-secondary">$ {{ number_format($stats['total_gastado'] ?? 0, 2) }}</div>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-orange-lt">$</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detalle de la tarjeta y vehículos asociados --}}
            <div class="row row-cards mt-2">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-credit-card me-2"></i>
                                Detalle de la tarjeta
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-5 text-secondary">Número</dt>
                                <dd class="col-7">
                                    <span class="font-monospace" title="Número completo">{{ $numeroFormateado }}</span>
                                </dd>

                                {{-- NIP eliminado según solicitud --}}

                                <dt class="col-5 text-secondary">Vencimiento</dt>
                                <dd class="col-7">{{ $mesAnio }}</dd>

                                <dt class="col-5 text-secondary">Created / Updated</dt>
                                <dd class="col-7">
                                    <div class="text-secondary">
                                        {{ optional($tarjeta->created_at)->format('Y-m-d H:i') }} /
                                        {{ optional($tarjeta->updated_at)->format('Y-m-d H:i') }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-truck me-2"></i>
                                Unidad vinculada
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($tarjeta->vehiculos->isEmpty())
                                <div class="text-secondary">No hay una unidad vinculada a esta tarjeta.</div>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($tarjeta->vehiculos as $v)
                                        @php
                                            $label = $v->unidad
                                                ? "#{$v->id} · {$v->unidad}"
                                                : "#{$v->id}";
                                            if ($v->placa) $label .= " · {$v->placa}";
                                        @endphp
                                        <div class="badge bg-secondary-lt">
                                            <i class="ti ti-car me-1"></i> {{ $label }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de cargas asociadas a la tarjeta --}}
            <div class="row row-cards mt-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header align-items-center">
                            <h3 class="card-title">
                                <i class="ti ti-gas-station me-2"></i>
                                Cargas realizadas con esta tarjeta
                            </h3>

                            <div class="ms-auto d-flex gap-2 align-items-center">
                                {{-- Opción (no funcional aún) de Exportar a Excel --}}
                                <a href="#"
                                   class="btn btn-success disabled"
                                   aria-disabled="true"
                                   title="Próximamente">
                                    <i class="ti ti-file-spreadsheet me-1"></i>
                                    Exportar a Excel
                                </a>

                                <form method="GET" class="d-inline-flex align-items-center">
                                    {{-- Mantener otros query params si los agregas en el futuro --}}
                                    @foreach(request()->except('per_page', 'page') as $k => $v)
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endforeach

                                    <span class="text-secondary me-2">Por página</span>
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        @foreach([10,15,25,50,100] as $pp)
                                            <option value="{{ $pp }}" @selected($perPage == $pp)>{{ $pp }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table card-table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Vehículo</th>
                                        <th>Tipo</th>
                                        <th class="text-end">Litros</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Km (ini → fin)</th>
                                        <th class="text-end">Rend. (km/L)</th>
                                        <th>Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse($cargas as $c)
                                    @php
                                        $veh = $c->vehiculo;
                                        $vehLabel = $veh?->unidad ? "#{$veh->id} · {$veh->unidad}" : "#{$veh->id}";
                                        if ($veh?->placa) $vehLabel .= " · {$veh->placa}";
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">
                                            {{ optional($c->fecha)->format('Y-m-d') ?? optional($c->created_at)->format('Y-m-d') }}
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="badge bg-blue-lt">
                                                <i class="ti ti-car me-1"></i> {{ $vehLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ tipoColor($c->tipo_combustible) }}">
                                                {{ $c->tipo_combustible ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($c->litros ?? 0, 3) }}</td>
                                        <td class="text-end">$ {{ number_format($c->precio ?? 0, 2) }}</td>
                                        <td class="text-end fw-bold">$ {{ number_format($c->total ?? 0, 2) }}</td>
                                        <td class="text-end text-secondary">
                                            {{ $c->km_inicial ?? '—' }} → {{ $c->km_final ?? '—' }}
                                        </td>
                                        <td class="text-end">{{ number_format($c->rendimiento ?? 0, 2) }}</td>
                                        <td class="text-nowrap">{{ $c->ubicacion ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">
                                            <div class="text-center text-secondary py-4">
                                                No hay cargas registradas para esta tarjeta.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($cargas->hasPages())
                            <div class="card-footer d-flex align-items-center">
                                <p class="m-0 text-secondary">
                                    Mostrando <span>{{ $cargas->firstItem() }}</span>–<span>{{ $cargas->lastItem() }}</span>
                                    de <span>{{ $cargas->total() }}</span> cargas
                                </p>
                                <div class="ms-auto">
                                    {{ $cargas->onEachSide(1)->links() }}
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
