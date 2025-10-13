<?php

namespace App\Http\Controllers;

use App\Models\TarjetaSiVale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TarjetaSiValeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    public function index(Request $request)
    {
        $tarjetas = TarjetaSiVale::query()
            ->search($request->input('search'))
            ->ultimos4($request->input('ultimos4'))
            ->tieneNip($request->input('tiene_nip'))
            ->venceFrom($request->input('from'))
            ->venceTo($request->input('to'))
            ->estado($request->input('estado'))
            ->ordenar($request->input('sort_by'), $request->input('sort_dir'))
            ->paginate((int) $request->input('per_page', 10))
            ->withQueryString();

        return view('tarjetas.index', compact('tarjetas'));
    }

    public function create()
    {
        return view('tarjetas.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        TarjetaSiVale::create($data);

        return redirect()
            ->route('tarjetas.index')
            ->with('success', 'Tarjeta SiVale creada correctamente.');
    }

    public function show(TarjetaSiVale $tarjeta, Request $request)
    {
        $tarjeta->load('vehiculos');

        $cargasQuery = $tarjeta->cargas()
            ->with(['vehiculo:id,unidad,placa,serie'])
            ->orderByDesc('fecha');

        $stats = [
            'total_cargas'  => (clone $cargasQuery)->count(),
            'total_litros'  => (clone $cargasQuery)->sum('litros'),
            'total_gastado' => (clone $cargasQuery)->sum('total'),
        ];

        $perPage = (int) $request->get('per_page', 15);
        $cargas  = $cargasQuery->paginate($perPage)->withQueryString();

        return view('tarjetas.show', compact('tarjeta', 'cargas', 'stats'));
    }

    public function edit(TarjetaSiVale $tarjeta)
    {
        return view('tarjetas.edit', compact('tarjeta'));
    }

    public function update(Request $request, TarjetaSiVale $tarjeta)
    {
        $data = $this->validateData($request, $tarjeta->id);
        $tarjeta->update($data);

        return redirect()
            ->route('tarjetas.index')
            ->with('success', 'Tarjeta SiVale actualizada correctamente.');
    }

    public function destroy(TarjetaSiVale $tarjeta)
    {
        $tarjeta->delete();

        return redirect()
            ->route('tarjetas.index')
            ->with('success', 'Tarjeta SiVale eliminada correctamente.');
    }

    /**
     * Valida datos (tolerante a guiones/espacios) y asegura UNIQUE
     * sobre el valor normalizado (solo dígitos).
     */
    private function validateData(Request $request, $id = null): array
    {
        $table = (new TarjetaSiVale())->getTable();

        $rules = [
            'numero_tarjeta' => [
                'required',
                'regex:/^[\d\-\s]{4,25}$/',
                function ($attribute, $value, $fail) use ($table, $id) {
                    $normalized = preg_replace('/\D+/', '', (string) $value);
                    $len = strlen($normalized);
                    if ($len < 4 || $len > 16) {
                        $fail('El número de tarjeta debe tener entre 4 y 16 dígitos (sin contar guiones/espacios).');
                        return;
                    }
                    $exists = DB::table($table)
                        ->where('numero_tarjeta', $normalized)
                        ->when($id, fn($q) => $q->where('id', '!=', $id))
                        ->exists();
                    if ($exists) {
                        $fail('Ya existe una tarjeta con ese número.');
                    }
                },
            ],
            'nip' => ['nullable', 'digits:4'],
            'fecha_vencimiento' => ['nullable', 'string', 'max:20'],
            'descripcion'       => ['nullable', 'string', 'max:1000'],
        ];

        $messages = [
            'numero_tarjeta.required' => 'El número de tarjeta es obligatorio.',
            'numero_tarjeta.regex'    => 'El número de tarjeta solo puede contener dígitos, espacios y guiones.',
            'nip.digits'              => 'El NIP debe tener exactamente 4 dígitos.',
            'fecha_vencimiento.max'   => 'La fecha de vencimiento es demasiado larga.',
            'descripcion.max'         => 'La descripción no puede exceder 1000 caracteres.',
        ];

        return $request->validate($rules, $messages);
    }
}
