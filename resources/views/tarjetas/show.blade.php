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
                                <span class="material-symbols-outlined me-1 align-middle">arrow_back</span>
                                Volver
                            </a>
                            <a href="{{ route('tarjetas.edit', $tarjeta) }}" class="btn btn-primary">
                                <span class="material-symbols-outlined me-1 align-middle">edit</span>
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
                            <h3 class="card-title d-flex align-items-center gap-2">
                                <span>Detalle de la tarjeta</span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-5 text-secondary">Número de tarjeta:</dt>
                                <dd class="col-7">
                                    <span class="font-monospace" title="Número completo">{{ $numeroFormateado }}</span>
                                </dd>

                                <dt class="col-5 text-secondary">Vencimiento:</dt>
                                <dd class="col-7">{{ $mesAnio }}</dd>

                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title d-flex align-items-center gap-2">
                                <span>Unidad vinculada</span>
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
                                                ? "{$v->unidad}"
                                                : "#{$v->id}";
                                            if ($v->placa) $label .= " · {$v->placa}";
                                        @endphp
                                        <div class="badge bg-secondary-lt">
                                            <span class="material-symbols-outlined me-1 align-middle">directions_car</span>
                                            {{ $label }}
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
                            <h3 class="card-title d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined">local_gas_station</span>
                                <span>Cargas realizadas</span>
                            </h3>

                            <div class="ms-auto d-flex gap-2 align-items-center">
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
                                                <span class="material-symbols-outlined me-1 align-middle">directions_car</span>
                                                {{ $vehLabel }}
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
                                                <span class="material-symbols-outlined me-2 align-middle">database_off</span>
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

    {{-- MODALES --}}
    <div class="modal modal-blur fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="text-secondary text-uppercase small mb-1">Detalles del Vehículo</p>
                        <h3 class="modal-title h4" id="vehicleModalLabel">Vehículo</h3>
                        <div class="text-secondary small" id="vehicleModalSubtitle"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    {{-- Datos generales (sin íconos aquí) --}}

                    {{-- Fotos --}}
                    <div class="card mb-3">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0 d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined">photo_library</span>
                                <span>Fotos del vehículo</span>
                            </h4>
                            <div class="d-flex gap-2">
                                <a id="managePhotosLink" href="#" class="btn btn-outline-secondary btn-sm">
                                    <span class="material-symbols-outlined me-1 align-middle">add_photo_alternate</span>
                                    Gestionar fotos
                                </a>
                                <button id="openGalleryBtn" type="button" class="btn btn-dark btn-sm d-none">
                                    <span class="material-symbols-outlined me-1 align-middle">slideshow</span>
                                    Ver galería
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="photosEmpty" class="text-secondary small">Este vehículo no tiene fotos.</div>
                            <div id="photosGrid" class="row g-2"></div>
                        </div>
                    </div>

                    {{-- Tanques --}}
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Tanques de combustible</h4>
                            <a id="addTankLink" href="#" class="btn btn-success btn-sm">
                                <span class="material-symbols-outlined me-1 align-middle">add_box</span>
                                Agregar
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tipo</th>
                                            <th>Capacidad (L)</th>
                                            <th>Rend. (km/L)</th>
                                            <th>Km recorre</th>
                                            <th>Costo tanque lleno</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tanksTbody">
                                        <tr><td colspan="6" class="text-secondary small">Este vehículo no tiene tanques.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <a id="editVehicleLink" href="#" class="btn btn-outline-secondary">
                        <span class="material-symbols-outlined me-1 align-middle">edit</span>
                        Editar vehículo
                    </a>
                    <button class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL GALERÍA --}}
    <div class="modal modal-blur fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title h4" id="galleryModalLabel">
                        <span class="material-symbols-outlined me-2 align-middle">photo_library</span>
                        Galería de fotos
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div id="galleryCarousel" class="carousel slide" data-bs-interval="false" data-bs-touch="true">
                        <div class="carousel-inner" id="galleryInner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>

                <div class="thumbs" id="galleryThumbs"></div>
            </div>
        </div>
    </div>

</x-app-layout>
