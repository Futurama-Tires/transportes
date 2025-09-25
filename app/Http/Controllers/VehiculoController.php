<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\TarjetaSiVale;
use App\Models\VehiculoFoto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// === Exportación Excel ===
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VehiculosExport;

class VehiculoController extends Controller
{
    public function __construct()
    {
        // Listar/crear/editar para admin o capturista
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    /** Listado con filtros y paginación. */
    public function index(Request $request)
    {
        // Si viene ?export=xlsx, exportamos SIN paginar, con los filtros/orden actuales
        if ($request->get('export') === 'xlsx') {
            $filename = 'vehiculos_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new VehiculosExport($request), $filename);
        }

        $sortBy  = $request->get('sort_by', 'unidad'); // ← default: unidad
        $sortDir = $request->get('sort_dir', 'asc');   // ← default: asc

        $vehiculos = Vehiculo::with(['tarjetaSiVale','tanques','fotos'])
            ->filter($request->all())
            ->sort($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $marcas = Vehiculo::select('marca')
            ->whereNotNull('marca')
            ->where('marca', '!=', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        return view('vehiculos.index', compact('vehiculos', 'marcas'));
    }

    public function create()
    {
        $tarjetas = class_exists(TarjetaSiVale::class) ? TarjetaSiVale::orderBy('id')->get() : collect();
        return view('vehiculos.create', compact('tarjetas'));
    }

    public function store(Request $request)
    {
        // Validación de campos del vehículo (incluye poliza_latino/qualitas)
        $data = $this->validateVehiculo($request);

        // Crear vehículo
        $vehiculo = Vehiculo::create($data);

        // Si vienen fotos en el create, validarlas y guardarlas (opcional)
        if ($request->hasFile('fotos')) {
            $request->validate([
                'fotos'   => ['array'],
                'fotos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            ], [
                'fotos.*.image' => 'Cada archivo debe ser una imagen.',
                'fotos.*.mimes' => 'Formatos permitidos: jpg, jpeg, png, webp.',
                'fotos.*.max'   => 'Cada imagen no debe superar los 8 MB.',
            ]);

            foreach ($request->file('fotos', []) as $file) {
                $dir = "vehiculos/{$vehiculo->id}";
                $filename = now()->format('Ymd_His') . '_' . $vehiculo->id . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

                // Guarda en disco local (privado)
                $relativePath = $file->storeAs($dir, $filename, 'local');

                VehiculoFoto::create([
                    'vehiculo_id' => $vehiculo->id,
                    'ruta'        => $relativePath,
                    'orden'       => 0,
                ]);
            }
        }

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo creado correctamente.');
    }

    public function show(Vehiculo $vehiculo)
    {
        $vehiculo->load(['tarjetaSiVale','fotos','tanques']);
        return view('vehiculos.show', compact('vehiculo'));
    }

    public function edit(Vehiculo $vehiculo)
    {
        $tarjetas = class_exists(TarjetaSiVale::class) ? TarjetaSiVale::orderBy('id')->get() : collect();
        return view('vehiculos.edit', compact('vehiculo', 'tarjetas'));
    }

    public function update(Request $request, Vehiculo $vehiculo)
    {
        // Validación de campos del vehículo (incluye poliza_latino/qualitas)
        $data = $this->validateVehiculo($request, $vehiculo->id);

        // Actualizar vehículo
        $vehiculo->update($data);

        // Si se agregan nuevas fotos desde el formulario de edición
        if ($request->hasFile('fotos')) {
            $request->validate([
                'fotos'   => ['array'],
                'fotos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            ], [
                'fotos.*.image' => 'Cada archivo debe ser una imagen.',
                'fotos.*.mimes' => 'Formatos permitidos: jpg, jpeg, png, webp.',
                'fotos.*.max'   => 'Cada imagen no debe superar los 8 MB.',
            ]);

            foreach ($request->file('fotos', []) as $file) {
                $dir = "vehiculos/{$vehiculo->id}";
                $filename = now()->format('Ymd_His') . '_' . $vehiculo->id . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

                // Guarda en disco local (privado)
                $relativePath = $file->storeAs($dir, $filename, 'local');

                VehiculoFoto::create([
                    'vehiculo_id' => $vehiculo->id,
                    'ruta'        => $relativePath,
                    'orden'       => 0,
                ]);
            }
        }

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $vehiculo->delete();

        return redirect()->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }

    private function validateVehiculo(Request $request, $vehiculoId = null): array
    {
        return $request->validate([
            'ubicacion'                 => ['required', Rule::in(['CVC', 'IXT', 'QRO', 'VALL', 'GDL'])],
            'propietario'               => ['required', 'string', 'max:255'],
            'unidad'                    => ['required', 'string', 'max:255'],
            'marca'                     => ['nullable', 'string', 'max:255'],
            'anio'                      => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'serie'                     => [
                'required', 'string', 'max:255',
                Rule::unique('vehiculos', 'serie')->ignore($vehiculoId),
            ],
            'motor'                     => ['nullable', 'string', 'max:255'],
            'placa'                     => [
                'nullable', 'string', 'max:255',
                Rule::unique('vehiculos', 'placa')->ignore($vehiculoId),
            ],
            'estado'                    => ['nullable', 'string', 'max:255'],
            // Tabla real en tu BD (según dump): tarjetassivale
            'tarjeta_si_vale_id'        => ['nullable', 'exists:tarjetassivale,id'],
            'nip'                       => ['nullable', 'string', 'max:255'],
            'fec_vencimiento'           => ['nullable', 'string', 'max:255'],
            'vencimiento_t_circulacion' => ['nullable', 'string', 'max:255'],
            'cambio_placas'             => ['nullable', 'string', 'max:255'],
            'poliza_hdi'                => ['nullable', 'string', 'max:255'],
            // Nuevos campos
            'poliza_latino'             => ['nullable', 'string', 'max:255'],
            'poliza_qualitas'           => ['nullable', 'string', 'max:255'],
        ], [
            'serie.unique' => 'La serie ya está registrada.',
            'placa.unique' => 'La placa ya está registrada.',
            'tarjeta_si_vale_id.exists' => 'La tarjeta seleccionada no es válida.',
        ]);
    }
}
