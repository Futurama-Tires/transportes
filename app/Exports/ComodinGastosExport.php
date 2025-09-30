<?php

namespace App\Exports;

use App\Models\ComodinGasto;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ComodinGastosExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents,
    WithCustomStartCell,
    WithTitle,
    WithDrawings
{
    protected array $params;
    protected int $rowNumber = 0;

    private const LAST_COL = 'E';          // A..E (#, Fecha, Tarjeta, Concepto, Monto)
    private const DATA_START_ROW = 6;      // Datos desde fila 6 (encabezados en fila 5)

    /** @param Request|array $params */
    public function __construct($params = [])
    {
        $this->params = $params instanceof Request ? $params->all() : (array) $params;
    }

    public function title(): string
    {
        return 'Gastos Tarjeta';
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return ['#','Fecha','Tarjeta','Concepto','Monto'];
    }

    public function drawings()
    {
        $logoPath = public_path('images/logoOriginal2.png');
        if (!file_exists($logoPath)) return null;

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Futurama Tires');
        $drawing->setPath($logoPath);
        $drawing->setHeight(48);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(2);
        return $drawing;
    }

    public function map($g): array
    {
        $this->rowNumber++;

        $fecha   = $g->fecha ? Carbon::parse($g->fecha)->format('Y-m-d') : '—';
        $tarjeta = optional($g->tarjeta)->numero_tarjeta ?? '—';
        $concepto= $g->concepto ?? '—';
        $monto   = is_null($g->monto) ? null : (float)$g->monto;

        return [
            $this->rowNumber,
            $fecha,
            $tarjeta,
            $concepto,
            $monto,
        ];
    }

    public function query()
    {
        $p = $this->params;

        $sortBy  = $p['sort_by']  ?? 'fecha';
        $sortDir = strtolower($p['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        if (!in_array($sortBy, ['fecha','monto','concepto','id'], true)) {
            $sortBy = 'fecha';
        }

        $q = ComodinGasto::query()->with('tarjeta');
        $this->applyFilters($q, $p);

        // Orden
        if ($sortBy === 'fecha') {
            $q->orderBy('fecha', $sortDir)->orderBy('id', $sortDir);
        } else {
            $q->orderBy($sortBy, $sortDir)->orderBy('id', 'desc');
        }

        return $q;
    }

    protected function applyFilters(Builder $q, array $p): void
    {
        if (!empty($p['tarjeta'])) {
            $q->where('tarjeta_comodin_id', (int)$p['tarjeta']);
        }
        if (!empty($p['search'])) {
            $s = trim((string)$p['search']);
            $q->where('concepto', 'like', "%{$s}%");
        }
        if (!empty($p['desde'])) {
            $q->whereDate('fecha', '>=', $p['desde']);
        }
        if (!empty($p['hasta'])) {
            $q->whereDate('fecha', '<=', $p['hasta']);
        }
        if ($p['monto_min'] ?? null) {
            $q->where('monto', '>=', (float)$p['monto_min']);
        }
        if ($p['monto_max'] ?? null) {
            $q->where('monto', '<=', (float)$p['monto_max']);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $sheet   = $e->sheet->getDelegate();
                $lastCol = self::LAST_COL;

                // Encabezado visual
                $sheet->mergeCells("B1:{$lastCol}1");
                $sheet->setCellValue('B1', 'Futurama Tires');

                $sheet->mergeCells("B2:{$lastCol}2");
                $sheet->setCellValue('B2', 'Reporte de Gastos (Tarjeta Comodín)');

                $sheet->mergeCells("B3:{$lastCol}3");
                $sheet->setCellValue('B3', 'Fecha de generación: ' . now()->format('Y-m-d H:i'));

                // Banda 1
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold'=>true,'size'=>32,'color'=>['rgb'=>'F6F7EB'],'name'=>'Arial','italic'=>true],
                    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'E94F37']],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(42);

                // Banda 2
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold'=>true,'size'=>12,'name'=>'Arial','color'=>['rgb'=>'393E41']],
                    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'F6F7EB']],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Banda 3
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['size'=>10,'name'=>'Arial','color'=>['rgb'=>'f8f8ff']],
                    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'393E41']],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // Borde grueso alrededor del header
                $sheet->getStyle("A1:{$lastCol}3")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle'=>Border::BORDER_THICK,'color'=>['rgb'=>'3d0600']]],
                ]);

                // Encabezados tabla
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font'      => ['bold'=>true],
                    'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType'=>Fill::FILL_SOLID,'color'=>['rgb'=>'EFEFEF']],
                    'borders'   => ['allBorders' => ['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'3d0600']]],
                ]);
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'borders' => ['outline' => ['borderStyle'=>Border::BORDER_THICK,'color'=>['rgb'=>'3d0600']]],
                ]);
                $sheet->setAutoFilter("A5:{$lastCol}5");

                // Bordes de datos
                $dataLastRow = max(5, self::DATA_START_ROW + $this->rowNumber - 1);
                if ($dataLastRow >= self::DATA_START_ROW) {
                    $sheet->getStyle("A".self::DATA_START_ROW.":{$lastCol}{$dataLastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle'=>Border::BORDER_HAIR,'color'=>['rgb'=>'DDDDDD']]],
                    ]);
                    // Alinear # (A), Fecha (B) centradas; Monto (E) derecha
                    $sheet->getStyle("A".self::DATA_START_ROW.":B{$dataLastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E".self::DATA_START_ROW.":E{$dataLastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Ancho mínimo para columna A
                $currentWidth = $sheet->getColumnDimension('A')->getWidth();
                if ($currentWidth < 12) $sheet->getColumnDimension('A')->setWidth(12);
            },
        ];
    }
}
