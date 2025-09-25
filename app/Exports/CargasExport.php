<?php

namespace App\Exports;

use App\Models\CargaCombustible;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CargasExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents,
    WithCustomStartCell,
    WithTitle,
    WithDrawings,
    WithColumnFormatting
{
    protected array $params;
    protected int $rowNumber = 0;

    /** 16 columnas (# + 15 campos) => A..P */
    private const LAST_COL = 'P';
    private const DATA_START_ROW = 6;

    /**
     * @param  Request|array  $params
     */
    public function __construct($params = [])
    {
        $this->params = $params instanceof Request ? $params->all() : (array) $params;
    }

    public function title(): string
    {
        return 'Cargas';
    }

    /**
     * Respeta filtros y orden actuales (sin paginar).
     */
    public function query()
    {
        return CargaCombustible::query()
            ->with(['vehiculo:id,unidad,placa,ubicacion', 'operador:id,nombre,apellido_paterno,apellido_materno'])
            ->filter($this->params);
    }

    public function startCell(): string
    {
        return 'A5'; // encabezados en fila 5; datos desde la 6
    }

    /**
     * Logo en A1 (busca varios archivos posibles).
     */
    public function drawings()
    {
        $candidatos = [
            public_path('images/logoOriginal2.png'),
            public_path('images/logoOriginal.png'),
            public_path('images/logo.png'),
        ];
        $logoPath = null;
        foreach ($candidatos as $p) {
            if (file_exists($p)) { $logoPath = $p; break; }
        }
        if (!$logoPath) return null;

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

    /**
     * Columnas (alineadas con tu tabla del index + placa).
     */
    public function headings(): array
    {
        return [
            '#',               // A
            'Fecha',           // B
            'Vehículo',        // C (unidad)
            'Placa',           // D
            'Operador',        // E
            'Tipo',            // F
            'Litros',          // G
            'Precio',          // H
            'Total',           // I
            'Rendimiento',     // J
            'KM Inicial',      // K
            'KM Final',        // L
            'KM Recorridos',   // M
            'Destino',         // N
            'Custodio',        // O
            'Observaciones',   // P
        ];
    }

    public function map($c): array
    {
        $this->rowNumber++;

        $veh = $c->vehiculo;
        $ope = $c->operador;

        $operadorNombre = $ope
            ? trim(collect([$ope->nombre ?? '', $ope->apellido_paterno ?? '', $ope->apellido_materno ?? ''])
                ->filter(fn($x) => $x !== '')
                ->implode(' '))
            : '—';

        $fechaStr = $c->fecha ? \Illuminate\Support\Carbon::parse($c->fecha)->format('Y-m-d') : '—';
        $kmRec = is_null($c->recorrido)
            ? ((is_numeric($c->km_final) && is_numeric($c->km_inicial)) ? ((int)$c->km_final - (int)$c->km_inicial) : null)
            : (int)$c->recorrido;

        return [
            $this->rowNumber,                                  // #
            $fechaStr,                                         // Fecha (string)
            $veh->unidad     ?? '—',                           // Unidad
            $veh->placa      ?? '—',                           // Placa
            $operadorNombre  ?: '—',                           // Operador
            $c->tipo_combustible ?? '—',                       // Tipo
            is_null($c->litros) ? null : (float)$c->litros,    // Litros
            is_null($c->precio) ? null : (float)$c->precio,    // Precio
            is_null($c->total)  ? null : (float)$c->total,     // Total
            is_null($c->rendimiento) ? null : (float)$c->rendimiento, // Rendimiento
            $c->km_inicial,                                    // KM Inicial (int|null)
            $c->km_final,                                      // KM Final (int|null)
            is_null($kmRec) ? null : (int)$kmRec,              // KM Recorridos
            $c->destino       ?? '—',                          // Destino
            $c->custodio      ?? '—',                          // Custodio
            ($c->observaciones ?? $c->comentarios ?? '—'),     // Observaciones
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '0.000',       // Litros
            'H' => '#,##0.00',    // Precio
            'I' => '#,##0.00',    // Total
            'J' => '0.00',        // Rendimiento
            'K' => '#,##0',       // KM Inicial
            'L' => '#,##0',       // KM Final
            'M' => '#,##0',       // KM Recorridos
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $sheet   = $e->sheet->getDelegate();
                $lastCol = self::LAST_COL;

                // Banners
                $sheet->mergeCells("B1:{$lastCol}1");
                $sheet->setCellValue('B1', 'Futurama Tires');

                $sheet->mergeCells("B2:{$lastCol}2");
                $sheet->setCellValue('B2', 'Reporte de Cargas de Combustible');

                $sheet->mergeCells("B3:{$lastCol}3");
                $sheet->setCellValue('B3', 'Fecha de generación: ' . now()->format('Y-m-d H:i'));

                // Estilo fila 1
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 32, 'color' => ['rgb' => 'F6F7EB'], 'name' => 'Arial', 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E94F37']],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(42);

                // Estilo fila 2
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'name' => 'Arial', 'color' => ['rgb' => '393E41']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F6F7EB']],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Estilo fila 3
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['size' => 10, 'name' => 'Arial', 'color' => ['rgb' => 'f8f8ff']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '393E41']],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // Borde grueso del header visual
                $sheet->getStyle("A1:{$lastCol}3")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '3d0600']],
                    ],
                ]);

                // Encabezados de la tabla (fila 5)
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFEFEF']],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '3d0600']],
                    ],
                ]);
                // Borde grueso alrededor del header de columnas
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '3d0600']],
                    ],
                ]);

                // Autofiltro
                $sheet->setAutoFilter("A5:{$lastCol}5");

                // Bordes finos para datos
                $dataLastRow = max(5, self::DATA_START_ROW + $this->rowNumber - 1);
                if ($dataLastRow >= self::DATA_START_ROW) {
                    $sheet->getStyle("A".self::DATA_START_ROW.":{$lastCol}{$dataLastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'DDDDDD']],
                        ],
                    ]);

                    // Alineaciones útiles
                    $sheet->getStyle("A".self::DATA_START_ROW.":A{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // #

                    // Numéricos a la derecha
                    foreach (['G','H','I','J','K','L','M'] as $col) {
                        $sheet->getStyle("{$col}".self::DATA_START_ROW.":{$col}{$dataLastRow}")
                              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    // Fecha centrada
                    $sheet->getStyle("B".self::DATA_START_ROW.":B{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Ancho mínimo para la columna A
                $currentWidth = $sheet->getColumnDimension('A')->getWidth();
                if ($currentWidth < 12) {
                    $sheet->getColumnDimension('A')->setWidth(12);
                }
            },
        ];
    }
}
