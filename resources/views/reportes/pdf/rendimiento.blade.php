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
    <span class="kpi">Rend. global:
      <strong>
        @if(($kpis['km'] ?? 0) > 0 && ($kpis['litros'] ?? 0) > 0)
          {{ nf(($kpis['km']/$kpis['litros']), 3) }} km/L
        @else — @endif
      </strong>
    </span>
  </div>

  {{-- Gráfica embebida como imagen (data URL o remota) --}}
  @if(!empty($chart_uri))
    <div class="mb-2">
      <img src="{{ $chart_uri }}" alt="Gráfica Rendimiento" style="width:100%; max-height:340px; object-fit:contain; border:1px solid #ddd; padding:4px;">
    </div>
  @endif

  <table>
    <thead>
      <tr>
        <th>Vehículo</th>
        <th>Operador</th>
        <th class="right">Km</th>
        <th class="right">Litros</th>
        <th class="right">Rend. real</th>
        <th class="right">Índice</th>
        <th class="right">Desv. %</th>
        <th class="right"># Cargas</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r['vehiculo_label'] ?? ($r['placa'] ?? '—') }}</td>
          <td>{{ $r['operador'] ?? '—' }}</td>
          <td class="right">{{ nf($r['km_recorridos']) }}</td>
          <td class="right">{{ nf($r['litros']) }}</td>
          <td class="right">{{ nf($r['rend_real'], 3) }}</td>
          <td class="right">{{ nf($r['indice'], 2) }}</td>
          <td class="right">{{ is_null($r['desviacion_pct']) ? '—' : nf($r['desviacion_pct'], 2) }}</td>
          <td class="right">{{ $r['num_cargas'] }}</td>
        </tr>
      @empty
        <tr><td colspan="8" class="muted">Sin datos.</td></tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Totales / Global</td>
        <td class="right">{{ nf($kpis['km'] ?? null) }}</td>
        <td class="right">{{ nf($kpis['litros'] ?? null) }}</td>
        <td class="right">
          @if(($kpis['km'] ?? 0) > 0 && ($kpis['litros'] ?? 0) > 0)
            {{ nf(($kpis['km']/$kpis['litros']), 3) }}
          @else — @endif
        </td>
        <td class="right">—</td>
        <td class="right">—</td>
        <td class="right">—</td>
      </tr>
    </tfoot>
  </table>
</body>
</html>