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

  <div class="mb-2">
    <span class="kpi">Litros: <strong>{{ nf($kpis['litros'] ?? null) }}</strong></span>
    <span class="kpi">Gasto $: <strong>{{ nf($kpis['gasto'] ?? null) }}</strong></span>
    <span class="kpi">Km: <strong>{{ nf($kpis['km'] ?? null) }}</strong></span>
    <span class="kpi">$ / km: 
      <strong>
        @if(($kpis['km'] ?? 0) > 0) {{ nf(($kpis['gasto']/$kpis['km']), 4) }}
        @else — @endif
      </strong>
    </span>
  </div>

  @if(!empty($chart_uri))
  <div class="mb-2">
    <img src="{{ $chart_uri }}" alt="Gráfica Costo por km" style="width:100%; max-height:340px; object-fit:contain; border:1px solid #ddd; padding:4px;">
  </div>
@endif


  <table>
    <thead>
      <tr>
        <th>Vehículo (placa)</th>
        <th>Operador</th>
        <th class="right">Litros</th>
        <th class="right">Gasto $</th>
        <th class="right">Km</th>
        <th class="right">$ / km</th>
        <th class="right">$ / L prom.</th>
        <th class="right"># Cargas</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r['placa'] }}</td>
          <td>{{ $r['operador'] }}</td>
          <td class="right">{{ nf($r['litros']) }}</td>
          <td class="right">{{ nf($r['gasto']) }}</td>
          <td class="right">{{ nf($r['km']) }}</td>
          <td class="right">{{ nf($r['costo_km'], 4) }}</td>
          <td class="right">{{ nf($r['precio_prom'], 3) }}</td>
          <td class="right">{{ $r['num_cargas'] }}</td>
        </tr>
      @empty
        <tr><td colspan="8" class="muted">Sin datos.</td></tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Totales / Global</td>
        <td class="right">{{ nf($kpis['litros'] ?? null) }}</td>
        <td class="right">{{ nf($kpis['gasto'] ?? null) }}</td>
        <td class="right">{{ nf($kpis['km'] ?? null) }}</td>
        <td class="right">
          @if(($kpis['km'] ?? 0) > 0) {{ nf(($kpis['gasto']/$kpis['km']), 4) }} @else — @endif
        </td>
        <td class="right">
          @if(($kpis['litros'] ?? 0) > 0) {{ nf(($kpis['gasto']/$kpis['litros']), 3) }} @else — @endif
        </td>
        <td class="right">—</td>
      </tr>
    </tfoot>
  </table>
</body>
</html>
