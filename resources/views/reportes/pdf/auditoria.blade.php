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
    @if(!empty($filtros['desde'])) Desde {{ $filtros['desde'] }} @endif
    @if(!empty($filtros['hasta'])) &nbsp;Hasta {{ $filtros['hasta'] }} @endif
    @if(!empty($filtros['tipo_comb'])) &nbsp;Tipo: {{ $filtros['tipo_comb'] }} @endif
    @if(!empty($filtros['destino'])) &nbsp;Destino: “{{ $filtros['destino'] }}” @endif
  </div>

  @if(!empty($chart_uri))
  <div class="mb-2">
    <img src="{{ $chart_uri }}" alt="Gráfica Auditoría" style="width:100%; max-height:340px; object-fit:contain; border:1px solid #ddd; padding:4px;">
  </div>
@endif


  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Placa</th>
        <th class="right">Litros</th>
        <th class="right">Precio</th>
        <th class="right">Total $</th>
        <th class="right">Cap. (L)</th>
        <th class="right">KM ini</th>
        <th class="right">KM fin</th>
        <th>Flags</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r['fecha'] }}</td>
          <td>{{ $r['placa'] }}</td>
          <td class="right">{{ nf($r['litros']) }}</td>
          <td class="right">{{ nf($r['precio'], 3) }}</td>
          <td class="right">{{ nf($r['total']) }}</td>
          <td class="right">{{ nf($r['cap_litros'], 1) }}</td>
          <td class="right">{{ $r['km_inicial'] ?? '—' }}</td>
          <td class="right">{{ $r['km_final'] ?? '—' }}</td>
          <td>{{ !empty($r['flags']) ? implode(', ', $r['flags']) : '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="9" class="muted">Sin datos.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
