<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportFilters
{
    public ?string $desde;
    public ?string $hasta;
    /** @var int[] */
    public array $vehiculos;
    /** @var int[] */
    public array $operadores;
    public ?string $destino;
    public ?string $tipo_comb;
    public ?int $anio;

    public static function fromRequest(Request $r): self
    {
        $self = new self();

        $self->desde      = $r->filled('desde') ? (string) $r->input('desde') : null;
        $self->hasta      = $r->filled('hasta') ? (string) $r->input('hasta') : null;
        $self->vehiculos  = array_values(array_filter(array_map('intval', (array) $r->input('vehiculos', []))));
        $self->operadores = array_values(array_filter(array_map('intval', (array) $r->input('operadores', []))));
        $self->destino    = $r->filled('destino')   ? (string) $r->input('destino')   : null;
        $self->tipo_comb  = $r->filled('tipo_comb') ? (string) $r->input('tipo_comb') : null;
        $self->anio       = $r->filled('anio')      ? (int) $r->input('anio')         : null;

        return $self;
    }

    /** WHERE para cargas_combustible (alias cc) + params por referencia */
    public function whereClauseCC(array &$params): string
    {
        $w = [];

        if ($this->desde) { $w[] = "cc.fecha >= ?"; $params[] = $this->desde; }
        if ($this->hasta) { $w[] = "cc.fecha <= ?"; $params[] = $this->hasta; }

        if (count($this->vehiculos)) {
            $w[] = "cc.vehiculo_id IN (" . implode(',', array_fill(0, count($this->vehiculos), '?')) . ")";
            array_push($params, ...$this->vehiculos);
        }

        if (count($this->operadores)) {
            $w[] = "cc.operador_id IN (" . implode(',', array_fill(0, count($this->operadores), '?')) . ")";
            array_push($params, ...$this->operadores);
        }

        if ($this->destino) {
            $w[] = "cc.destino LIKE ?";
            $params[] = '%'.$this->destino.'%';
        }

        if ($this->tipo_comb) {
            $w[] = "LOWER(cc.tipo_combustible) = LOWER(?)";
            $params[] = $this->tipo_comb;
        }

        return count($w) ? ('WHERE ' . implode(' AND ', $w)) : '';
    }

    /** Paginación en memoria */
    public function paginate(array $rows, int $perPage = 25, int $page = 1): array
    {
        $page    = max(1, (int)$page);
        $perPage = min(200, max(1, (int)$perPage));

        $total    = count($rows);
        $lastPage = (int) max(1, ceil($total / $perPage));
        $page     = min($page, $lastPage);
        $offset   = ($page - 1) * $perPage;

        $data = array_slice($rows, $offset, $perPage);

        return [
            'data' => array_values($data),
            'meta' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'from'         => $total ? ($offset + 1) : 0,
                'to'           => $total ? min($offset + $perPage, $total) : 0,
            ],
        ];
    }

    /** KPIs comunes: litros, gasto, km, costo_km */
    public function numbers(array $rows): array
    {
        $litros = 0; $gasto = 0; $km = 0;
        foreach ($rows as $r) {
            $litros += (float)($r['litros'] ?? 0);
            $gasto  += (float)($r['gasto'] ?? 0);
            $km     += (float)($r['km_recorridos'] ?? $r['km'] ?? 0);
        }
        $costoKm = $km > 0 ? $gasto / $km : 0;

        return [
            'litros'   => round($litros, 2),
            'gasto'    => round($gasto, 2),
            'km'       => round($km, 2),
            'costo_km' => round($costoKm, 4),
        ];
    }

    /** Resumen de filtros para el PDF */
    public function filtroResumen(): array
    {
        return [
            'desde'      => $this->desde,
            'hasta'      => $this->hasta,
            'vehiculos'  => $this->vehiculos,
            'operadores' => $this->operadores,
            'destino'    => $this->destino,
            'tipo_comb'  => $this->tipo_comb,
            'anio'       => $this->anio,
        ];
    }

    /** Etiqueta "unidad - placa" con fallback */
    public function makeVehiculoLabel(?string $unidad, ?string $placa): string
    {
        $u = trim((string)($unidad ?? ''));
        $p = trim((string)($placa ?? ''));
        if ($u !== '' && $p !== '') return $u.' - '.$p;
        return $u !== '' ? $u : $p;
    }

    /** Normaliza chart_uri (data:, http/https, o base64 pelona) */
    public function normalizeChartUri(?string $raw): ?string
    {
        if (!$raw) return null;
        $raw = trim($raw);
        if (str_starts_with($raw, 'data:image/')) return $raw;
        if (preg_match('#^https?://#i', $raw)) return $raw;
        if (preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $raw) && strlen($raw) > 200) {
            return 'data:image/png;base64,' . preg_replace('/\s+/', '', $raw);
        }
        return null;
    }

    /** Normaliza estado (p.ej., EDOMEX) */
    public function normalizeEstado(?string $s): string
    {
        $norm = $s ? Str::of($s)->ascii()->upper()->replaceMatches('/\s+/', ' ')->trim() : '';
        $val = (string)$norm;
        if (in_array($val, ['ESTADO DE MEXICO','MEXICO','EDO MEXICO','EDO. MEX','E DOMEX'], true)) {
            return 'EDO MEX';
        }
        return $val;
    }

    /** Último dígito de la placa (ignora letras al final) */
    public function ultimaCifraDePlaca(?string $placa): ?int
    {
        if (!$placa) return null;
        $str = strtoupper(trim($placa));
        for ($i = strlen($str) - 1; $i >= 0; $i--) {
            if (ctype_digit($str[$i])) return intval($str[$i]);
        }
        return null;
    }
}
