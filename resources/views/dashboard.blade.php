{{-- resources/views/dashboard.blade.php (versión Tabler) --}}
<x-app-layout>
    {{-- HEADER --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a>Inicio</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Panel</li>
                            </ol>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="page-title mb-0">Panel de Administración</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- BODY --}}
    <div class="page-body">
        <div class="container-xl">

            {{-- Mensajes por rol (discretos) --}}
            <div class="mb-3">
                @role('administrador')
                    <div class="alert alert-success py-2 mb-2" role="alert">
                        <i class="ti ti-shield-check me-2"></i> Perfil: Administrador
                    </div>
                @endrole
                @role('capturista')
                    <div class="alert alert-primary py-2 mb-2" role="alert">
                        <i class="ti ti-edit me-2"></i> Perfil: Capturista
                    </div>
                @endrole
                @role('operador')
                    <div class="alert alert-warning py-2 mb-2" role="alert">
                        <i class="ti ti-steering-wheel me-2"></i> Perfil: Operador
                    </div>
                @endrole
            </div>

            {{-- Grid de accesos (cards limpias, ejecutivas) --}}
            <div class="row row-cards">

                @hasanyrole('administrador|capturista')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('cargas.index') }}" class="card card-link bg-azure-lt" aria-label="Gestión de cargas">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-azure text-white me-3">
                                <i class="ti ti-gas-station"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Cargas de combustible</div>
                                <div class="text-secondary small">Gestionar cargas de combustible.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('operadores.index') }}" class="card card-link bg-green-lt" aria-label="Operadores">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-green text-white me-3">
                                <i class="ti ti-users"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Operadores</div>
                                <div class="text-secondary small">Gestionar los operadores del sistema.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endhasanyrole

                @role('administrador')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('capturistas.index') }}" class="card card-link bg-purple-lt" aria-label="Capturistas">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-purple text-white me-3">
                                <i class="ti ti-id-badge-2"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Capturistas</div>
                                <div class="text-secondary small">Gestionar los capturistas del sistema.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endrole

                @hasanyrole('administrador|capturista')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('vehiculos.index') }}" class="card card-link bg-yellow-lt" aria-label="Gestión de Vehículos">
                        <div class="card-body d-flex align-items-center">
                            {{-- En amarillo es mejor icono oscuro para contraste --}}
                            <span class="avatar bg-yellow text-dark me-3">
                                <i class="ti ti-truck"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Vehículos</div>
                                <div class="text-secondary small">Gestionar los vehículos del sistema.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endhasanyrole

                {{-- NUEVO: Programa de verificación (semestre/terminación) --}}
                @hasanyrole('administrador|capturista')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('programa-verificacion.index') }}" class="card card-link bg-lime-lt" aria-label="Programa de verificación">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-lime text-dark me-3">
                                <i class="ti ti-calendar-stats"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Programa de verificación</div>
                                <div class="text-secondary small">Gestionar verificaciones.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endhasanyrole

                {{-- NUEVO: Reglas de verificación (crear/publicar/generar periodos) --}}
                @role('administrador')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('verificacion-reglas.index') }}" class="card card-link bg-teal-lt" aria-label="Reglas de verificación">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-teal text-white me-3">
                                <i class="ti ti-adjustments-alt"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Reglas de verificación</div>
                                <div class="text-secondary small">Crear reglas por estado y generar calendario.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endrole

                @hasanyrole('administrador|capturista')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('tarjetas.index') }}" class="card card-link bg-cyan-lt" aria-label="Tarjetas SiVale">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-cyan text-white me-3">
                                <i class="ti ti-credit-card"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Tarjetas SiVale</div>
                                <div class="text-secondary small">Gestionar las tarjetas SiVale.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endhasanyrole

                @hasanyrole('administrador|capturista')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('tarjetas-comodin.index') }}" class="card card-link bg-cyan-lt" aria-label="Tarjeta comodín">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-cyan text-white me-3">
                                <i class="ti ti-credit-card"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Tarjetas comodín</div>
                                <div class="text-secondary small">Consultar las tarjetas tipo comodín.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endhasanyrole

                @role('administrador')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('admin.backup.index') }}" class="card card-link bg-indigo-lt" aria-label="Bases de datos" rel="noopener">
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-indigo text-white me-3">
                                <i class="ti ti-database"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Bases de datos</div>
                                <div class="text-secondary small">Respaldo y restauración de la base de datos.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endrole

                @hasanyrole('administrador|capturista')
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="{{ route('reportes.index') }}" class="card card-link bg-orange-lt" aria-label="Reportes" >
                        <div class="card-body d-flex align-items-center">
                            <span class="avatar bg-orange text-white me-3">
                                <i class="ti ti-chart-bar"></i>
                            </span>
                            <div class="flex-fill">
                                <div class="card-title mb-1">Reportes</div>
                                <div class="text-secondary small">Consultar y exportar reportes del sistema.</div>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </div>
                    </a>
                </div>
                @endhasanyrole

            </div>
                

            {{-- (Opcional) Zona de KPIs resumidos - deja comentado si aún no tienes datos
            <div class="row row-cards mt-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="text-secondary">Cargas del mes</div>
                                    <div class="h3 mb-0">{{ $kpis['cargasMes'] ?? '—' }}</div>
                                </div>
                                <div class="ms-auto">
                                    <i class="ti ti-gas-station text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Más cards KPI si es necesario -->
            </div>
            --}}

            {{-- FOOTER --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>

        </div>
    </div>
</x-app-layout>
