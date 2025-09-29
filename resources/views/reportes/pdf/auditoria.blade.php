@php
    function nf($n, $d=2){ return is_null($n) ? '—' : number_format((float)$n, $d, '.', ','); }
@endphp
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ $titulo }}</title>
  {!! view()->make('reportes.pdf._styles')->render() !!}
</head>
<body>
  <h1 class="mb-2">{{ $titulo }}</h1>

  <div class="small muted">
    <strong>Filtros:</strong>
    @if(!empty($filtros['anio'])) Año {{ $filtros['anio'] }} @endif
    @if(!empty($filtros['desde'])) &nbsp;Desde {{ $filtros['desde'] }} @endif
    @if(!empty($filtros['hasta'])) &nbsp;Hasta {{ $filtros['hasta'] }} @endif
  </div>

  {{-- KPIs --}}
  @if(!empty($kpis))
  <div class="mb-2">
    <span class="kpi">Total: <strong>{{ nf($kpis['total'] ?? null, 0) }}</strong></span>
    <span class="kpi">Verificados: <strong>{{ nf($kpis['verificados'] ?? null, 0) }}</strong></span>
    <span class="kpi">Sin verificar: <strong>{{ nf($kpis['sin_verificar'] ?? null, 0) }}</strong></span>
  </div>
  @endif

  @if(!empty($chart_uri))
  <div class="mb-2">
    <img src="{{ $chart_uri }}" alt="Gráfica Verificación" style="width:100%; max-height:340px; object-fit:contain; border:1px solid #ddd; padding:4px;">
  </div>
  @endif

  <table>
    <thead>
      <tr>
        <th>Vehículo</th>
        <th>Estado</th>
        <th>Año</th>
        <th>Estatus</th>
        <th>Fecha verificación</th>
      </tr>
    </thead>
    <tbody>
      @forelse(($rows ?? []) as $r)
        <tr>
          <td>{{ $r['vehiculo_label'] ?? ($r['placa'] ?? '—') }}</td>
          <td>{{ $r['estado'] ?? '—' }}</td>
          <td>{{ $r['anio'] ?? '—' }}</td>
          <td>{{ $r['estatus'] ?? '—' }}</td>
          <td>{{ $r['fecha_verificacion'] ?? '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="muted">Sin datos.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>