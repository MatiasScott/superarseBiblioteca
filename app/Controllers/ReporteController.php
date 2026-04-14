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
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisTodas(), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas', 'estado']),
                ];

            case 'tesis_por_carrera':
                $categoriaId = (int) ($filters['categoria_id'] ?? 0);
                if ($categoriaId <= 0) {
                    $this->renderTextError('Debe seleccionar la carrera para este reporte');
                }
                return [
                    'title' => 'Reporte de Tesis por Carrera',
                    'filename' => 'reporte_tesis_por_carrera',
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisPorCarrera($categoriaId), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas', 'estado']),
                ];

            case 'tesis_por_anio':
                $anio = (int) ($filters['anio'] ?? 0);
                if ($anio <= 0) {
                    $this->renderTextError('Debe seleccionar el año para este reporte');
                }
                return [
                    'title' => 'Reporte de Tesis por Año',
                    'filename' => 'reporte_tesis_por_anio',
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisPorAnio($anio), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas', 'estado']),
                ];

            case 'tesis_mas_vistas':
                return [
                    'title' => 'Reporte de Tesis Más Vistas (Menor a Mayor)',
                    'filename' => 'reporte_tesis_mas_vistas',
                    'headers' => ['Código', 'Título', 'Autor', 'Tutor', 'Universidad', 'Carrera', 'Año', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getTesisMasVistasAsc(), ['codigo', 'titulo', 'autor', 'tutor', 'universidad', 'carrera', 'anio', 'visitas', 'estado']),
                ];

            case 'libros_todos':
                return [
                    'title' => 'Reporte de Todos los Libros',
                    'filename' => 'reporte_libros_todos',
                    'headers' => ['Código', 'Título', 'Autor', 'Categoría', 'Edición', 'Editorial', 'Año', 'Stock', 'Ubicación', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getLibrosTodos(), ['codigo', 'titulo', 'autor', 'categoria', 'edicion', 'editorial', 'anio', 'stock', 'ubicacion', 'visitas', 'estado']),
                ];

            case 'libros_por_categoria':
                $categoriaId = (int) ($filters['categoria_id'] ?? 0);
                if ($categoriaId <= 0) {
                    $this->renderTextError('Debe seleccionar la categoría para este reporte');
                }
                return [
                    'title' => 'Reporte de Libros por Categoría',
                    'filename' => 'reporte_libros_por_categoria',
                    'headers' => ['Código', 'Título', 'Autor', 'Categoría', 'Edición', 'Editorial', 'Año', 'Stock', 'Ubicación', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getLibrosPorCategoria($categoriaId), ['codigo', 'titulo', 'autor', 'categoria', 'edicion', 'editorial', 'anio', 'stock', 'ubicacion', 'visitas', 'estado']),
                ];

            case 'libros_vigentes_5_anios':
                return [
                    'title' => 'Reporte de Libros Vigentes (Últimos 5 Años)',
                    'filename' => 'reporte_libros_vigentes_5_anios',
                    'headers' => ['Código', 'Título', 'Autor', 'Categoría', 'Edición', 'Editorial', 'Año', 'Stock', 'Ubicación', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getLibrosVigentes5Anios(), ['codigo', 'titulo', 'autor', 'categoria', 'edicion', 'editorial', 'anio', 'stock', 'ubicacion', 'visitas', 'estado']),
                ];

            case 'prestamos_totales':
                return [
                    'title' => 'Reporte de Préstamos Totales',
                    'filename' => 'reporte_prestamos_totales',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Estado', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosTotales(), ['id', 'estudiante', 'item_titulo', 'estado', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_activos':
                return [
                    'title' => 'Reporte de Préstamos Activos',
                    'filename' => 'reporte_prestamos_activos',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Estado', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosActivos(), ['id', 'estudiante', 'item_titulo', 'estado', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_pendientes':
                return [
                    'title' => 'Reporte de Préstamos Pendientes',
                    'filename' => 'reporte_prestamos_pendientes',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Estado', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosPendientes(), ['id', 'estudiante', 'item_titulo', 'estado', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_rechazados':
                return [
                    'title' => 'Reporte de Préstamos Rechazados',
                    'filename' => 'reporte_prestamos_rechazados',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Estado', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosRechazados(), ['id', 'estudiante', 'item_titulo', 'estado', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'prestamos_devueltos':
                return [
                    'title' => 'Reporte de Libros Devueltos',
                    'filename' => 'reporte_prestamos_devueltos',
                    'headers' => ['ID', 'Estudiante', 'Libro', 'Estado', 'Fecha Solicitud', 'Fecha Préstamo', 'Fecha Devolución', 'Fecha Respuesta', 'Motivo Rechazo'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPrestamosDevueltos(), ['id', 'estudiante', 'item_titulo', 'estado', 'fecha_solicitud', 'fecha_prestamo', 'fecha_devolucion', 'fecha_respuesta', 'motivo_rechazo']),
                ];

            case 'publicaciones_todas':
                return [
                    'title' => 'Reporte de Publicaciones',
                    'filename' => 'reporte_publicaciones',
                    'headers' => ['Código', 'Título', 'Autor', 'Revista', 'Categoría', 'Año', 'Visitas', 'Estado'],
                    'rows' => $this->rowsFromAssoc($this->reporteModel->getPublicacionesTodas(), ['codigo', 'titulo', 'autor', 'revista', 'categoria', 'anio', 'visitas', 'estado']),
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
        $lines = [];
        $lines[] = strtoupper($title);
        $lines[] = 'Fecha de descarga: ' . date('Y-m-d H:i:s');
        $lines[] = str_repeat('-', 110);
        $lines[] = $this->truncateLine(implode(' | ', $headers));
        $lines[] = str_repeat('-', 110);

        foreach ($rows as $row) {
            $lines[] = $this->truncateLine(implode(' | ', array_map([$this, 'sanitizePdfText'], $row)));
        }

        if (count($rows) === 0) {
            $lines[] = 'Sin registros para este reporte.';
        }

        $pdfBinary = $this->buildSimplePdf($lines);
        $filename = $fileBaseName . '_' . date('Ymd_His') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . strlen($pdfBinary));

        echo $pdfBinary;
        exit;
    }

    private function buildSimplePdf(array $lines): string
    {
        $content = "BT\n/F1 9 Tf\n";
        $y = 820;

        foreach ($lines as $line) {
            if ($y < 40) {
                break;
            }

            $escaped = $this->escapePdfText($line);
            $content .= sprintf("1 0 0 1 36 %d Tm (%s) Tj\n", $y, $escaped);
            $y -= 12;
        }

        $content .= "ET\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream\nendobj\n";

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

        return $pdf;
    }

    private function truncateLine(string $line): string
    {
        if (mb_strlen($line) <= 150) {
            return $line;
        }

        return mb_substr($line, 0, 147) . '...';
    }

    private function sanitizePdfText(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return $text;
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
