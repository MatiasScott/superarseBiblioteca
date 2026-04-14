<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Models/LibrosModel.php';
require_once __DIR__ . '/../Models/TesisModel.php';
require_once __DIR__ . '/../Models/PublicacionModel.php';
require_once __DIR__ . '/../Models/ReporteModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ReporteController
{
    private $librosModel;
    private $tesisModel;
    private $publicacionesModel;
    private $reporteModel;

    public function __construct()
    {
        AuthHelper::startSession();
        AuthHelper::requireAdminPage(defined('BASE_URL') ? BASE_URL : '');

        $this->librosModel = new LibrosModel();
        $this->tesisModel = new TesisModel();
        $this->publicacionesModel = new PublicacionModel();
        $this->reporteModel = new ReporteModel();
    }

    public function filtros(): void
    {
        header('Content-Type: application/json');

        echo json_encode([
            'ok' => true,
            'tesisCarreras' => $this->reporteModel->getCategoriasPorTipo(2),
            'librosCategorias' => $this->reporteModel->getCategoriasPorTipo(1),
            'aniosTesis' => $this->reporteModel->getAniosTesisDisponibles(),
        ]);
        exit;
    }

    public function exportar(): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);
        if (ob_get_length()) {
            ob_end_clean();
        }

        $key = trim((string) ($_GET['key'] ?? ''));
        $format = strtolower(trim((string) ($_GET['format'] ?? 'xlsx')));

        if ($key === '') {
            $this->renderTextError('Parámetro key obligatorio');
        }

        if (!in_array($format, ['xlsx', 'pdf'], true)) {
            $this->renderTextError('Formato inválido. Use xlsx o pdf');
        }

        $report = $this->resolveReport($key, $_GET);
        if (!$report) {
            $this->renderTextError('Tipo de reporte no válido');
        }

        if ($format === 'pdf') {
            $this->downloadPdf($report['title'], $report['headers'], $report['rows'], $report['filename']);
            return;
        }

        $this->downloadExcel($report['title'], $report['headers'], $report['rows'], $report['filename']);
    }

    private function resolveReport(string $key, array $filters): ?array
    {
        switch ($key) {
            case 'tesis_todas':
                return [
                    'title' => 'Reporte de Tesis',
                    'filename' => 'reporte_tesis_todas',
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisTodas(), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas']),
                ];

            case 'tesis_por_carrera':
                $categoriaId = (int) ($filters['categoria_id'] ?? 0);
                if ($categoriaId <= 0) {
                    $this->renderTextError('Debe seleccionar la carrera para este reporte');
                }
                return [
                    'title' => 'Reporte de Tesis por Carrera',
                    'filename' => 'reporte_tesis_por_carrera',
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisPorCarrera($categoriaId), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas']),
                ];

            case 'tesis_por_anio':
                $anio = (int) ($filters['anio'] ?? 0);
                if ($anio <= 0) {
                    $this->renderTextError('Debe seleccionar el año para este reporte');
                }
                return [
                    'title' => 'Reporte de Tesis por Año',
                    'filename' => 'reporte_tesis_por_anio',
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisPorAnio($anio), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas']),
                ];

            case 'tesis_mas_vistas':
                return [
                    'title' => 'Reporte de Tesis Más Vistas (Menor a Mayor)',
                    'filename' => 'reporte_tesis_mas_vistas',
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisMasVistasAsc(), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas']),
                ];

            case 'libros_todos':
                return [
                    'title' => 'Reporte de Todos los Libros',
                    'filename' => 'reporte_libros_todos',
                    'headers' => ['Código', 'Título', 'Autor', 'Categoría', 'Edición', 'Editorial', 'Año', 'Stock', 'Ubicación', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getLibrosTodos(), ['codigo', 'titulo', 'autor', 'categoria', 'edicion', 'editorial', 'anio', 'stock', 'ubicacion', 'visitas']),
                ];

            case 'libros_por_categoria':
                $categoriaId = (int) ($filters['categoria_id'] ?? 0);
                if ($categoriaId <= 0) {
                    $this->renderTextError('Debe seleccionar la categoría para este reporte');
                }
                return [
                    'title' => 'Reporte de Libros por Categoría',
                    'filename' => 'reporte_libros_por_categoria',
                    'headers' => ['Código', 'Título', 'Autor', 'Categoría', 'Edición', 'Editorial', 'Año', 'Stock', 'Ubicación', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getLibrosPorCategoria($categoriaId), ['codigo', 'titulo', 'autor', 'categoria', 'edicion', 'editorial', 'anio', 'stock', 'ubicacion', 'visitas']),
                ];

            case 'libros_vigentes_5_anios':
                return [
                    'title' => 'Reporte de Libros Vigentes (Últimos 5 Años)',
                    'filename' => 'reporte_libros_vigentes_5_anios',
                    'headers' => ['Código', 'Título', 'Autor', 'Categoría', 'Edición', 'Editorial', 'Año', 'Stock', 'Ubicación', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getLibrosVigentes5Anios(), ['codigo', 'titulo', 'autor', 'categoria', 'edicion', 'editorial', 'anio', 'stock', 'ubicacion', 'visitas']),
                ];

            case 'prestamos_totales':
                return [
                    'title' => 'Reporte de Préstamos Totales',
                    'filename' => 'reporte_prestamos_totales',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosTotales(), ['id', 'estudiante', 'item_titulo', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_activos':
                return [
                    'title' => 'Reporte de Préstamos Activos',
                    'filename' => 'reporte_prestamos_activos',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosActivos(), ['id', 'estudiante', 'item_titulo', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_pendientes':
                return [
                    'title' => 'Reporte de Préstamos Pendientes',
                    'filename' => 'reporte_prestamos_pendientes',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosPendientes(), ['id', 'estudiante', 'item_titulo', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_rechazados':
                return [
                    'title' => 'Reporte de Préstamos Rechazados',
                    'filename' => 'reporte_prestamos_rechazados',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosRechazados(), ['id', 'estudiante', 'item_titulo', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_devueltos':
                return [
                    'title' => 'Reporte de Libros Devueltos',
                    'filename' => 'reporte_prestamos_devueltos',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosDevueltos(), ['id', 'estudiante', 'item_titulo', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'publicaciones_todas':
                return [
                    'title' => 'Reporte de Publicaciones',
                    'filename' => 'reporte_publicaciones',
                    'headers' => ['Código', 'Título', 'Autor', 'Revista', 'Categoría', 'Año', 'Visitas'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPublicacionesTodas(), ['codigo', 'titulo', 'autor', 'revista', 'categoria', 'anio', 'visitas']),
                ];
        }

        return null;
    }

    private function rowsFromAssoc(array $records, array $keys): array
    {
        $rows = [];

        foreach ($records as $record) {
            $row = [];
            foreach ($keys as $key) {
                $value = $record[$key] ?? '';
                $row[] = is_scalar($value) || $value === null ? (string) ($value ?? '') : json_encode($value);
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function tesisRowsWithAutorTutor(array $records): array
    {
        foreach ($records as &$record) {
            $autor = trim((string) ($record['autor'] ?? ''));
            $tutor = trim((string) ($record['tutor'] ?? ''));

            if ($autor !== '' && $tutor !== '') {
                $record['autor_tutor'] = 'Autor: ' . $autor . "\n" . 'Tutor: ' . $tutor;
            } elseif ($autor !== '') {
                $record['autor_tutor'] = 'Autor: ' . $autor;
            } elseif ($tutor !== '') {
                $record['autor_tutor'] = 'Tutor: ' . $tutor;
            } else {
                $record['autor_tutor'] = '';
            }
        }
        unset($record);

            return $this->rowsFromAssoc($records, ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas']);
    }

    private function downloadExcel(string $title, array $headers, array $rows, string $fileBaseName): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', strtoupper($title));
        $sheet->mergeCells('A1:' . $this->excelColumn(count($headers)) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Fecha de descarga: ' . date('Y-m-d H:i:s'));
        $sheet->mergeCells('A2:' . $this->excelColumn(count($headers)) . '2');

        $rowNumber = 4;
        foreach ($headers as $index => $header) {
            $col = $this->excelColumn($index + 1);
            $sheet->setCellValue($col . $rowNumber, $header);
        }
        $sheet->getStyle('A4:' . $this->excelColumn(count($headers)) . '4')->getFont()->setBold(true);

        $rowNumber = 5;
        foreach ($rows as $row) {
            foreach ($row as $index => $value) {
                $col = $this->excelColumn($index + 1);
                $sheet->setCellValueExplicit($col . $rowNumber, $value, DataType::TYPE_STRING);
            }
            $rowNumber++;
        }

        foreach (range(1, count($headers)) as $index) {
            $sheet->getColumnDimension($this->excelColumn($index))->setAutoSize(true);
        }

        $filename = $fileBaseName . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function downloadPdf(string $title, array $headers, array $rows, string $fileBaseName): void
    {
        $pdfBinary = $this->buildStyledTablePdf($title, $headers, $rows);
        $filename = $fileBaseName . '_' . date('Ymd_His') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . strlen($pdfBinary));

        echo $pdfBinary;
        exit;
    }

    private function buildStyledTablePdf(string $title, array $headers, array $rows): string
    {
        $pageWidth = 842;   // A4 horizontal
        $pageHeight = 595;
        $left = 20;
        $right = 20;
        $bottom = 18;
        $usableWidth = $pageWidth - $left - $right;
        $tableTopY = 525.0;

        if (count($headers) === 0) {
            $headers = ['Dato'];
        }

        $displayRows = $rows;
        if (count($displayRows) === 0) {
            $displayRows = [['Sin registros para este reporte']];
            if (count($headers) === 1) {
                $headers = ['Detalle'];
            }
        }

        $weights = [];
        foreach ($headers as $i => $header) {
            $maxLen = mb_strlen((string) $header);
            foreach ($displayRows as $row) {
                $cell = (string) ($row[$i] ?? '');
                $maxLen = max($maxLen, mb_strlen($cell));
            }
            $weights[$i] = min(40, max(8, $maxLen));
        }

        $weightSum = array_sum($weights);
        if ($weightSum <= 0) {
            $weightSum = count($headers);
        }

        $colWidths = [];
        foreach ($weights as $i => $w) {
            $colWidths[$i] = ($w / $weightSum) * $usableWidth;
        }
        $this->boostLongTextColumns($headers, $colWidths, $usableWidth);
        $this->enforceMinColumnWidths($headers, $colWidths, $usableWidth);

        $totalRows = count($displayRows);
        $rowHeightBase = ($totalRows > 0)
            ? max(4.0, min(11.0, (($tableTopY - $bottom) - 12.0) / ($totalRows + 1)))
            : 10.0;
        $fontSizeBase = max(3.2, min(8.0, $rowHeightBase - 1.2));
        $headerHeight = max(8.0, $rowHeightBase + 1.0);
        $rowLineHeight = max(3.2, $fontSizeBase + 0.35);

        $pages = [];
        $pageNumber = 1;
        [$content, $y] = $this->startPdfPage($title, $left, $usableWidth, $tableTopY, $pageNumber);
        $content .= $this->drawPdfTableHeader($headers, $colWidths, $left, $y, $headerHeight);
        $y -= $headerHeight;

        foreach ($displayRows as $rowIndex => $row) {
            $wrappedByCol = [];
            $maxLinesInRow = 1;
            for ($i = 0; $i < count($headers); $i++) {
                $cellValue = (string) ($row[$i] ?? '');
                $wrapped = $this->wrapCellText($cellValue, $colWidths[$i], $fontSizeBase);
                $wrappedByCol[$i] = $wrapped;
                $maxLinesInRow = max($maxLinesInRow, count($wrapped));
            }

            $rowHeight = max($rowHeightBase, ($maxLinesInRow * $rowLineHeight) + 2.0);

            if (($y - $rowHeight) < $bottom) {
                $pages[] = $content;
                $pageNumber++;

                [$content, $y] = $this->startPdfPage($title, $left, $usableWidth, $tableTopY, $pageNumber);
                $content .= $this->drawPdfTableHeader($headers, $colWidths, $left, $y, $headerHeight);
                $y -= $headerHeight;
            }

            $x = $left;
            $isOdd = ($rowIndex % 2) === 0;
            $bg = $isOdd ? '1 1 1' : '0.975 0.985 0.995';

            for ($i = 0; $i < count($headers); $i++) {
                $w = $colWidths[$i];
                $cellLines = $wrappedByCol[$i];

                $content .= $bg . " rg\n";
                $content .= sprintf("%0.2f %0.2f %0.2f %0.2f re f\n", (float) $x, (float) ($y - $rowHeight), (float) $w, (float) $rowHeight);
                $content .= "0.82 0.86 0.92 RG\n0.3 w\n";
                $content .= sprintf("%0.2f %0.2f %0.2f %0.2f re S\n", (float) $x, (float) ($y - $rowHeight), (float) $w, (float) $rowHeight);

                foreach ($cellLines as $lineIndex => $line) {
                    $lineY = $y - (($lineIndex + 1) * $rowLineHeight);
                    if ($lineY < $bottom) {
                        break;
                    }

                    $content .= "BT\n/F1 " . number_format($fontSizeBase, 2, '.', '') . " Tf\n0.08 0.08 0.08 rg\n";
                    $content .= sprintf("1 0 0 1 %0.2f %0.2f Tm (%s) Tj\n", (float) ($x + 2), (float) $lineY, $this->escapePdfText($this->toPdfText($line)));
                    $content .= "ET\n";
                }

                $x += $w;
            }

            $y -= $rowHeight;
        }

        $pages[] = $content;

        return $this->buildPdfFromPageContents($pages, $pageWidth, $pageHeight);
    }

    private function startPdfPage(string $title, float $left, float $usableWidth, float $tableTopY, int $pageNumber): array
    {
        $content = "";

        $titleText = strtoupper($title);
        if ($pageNumber > 1) {
            $titleText .= ' (PAG. ' . $pageNumber . ')';
        }

        $content .= "0.11 0.28 0.52 rg\n";
        $content .= sprintf("%0.2f %0.2f %0.2f %0.2f re f\n", (float) $left, 548.0, (float) $usableWidth, 24.0);

        $content .= "BT\n/F1 12 Tf\n1 1 1 rg\n";
        $content .= sprintf("1 0 0 1 %0.2f %0.2f Tm (%s) Tj\n", (float) ($left + 8), 556.0, $this->escapePdfText($this->toPdfText($titleText)));
        $content .= "ET\n";

        $content .= "BT\n/F1 8 Tf\n0.2 0.2 0.2 rg\n";
        $content .= sprintf("1 0 0 1 %0.2f %0.2f Tm (%s) Tj\n", (float) $left, 538.0, $this->escapePdfText($this->toPdfText('Fecha de descarga: ' . date('Y-m-d H:i:s'))));
        $content .= "ET\n";

        return [$content, $tableTopY];
    }

    private function drawPdfTableHeader(array $headers, array $colWidths, float $left, float $y, float $headerHeight): string
    {
        $content = '';
        $x = $left;

        for ($i = 0; $i < count($headers); $i++) {
            $w = $colWidths[$i];
            $content .= "0.86 0.9 0.96 rg\n";
            $content .= sprintf("%0.2f %0.2f %0.2f %0.2f re f\n", (float) $x, (float) ($y - $headerHeight), (float) $w, (float) $headerHeight);
            $content .= "0.62 0.72 0.86 RG\n0.5 w\n";
            $content .= sprintf("%0.2f %0.2f %0.2f %0.2f re S\n", (float) $x, (float) ($y - $headerHeight), (float) $w, (float) $headerHeight);

            $headerText = $this->ellipsis((string) $headers[$i], 28);
            $content .= "BT\n/F1 7 Tf\n0.07 0.2 0.35 rg\n";
            $content .= sprintf("1 0 0 1 %0.2f %0.2f Tm (%s) Tj\n", (float) ($x + 2), (float) ($y - $headerHeight + 2.3), $this->escapePdfText($this->toPdfText($headerText)));
            $content .= "ET\n";

            $x += $w;
        }

        return $content;
    }

    private function buildPdfFromPageContents(array $pageContents, float $pageWidth, float $pageHeight): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $objects = [];

        $fontObjNum = 3;
        $firstPageObjNum = 4;

        $kids = [];
        foreach ($pageContents as $index => $content) {
            $pageObjNum = $firstPageObjNum + ($index * 2);
            $contentObjNum = $pageObjNum + 1;
            $kids[] = $pageObjNum . ' 0 R';

            $objects[$pageObjNum] = $pageObjNum . " 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 "
                . (int) $pageWidth . " " . (int) $pageHeight . "] /Resources << /Font << /F1 " . $fontObjNum
                . " 0 R >> >> /Contents " . $contentObjNum . " 0 R >>\nendobj\n";

            $objects[$contentObjNum] = $contentObjNum . " 0 obj\n<< /Length " . strlen($content)
                . " >>\nstream\n" . $content . "endstream\nendobj\n";
        }

        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . '] /Count ' . count($pageContents) . " >>\nendobj\n";
        $objects[$fontObjNum] = $fontObjNum . " 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        ksort($objects);
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj;
        }

        $xrefPos = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 " . $size . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . $size . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

        return $pdf;
    }

    private function wrapCellText(string $text, float $colWidth, float $fontSize): array
    {
        if (trim($text) === '') {
            return [''];
        }

        // Regla solicitada: cada 20 caracteres, respetando el ancho real de la columna.
        $avgCharWidth = max(0.45, $fontSize * 0.52);
        $maxByWidth = (int) floor(max(1.0, ($colWidth - 4.0) / $avgCharWidth));
        $maxChars = max(1, min(20, $maxByWidth));

        $paragraphs = preg_split('/\R/u', (string) $text) ?: [''];
        $allLines = [];

        foreach ($paragraphs as $paragraph) {
            $clean = trim(preg_replace('/\s+/', ' ', $paragraph));
            if ($clean === '') {
                $allLines[] = '';
                continue;
            }

            $words = preg_split('/\s+/', $clean) ?: [];
            $current = '';

            foreach ($words as $word) {
                if (mb_strlen($word) > $maxChars) {
                    if ($current !== '') {
                        $allLines[] = $current;
                        $current = '';
                    }

                    $chunks = $this->splitLongWord($word, $maxChars);
                    foreach ($chunks as $chunk) {
                        $allLines[] = $chunk;
                    }
                    continue;
                }

                $candidate = $current === '' ? $word : ($current . ' ' . $word);
                if (mb_strlen($candidate) <= $maxChars) {
                    $current = $candidate;
                } else {
                    $allLines[] = $current;
                    $current = $word;
                }
            }

            if ($current !== '') {
                $allLines[] = $current;
            }
        }

        return count($allLines) > 0 ? $allLines : [''];
    }

    private function splitLongWord(string $word, int $maxChars): array
    {
        if ($maxChars <= 1) {
            return ['…'];
        }

        $chunks = [];
        $len = mb_strlen($word);
        for ($i = 0; $i < $len; $i += $maxChars) {
            $chunks[] = mb_substr($word, $i, $maxChars);
        }

        return $chunks;
    }

    private function boostLongTextColumns(array $headers, array &$colWidths, float $usableWidth): void
    {
        $temp = [];
        foreach ($headers as $i => $header) {
            $key = $this->normalizeHeader((string) $header);
            $factor = 1.0;
            if ($key === 'titulo') {
                $factor = 2.6;
            } elseif ($key === 'codigo') {
                $factor = 1.35;
            } elseif (strpos($key, 'autor') !== false && strpos($key, 'tutor') !== false) {
                $factor = 2.6;
            } elseif ($key === 'autor' || $key === 'tutor') {
                $factor = 2.0;
            } elseif ($key === 'universidad') {
                $factor = 1.7;
            } elseif ($key === 'carrera') {
                $factor = 1.35;
            } elseif ($key === 'visitas') {
                $factor = 0.95;
            } elseif ($key === 'ano' || $key === 'anio') {
                $factor = 0.95;
            } elseif ($key === 'estado') {
                $factor = 0.75;
            }

            $temp[$i] = ($colWidths[$i] ?? 0) * $factor;
        }

        $sum = array_sum($temp);
        if ($sum <= 0) {
            return;
        }

        foreach ($temp as $i => $w) {
            $colWidths[$i] = ($w / $sum) * $usableWidth;
        }
    }

    private function enforceMinColumnWidths(array $headers, array &$colWidths, float $usableWidth): void
    {
        $minByIndex = [];
        foreach ($headers as $i => $header) {
            $key = $this->normalizeHeader((string) $header);
            if ($key === 'codigo') {
                $minByIndex[$i] = 70.0;
            } elseif ($key === 'universidad') {
                $minByIndex[$i] = 110.0;
            } elseif ($key === 'carrera') {
                $minByIndex[$i] = 56.0;
            } elseif ($key === 'tutor') {
                $minByIndex[$i] = 80.0;
            } elseif ($key === 'ano' || $key === 'anio') {
                $minByIndex[$i] = 30.0;
            } elseif ($key === 'visitas') {
                $minByIndex[$i] = 34.0;
            }
        }

        if (empty($minByIndex)) {
            return;
        }

        $extraNeeded = 0.0;
        foreach ($minByIndex as $i => $minWidth) {
            $current = $colWidths[$i] ?? 0.0;
            if ($current < $minWidth) {
                $extraNeeded += ($minWidth - $current);
                $colWidths[$i] = $minWidth;
            }
        }

        if ($extraNeeded <= 0.0) {
            return;
        }

        $donorIndexes = [];
        $donorTotal = 0.0;
        foreach ($colWidths as $i => $w) {
            if (isset($minByIndex[$i])) {
                continue;
            }
            $donorIndexes[] = $i;
            $donorTotal += $w;
        }

        if ($donorTotal <= 0.0) {
            return;
        }

        foreach ($donorIndexes as $i) {
            $portion = ($colWidths[$i] / $donorTotal) * $extraNeeded;
            $colWidths[$i] = max(18.0, $colWidths[$i] - $portion);
        }

        $sum = array_sum($colWidths);
        if ($sum <= 0.0) {
            return;
        }

        foreach ($colWidths as $i => $w) {
            $colWidths[$i] = ($w / $sum) * $usableWidth;
        }
    }

    private function normalizeHeader(string $header): string
    {
        $h = mb_strtolower(trim($header));
        $map = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n',
        ];

        return strtr($h, $map);
    }

    private function ellipsis(string $text, int $maxChars): string
    {
        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        if ($maxChars <= 1) {
            return '…';
        }

        return rtrim(mb_substr($text, 0, $maxChars - 1)) . '…';
    }

    private function toPdfText(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (!mb_check_encoding($text, 'UTF-8')) {
            $converted = @iconv('ISO-8859-1', 'Windows-1252//TRANSLIT//IGNORE', $text);
        } else {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        }

        if ($converted === false) {
            $converted = utf8_decode($text);
        }

        return $converted;
    }

    private function escapePdfText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        return $text;
    }

    private function excelColumn(int $index): string
    {
        $column = '';

        while ($index > 0) {
            $index--;
            $column = chr(65 + ($index % 26)) . $column;
            $index = intdiv($index, 26);
        }

        return $column;
    }

    private function renderTextError(string $message): void
    {
        http_response_code(400);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
        exit;
    }

    public function reporteLibrosMasVistos(): void
    {
        $this->exportMasVistosCompat($this->librosModel, 1, 'Libros Más Vistos', 'reporte_libros_mas_vistos');
    }

    public function reporteTesisMasVistas(): void
    {
        $this->exportMasVistosCompat($this->tesisModel, 2, 'Tesis Más Vistas', 'reporte_tesis_mas_vistas');
    }

    public function reportePublicacionesMasVistas(): void
    {
        $this->exportMasVistosCompat($this->publicacionesModel, 3, 'Publicaciones Más Vistas', 'reporte_publicaciones_mas_vistas');
    }

    public function exportarCatalogoLibros(): void
    {
        $report = $this->resolveReport('libros_todos', []);
        $this->downloadExcel($report['title'], $report['headers'], $report['rows'], $report['filename']);
    }

    private function exportMasVistosCompat($model, int $tipoId, string $title, string $filename): void
    {
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        if (!$fechaInicio || !$fechaFin) {
            $this->renderTextError('Rango de fechas obligatorio');
        }

        $records = $model->getMasVistosPorRango($tipoId, $fechaInicio, $fechaFin);
        $rows = $this->rowsFromAssoc($records, ['titulo', 'visitas']);

        $this->downloadExcel(
            $title,
            ['Título', 'Visitas'],
            $rows,
            $filename
        );
    }
}
