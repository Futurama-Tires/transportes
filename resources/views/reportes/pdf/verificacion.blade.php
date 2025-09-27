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
    @if(!empty($filtros['desde'])) Desde {{ $filtros['desde'] }} @endif
    @if(!empty($filtros['hasta'])) &nbsp;Hasta {{ $filtros['hasta'] }} @endif
  </div>

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
        <th>Terminación</th>
        <th>Ventana</th>
        <th>Semestre</th>
        <th>Estatus</th>
      </tr>
    </thead>
    <tbody>
      @forelse(($rows ?? []) as $r)
        <tr>
          <td>{{ $r['placa'] ?? '—' }}</td>
          <td>{{ $r['estado'] ?? '—' }}</td>
          <td>{{ $r['terminacion'] ?? '—' }}</td>
          <td>{{ $r['ventana'] ?? '—' }}</td>
          <td>{{ $r['semestre'] ?? '—' }}</td>
          <td>{{ $r['estatus'] ?? '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="muted">Sin datos.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
