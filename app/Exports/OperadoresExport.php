<?php

namespace App\Exports;

use App\Models\Operador;
use Illuminate\Http\Request;
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

class OperadoresExport implements
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

    /** Última columna usada (A..H) para merges/estilos */
    private const LAST_COL = 'H';

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
        $sortBy  = $this->params['sort_by']  ?? 'nombre_completo';
        $sortDir = $this->params['sort_dir'] ?? 'asc';
        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';

        $q = Operador::query()
            ->with(['user'])
            ->filter($this->params);

        if ($sortBy === 'nombre_completo') {
            $q->orderByRaw("CONCAT_WS(' ', operadores.nombre, operadores.apellido_paterno, operadores.apellido_materno) {$sortDir}");
        } elseif ($sortBy === 'email') {
            $q->leftJoin('users', 'operadores.user_id', '=', 'users.id')
              ->orderBy('users.email', $sortDir)
              ->select('operadores.*');
        } else {
            $q->orderBy($sortBy, $sortDir);
        }

        return $q;
    }

    /**
     * Título de la hoja.
     */
    public function title(): string
    {
        return 'Operadores';
    }

    /**
     * Encabezados de columnas (fila 5 en este diseño).
     */
    public function headings(): array
    {
        return [
            '#',
            'Nombre completo',
            'Correo electrónico',
            'Teléfono',
            'Tipo sangre',
            'Contacto emergencia',
            'Tel. emergencia',
            'Creado el',
        ];
    }

    /**
     * La celda donde inician los encabezados de columnas.
     */
    public function startCell(): string
    {
        // Encabezados de columnas van en la fila 5, datos empiezan en la 6
        return 'A5';
    }

    /**
     * Logo en A1.
     */
    public function drawings()
    {
        $logoPath = public_path('images/logoOriginal2.png'); // \transportes\public\images\logoOriginal.png
        if (!file_exists($logoPath)) {
            return null;
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Futurama Tires');
        $drawing->setPath($logoPath);
        $drawing->setHeight(48);       // ajusta si deseas más grande
        $drawing->setCoordinates('A1'); // posición
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(2);

        return $drawing;
    }

    /**
     * Mapeo de cada fila (modelo Operador -> fila XLSX).
     */
    public function map($op): array
    {
        $this->rowNumber++;

        $nombre = $op->nombre_completo
            ?? trim(($op->nombre ?? '') . ' ' . ($op->apellido_paterno ?? '') . ' ' . ($op->apellido_materno ?? ''));

        return [
            $this->rowNumber,
            $nombre ?: 'Operador',
            optional($op->user)->email ?: '—',
            $op->telefono ?: '—',
            $op->tipo_sangre ?: '—',
            $op->contacto_emergencia_nombre ?: '—',
            $op->contacto_emergencia_tel ?: '—',
            optional($op->created_at)?->format('Y-m-d H:i') ?: '—',
        ];
    }

    /**
     * Estilos y header visual (logo, título, subtítulo, fecha, bordes).
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $sheet   = $e->sheet->getDelegate();
                $lastCol = self::LAST_COL;

                // Reservamos la columna A para el logo; colocamos textos de encabezado a partir de B
                // Fila 1: Futurama Tires
                $sheet->mergeCells("B1:{$lastCol}1");
                $sheet->setCellValue('B1', 'Futurama Tires');

                // Fila 2: Reporte de Operadores
                $sheet->mergeCells("B2:{$lastCol}2");
                $sheet->setCellValue('B2', 'Reporte de Operadores');

                // Fila 3: Fecha de generación
                $sheet->mergeCells("B3:{$lastCol}3");
                $sheet->setCellValue('B3', 'Fecha de generación: ' . now()->format('Y-m-d H:i'));

                // Fondo claro para bandas superiores (A1:H3) y bordes gruesos alrededor del bloque
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => [
                        'bold' => true,
                        'size' => 32,
                        'color' => ['rgb' => 'F6F7EB'], // Color blanco
                        'name' => 'Arial', // Fuente Arial
                        'italic' => true
                    ],
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
                    'font'      => ['size' => 10,'name' => 'Arial', 'color' => ['rgb' => 'f8f8ff']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '393E41']],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // Borde grueso que envuelve el encabezado (filas 1 a 3)
                $sheet->getStyle("A1:{$lastCol}3")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color'       => ['rgb' => '3d0600'],
                        ],
                    ],
                ]);

                // Encabezados de tabla (fila 5)
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EFEFEF']],
                    'borders'   => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '3d0600'],
                        ],
                    ],
                ]);
                // Borde grueso envolviendo la fila de headers de la tabla
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color'       => ['rgb' => '3d0600'],
                        ],
                    ],
                ]);

                // Auto–filter en la fila de encabezados
                $sheet->setAutoFilter("A5:{$lastCol}5");

                // Bordes finos para toda la tabla de datos
                $dataLastRow = max(5, self::DATA_START_ROW + $this->rowNumber - 1);
                if ($dataLastRow >= self::DATA_START_ROW) {
                    $sheet->getStyle("A".self::DATA_START_ROW.":{$lastCol}{$dataLastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_HAIR,
                                'color'       => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                    ]);
                    // Alinear números de fila y fechas
                    $sheet->getStyle("A".self::DATA_START_ROW.":A{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H".self::DATA_START_ROW.":H{$dataLastRow}")
                          ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Ajuste: un pequeño ancho para la columna A (logo) si fuese necesario
                // (AutoSize está activo, pero aseguramos mínimo)
                $currentWidth = $sheet->getColumnDimension('A')->getWidth();
                if ($currentWidth < 12) {
                    $sheet->getColumnDimension('A')->setWidth(12);
                }
            },
        ];
    }
}
