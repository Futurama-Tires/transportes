<?php

namespace App\Exports;

use App\Models\Vehiculo;
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
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class VehiculosExport extends DefaultValueBinder implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents,
    WithCustomStartCell,
    WithTitle,
    WithDrawings,
    WithColumnFormatting,
    WithCustomValueBinder
{
    protected array $params;
    protected int $rowNumber = 0;

    /** Última columna usada (A..S) -> 19 columnas incluyendo # */
    private const LAST_COL = 'S';

    /** Fila donde empiezan los datos (después del header visual) */
    private const DATA_START_ROW = 6;

    /**
     * @param  Request|array  $params
     */
    public function __construct($params = [])
    {
        $this->params = $params instanceof Request ? $params->all() : (array) $params;
    }

    /**
     * Exporta TODOS los registros que cumplen filtros/orden actuales (sin paginar).
     */
    public function query()
    {
        $sortBy  = $this->params['sort_by']  ?? 'unidad';
        $sortDir = $this->params['sort_dir'] ?? 'asc';
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $q = Vehiculo::query()
            ->with(['tarjetaSiVale','tanques','fotos'])
            ->filter($this->params);

        if (method_exists(Vehiculo::query()->getModel(), 'scopeSort')) {
            $q->sort($sortBy, $sortDir);
        } else {
            $q->orderBy($sortBy, $sortDir);
        }

        return $q;
    }

    public function title(): string
    {
        return 'Vehículos';
    }

    /**
     * Columnas: todas (salvo id), más la numeración "#".
     */
    public function headings(): array
    {
        return [
            '#',
            'Ubicación',
            'Propietario',
            'Unidad',
            'Marca',
            'Año',
            'Serie',
            'Motor',
            'Placa',
            'Estado',
            'Tarjeta SiVale',     // número de tarjeta (no ID)
            'NIP',                // desde tarjetasSiVale.nip
            'Venc. tarjeta',
            'Venc. circulación',
            'Cambio de placas',
            'Póliza HDI',
            'Póliza Latino',
            'Póliza Qualitas',
            'Kilómetros',
        ];
    }

    public function startCell(): string
    {
        return 'A5'; // Encabezados en fila 5; datos desde la 6
    }

    /**
     * Logo en A1 (igual que en OperadoresExport).
     */
    public function drawings()
    {
        $logoPath = public_path('images/logoOriginal2.png');
        if (!file_exists($logoPath)) {
            return null;
        }

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
     * Mapeo de cada fila: numeración + atributos (sin id).
     * Tarjeta SiVale: número como string (evita 5.2E+15).
     * NIP: se toma de la relación tarjetasSiVale.nip y se exporta como string.
     * Venc. tarjeta: usa $v->fec_vencimiento o, si está vacío, la fecha de la relación.
     */
    public function map($v): array
    {
        $this->rowNumber++;

        // Tarjeta como STRING
        $numeroTarjeta = optional($v->tarjetaSiVale)->numero_tarjeta;
        $numeroTarjeta = ($numeroTarjeta !== null && $numeroTarjeta !== '') ? (string) $numeroTarjeta : '—';

        // NIP desde la relación, como STRING
        $nipRel = optional($v->tarjetaSiVale)->nip;
        $nip    = ($nipRel !== null && trim((string)$nipRel) !== '') ? (string) $nipRel : '—';

        $vencTarjeta = $v->fec_vencimiento ?: optional($v->tarjetaSiVale)->fecha_vencimiento;

        return [
            $this->rowNumber,                               // A
            $v->ubicacion          ?? '—',                  // B
            $v->propietario        ?? '—',                  // C
            $v->unidad             ?? '—',                  // D
            $v->marca              ?? '—',                  // E
            $v->anio               ?? '—',                  // F
            $v->serie              ?? '—',                  // G
            $v->motor              ?? '—',                  // H
            $v->placa              ?? '—',                  // I
            $v->estado             ?? '—',                  // J
            $numeroTarjeta,                                  // K
            $nip,                                            // L
            $vencTarjeta           ?: '—',                  // M
            $v->vencimiento_t_circulacion ?? '—',           // N
            $v->cambio_placas      ?? '—',                  // O
            $v->poliza_hdi         ?? '—',                  // P
            $v->poliza_latino      ?? '—',                  // Q
            $v->poliza_qualitas    ?? '—',                  // R
            isset($v->kilometros)  ? (int) $v->kilometros  : '—', // S
        ];
    }

    /**
     * Fuerza formatos de columna.
     * K (Tarjeta SiVale) y L (NIP) como TEXTO para preservar dígitos.
     */
    public function columnFormats(): array
    {
        return [
            'K' => NumberFormat::FORMAT_TEXT, // Tarjeta SiVale
            'L' => NumberFormat::FORMAT_TEXT, // NIP
        ];
    }

    /**
     * Value Binder: asegura que K y L se escriban como TEXTO (no número).
     */
    public function bindValue(Cell $cell, $value)
    {
        $col = $cell->getColumn();

        if (in_array($col, ['K', 'L'], true) && $value !== null && $value !== '—' && $value !== '') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * Estilos y header visual (idéntico a OperadoresExport; ajustado a LAST_COL).
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $sheet   = $e->sheet->getDelegate();
                $lastCol = self::LAST_COL;

                // Fila 1: Futurama Tires
                $sheet->mergeCells("B1:{$lastCol}1");
                $sheet->setCellValue('B1', 'Futurama Tires');

                // Fila 2: Reporte de Vehículos
                $sheet->mergeCells("B2:{$lastCol}2");
                $sheet->setCellValue('B2', 'Reporte de Vehículos');

                // Fila 3: Fecha de generación
                $sheet->mergeCells("B3:{$lastCol}3");
                $sheet->setCellValue('B3', 'Fecha de generación: ' . now()->format('Y-m-d H:i'));

                // Bandas 1-3
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 32, 'color' => ['rgb' => 'F6F7EB'], 'name' => 'Arial', 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E94F37']],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(42);

                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'name' => 'Arial', 'color' => ['rgb' => '393E41']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F6F7EB']],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['size' => 10, 'name' => 'Arial', 'color' => ['rgb' => 'f8f8ff']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '393E41']],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // Borde grueso alrededor del encabezado
                $sheet->getStyle("A1:{$lastCol}3")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '3d0600']],
                    ],
                ]);

                // Encabezados de tabla (fila 5)
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFEFEF']],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '3d0600']],
                    ],
                ]);
                // Borde grueso envolviendo headers
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '3d0600']],
                    ],
                ]);

                // Autofiltro
                $sheet->setAutoFilter("A5:{$lastCol}5");

                // Bordes finos para la tabla de datos
                $dataLastRow = max(5, self::DATA_START_ROW + $this->rowNumber - 1);
                if ($dataLastRow >= self::DATA_START_ROW) {
                    $sheet->getStyle("A".self::DATA_START_ROW.":{$lastCol}{$dataLastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'DDDDDD']],
                        ],
                    ]);

                    // Alineaciones útiles (#, Año, Tarjeta, NIP, Kilómetros)
                    $sheet->getStyle("A".self::DATA_START_ROW.":A{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // #
                    $sheet->getStyle("F".self::DATA_START_ROW.":F{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Año
                    $sheet->getStyle("K".self::DATA_START_ROW.":K{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tarjeta SiVale (texto)
                    $sheet->getStyle("L".self::DATA_START_ROW.":L{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // NIP (texto)
                    $sheet->getStyle("S".self::DATA_START_ROW.":S{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kilómetros
                }

                // Ancho mínimo para la columna A (logo/numeración)
                $currentWidth = $sheet->getColumnDimension('A')->getWidth();
                if ($currentWidth < 12) {
                    $sheet->getColumnDimension('A')->setWidth(12);
                }
            },
        ];
    }
}
