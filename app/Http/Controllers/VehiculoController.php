<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\TarjetaSiVale;
use App\Models\VehiculoFoto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $vehiculos = Vehiculo::with(['tarjetaSiVale','tanques','fotos'])
            ->filter($request->all())
            ->sort($request->get('sort_by'), $request->get('sort_dir'))
            ->paginate(25)
            ->withQueryString();

        // Datos para filtros (opcionales)
        $ubicaciones = Vehiculo::select('ubicacion')->whereNotNull('ubicacion')->distinct()->orderBy('ubicacion')->pluck('ubicacion');
        $marcas      = Vehiculo::select('marca')->whereNotNull('marca')->distinct()->orderBy('marca')->pluck('marca');
        $estados     = Vehiculo::select('estado')->whereNotNull('estado')->distinct()->orderBy('estado')->pluck('estado');
        $anios       = Vehiculo::select('anio')->whereNotNull('anio')->distinct()->orderByDesc('anio')->pluck('anio');
        $tarjetas    = class_exists(TarjetaSiVale::class) ? TarjetaSiVale::select('id')->orderBy('id')->get() : collect();

        return view('vehiculos.index', compact('vehiculos', 'ubicaciones', 'marcas', 'estados', 'anios', 'tarjetas'));
    }

    public function create()
    {
        $tarjetas = class_exists(TarjetaSiVale::class) ? TarjetaSiVale::orderBy('id')->get() : collect();
        return view('vehiculos.create', compact('tarjetas'));
    }

    public function store(Request $request)
    {
        // Validación de campos del vehículo
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

            $saved = 0;
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
                $saved++;
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
        // Validación de campos del vehículo
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

            $saved = 0;
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
                $saved++;
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
            // Usa el nombre de tabla real en tu BD:
            'tarjeta_si_vale_id'        => ['nullable', 'exists:tarjetassivale,id'],
            'nip'                       => ['nullable', 'string', 'max:255'],
            'fec_vencimiento'           => ['nullable', 'string', 'max:255'],
            'vencimiento_t_circulacion' => ['nullable', 'string', 'max:255'],
            'cambio_placas'             => ['nullable', 'string', 'max:255'],
            'poliza_hdi'                => ['nullable', 'string', 'max:255'],
            // eliminado: 'rend'
        ], [
            'serie.unique' => 'La serie ya está registrada.',
            'placa.unique' => 'La placa ya está registrada.',
            'tarjeta_si_vale_id.exists' => 'La tarjeta seleccionada no es válida.',
        ]);
    }
}
