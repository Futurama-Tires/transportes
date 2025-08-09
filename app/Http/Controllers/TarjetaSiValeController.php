<?php

namespace App\Http\Controllers;

use App\Models\TarjetaSiVale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TarjetaSiValeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrador|capturista']);
    }

    public function index()
    {
        $tarjetas = TarjetaSiVale::latest()->paginate(10);
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

    public function show(TarjetaSiVale $tarjeta)
    {
        return view('tarjetas.show', compact('tarjeta'));
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

    private function validateData(Request $request, $id = null)
{
    $data = $request->validate([
        'numero_tarjeta'    => ['required', 'digits:16', Rule::unique('tarjetasSiVale')->ignore($id)],
        'nip'               => ['nullable', 'digits:4'],
        'fecha_vencimiento' => ['required', 'date_format:Y-m'],
    ], [
        'numero_tarjeta.digits' => 'El número de tarjeta debe tener exactamente 16 dígitos.',
        'nip.digits'            => 'El NIP debe tener exactamente 4 dígitos.',
        'fecha_vencimiento.date_format' => 'El formato de fecha debe ser Mes/Año.',
    ]);

    // Si existe la fecha, convertir a YYYY-MM-01 para MySQL DATE
    if (!empty($data['fecha_vencimiento'])) {
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] . '-01';
    }

    return $data;
}

}
