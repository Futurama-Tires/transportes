{{-- resources/views/admin/backup.blade.php --}}
<x-app-layout>

    {{-- ===== Header (como en cargas.index) ===== --}}
    <x-slot name="header">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <p class="text-secondary text-uppercase small mb-1">Administración</p>
                        <h2 class="page-title mb-0">Respaldo y Restauración de Base de Datos</h2>
                        <div class="text-secondary small mt-1">
                            Solo administradores. Driver:
                            <strong>{{ $dbInfo['driver'] }}</strong> |
                            BD: <strong>{{ $dbInfo['database'] }}</strong> @
                            {{ $dbInfo['host'] }}:{{ $dbInfo['port'] }}
                        </div>
                    </div>

                    {{-- Botón volver al menú principal --}}
                    <div class="col-auto ms-auto">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1" aria-hidden="true"></i>
                            Volver al menú principal
                        </a>
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

            <div class="row row-cards">
                {{-- Descargar respaldo --}}
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Generar y descargar .sql</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary">
                                Se usará <code>mysqldump</code> con
                                <code>--single-transaction</code>,
                                <code>--routines</code>,
                                <code>--triggers</code> y
                                <code>--events</code>.
                            </p>
                            @unless ($hasMySqlDump)
                                <div class="alert alert-warning">
                                    No se detectó <code>mysqldump</code>. Define
                                    <strong>MYSQLDUMP_PATH</strong> en tu <code>.env</code> o agrega el binario al PATH.
                                </div>
                            @endunless
                            <form method="POST" action="{{ route('admin.backup.download') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary" {{ $hasMySqlDump ? '' : 'disabled' }}>
                                    <i class="ti ti-download me-1" aria-hidden="true"></i> Descargar SQL
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Restaurar desde archivo --}}
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Restaurar desde archivo</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary">
                                Selecciona un archivo de respaldo y confirma la restauración para sobreescribir la base de datos actual.
                                El proceso puede tardar varios minutos según el tamaño del archivo y no se puede deshacer.
                            </p>
                            @unless ($hasMysqlCli)
                                <div class="alert alert-warning">
                                    No se detectó <code>mysql</code>. Define <strong>MYSQL_CLI_PATH</strong> en <code>.env</code> o agrega el binario al PATH.
                                    Se intentará un método alterno en PHP (puede fallar con dumps que usan <code>DELIMITER</code>/procedimientos).
                                </div>
                            @endunless

                            <form method="POST"
                                  action="{{ route('admin.backup.restore') }}"
                                  enctype="multipart/form-data"
                                  onsubmit="return confirm('⚠️ Esta acción SOBREESCRIBE tu base de datos.\n\n¿Confirmas continuar?');">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Archivo SQL</label>
                                    <input type="file" class="form-control" name="sql_file" accept=".sql,.gz" required>
                                    <div class="form-hint">Selecciona un archivo .sql o .sql.gz</div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confirm" name="confirm" value="1" required>
                                    <label class="form-check-label" for="confirm">
                                        Entiendo que esta acción es irreversible y sobreescribe la base actual.
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-danger">
                                    <i class="ti ti-database-import me-1" aria-hidden="true"></i> Restaurar BD
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ====== Footer ====== --}}
            <div class="text-center text-secondary small py-4">
                © {{ date('Y') }} Futurama Tires · Todos los derechos reservados
            </div>

        </div>
    </div>
</x-app-layout>
