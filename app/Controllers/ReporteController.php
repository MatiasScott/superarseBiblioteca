<?php
// Asegúrate que el autoloader de Composer esté cargado en tu archivo principal
require_once __DIR__ . '/../../vendor/autoload.php';

// Cargar los tres modelos (ajusta las rutas según tu estructura)
require_once __DIR__ . '/../Models/LibrosModel.php';
require_once __DIR__ . '/../Models/TesisModel.php';
require_once __DIR__ . '/../Models/PublicacionModel.php';
require_once __DIR__ . '/../Helpers/AuthHelper.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController
{
    private $librosModel;
    private $tesisModel;
    private $publicacionesModel;

    public function __construct()
    {
        AuthHelper::startSession();
        AuthHelper::requireAdminPage(defined('BASE_URL') ? BASE_URL : '');

        // Inicializar los tres modelos
        $this->librosModel = new LibrosModel();
        $this->tesisModel = new TesisModel();
        $this->publicacionesModel = new PublicacionModel();
    }

    /* ======================================================
        LIBROS MÁS VISTOS (TIPO ID = 1)
    ====================================================== */
    public function reporteLibrosMasVistos()
    {
        // Evitar basura en la salida
        ini_set('display_errors', 0);
        error_reporting(0);
        if (ob_get_length()) { ob_end_clean(); }

        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin    = $_GET['fechaFin'] ?? null;

        if (!$fechaInicio || !$fechaFin) {
            die("Rango de fechas obligatorio");
        }

        // Obtener datos usando el Modelo de Libros y TIPO ID 1
        $datos = $this->librosModel
            ->getMasVistosPorRango(1, $fechaInicio, $fechaFin);

        if (empty($datos)) {
            die("No hay datos de Libros para exportar");
        }

        // --- Generación y Descarga ---
        $this->generarYDescargarExcel(
            $datos,
            $fechaInicio,
            $fechaFin,
            'Libros Más Vistos',
            'Reporte_Libros_Mas_Vistos.xlsx'
        );
    }

    /* ======================================================
        TESIS MÁS VISTAS (TIPO ID = 2)
    ====================================================== */
    public function reporteTesisMasVistas()
    {
        ini_set('display_errors', 0);
        error_reporting(0);
        if (ob_get_length()) { ob_end_clean(); }

        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin    = $_GET['fechaFin'] ?? null;

        if (!$fechaInicio || !$fechaFin) {
            die("Rango de fechas obligatorio");
        }

        // Obtener datos usando el Modelo de Tesis y TIPO ID 2
        $datos = $this->tesisModel 
            ->getMasVistosPorRango(2, $fechaInicio, $fechaFin); // <-- ¡ID 2!

        if (empty($datos)) {
            die("No hay datos de Tesis para exportar");
        }
        
        // --- Generación y Descarga ---
        $this->generarYDescargarExcel(
            $datos,
            $fechaInicio,
            $fechaFin,
            'Tesis Más Vistas',
            'Reporte_Tesis_Mas_Vistas.xlsx'
        );
    }

    /* ======================================================
        PUBLICACIONES MÁS VISTAS (TIPO ID = 3)
    ====================================================== */
    public function reportePublicacionesMasVistas()
    {
        ini_set('display_errors', 0);
        error_reporting(0);
        if (ob_get_length()) { ob_end_clean(); }

        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin    = $_GET['fechaFin'] ?? null;

        if (!$fechaInicio || !$fechaFin) {
            die("Rango de fechas obligatorio");
        }

        // Obtener datos usando el Modelo de Publicaciones y TIPO ID 3
        $datos = $this->publicacionesModel
            ->getMasVistosPorRango(3, $fechaInicio, $fechaFin); // <-- ¡ID 3!

        if (empty($datos)) {
            die("No hay datos de Publicaciones para exportar");
        }

        // --- Generación y Descarga ---
        $this->generarYDescargarExcel(
            $datos,
            $fechaInicio,
            $fechaFin,
            'Publicaciones Más Vistas',
            'Reporte_Publicaciones_Mas_Vistas.xlsx'
        );
    }
    
    /* ======================================================
        MÉTODO PRIVADO DE GENERACIÓN Y DESCARGA (REUTILIZACIÓN)
    ====================================================== */
    private function generarYDescargarExcel($datos, $fechaInicio, $fechaFin, $tituloHoja, $nombreArchivo)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($tituloHoja);

        // Título principal
        $sheet->setCellValue('A1', 'REPORTE DE ' . strtoupper($tituloHoja));
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Subtítulo de fechas
        $sheet->setCellValue(
            'A2',
            "Desde: $fechaInicio  |  Hasta: $fechaFin"
        );
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        // Encabezados
        $sheet->setCellValue('A3', 'Título');
        $sheet->setCellValue('B3', 'Visitas');
        $sheet->getStyle('A3:B3')->getFont()->setBold(true);

        // Datos
        $row = 4;
        foreach ($datos as $item) {
            $sheet->setCellValue("A{$row}", $item['titulo']);
            $sheet->setCellValue("B{$row}", (int)$item['visitas']);
            $row++;
        }

        // Autoajustar columnas
        foreach (['A', 'B'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- DESCARGA ---
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    
    /* ======================================================
    EXPORTAR CATÁLOGO COMPLETO DE LIBROS (ADMIN)
====================================================== */
public function exportarCatalogoLibros()
{
    ini_set('display_errors', 0);
    error_reporting(0);
    if (ob_get_length()) { ob_end_clean(); }

    date_default_timezone_set('America/Guayaquil');

    $fechaHora = date('d/m/Y H:i:s');
    $fechaArchivo = date('Ymd_His');

    $datos = $this->librosModel->getCatalogoCompletoAdmin();

    if (empty($datos)) {
        die("No hay libros para exportar");
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Catálogo de Libros');

    /* =====================================
       TÍTULO
    ===================================== */
    $sheet->setCellValue('A1', 'CATÁLOGO GENERAL DE LIBROS');
    $sheet->mergeCells('A1:N1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    /* =====================================
       FECHA Y HORA
    ===================================== */
    $sheet->setCellValue('A2', 'Fecha y hora de descarga: ' . $fechaHora);
    $sheet->mergeCells('A2:N2');
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

    /* =====================================
       FILA DE SEPARACIÓN
    ===================================== */
    $sheet->mergeCells('A3:N3');

    /* =====================================
       ENCABEZADOS
    ===================================== */
    $headers = [
        'Código',
        'Portada (URL)',
        'Título',
        'Autor',
        'Descripción',
        'Edición',
        'Editorial',
        'Código de Barras',
        'Categoría',
        'Año',
        'Número de Ejemplares',
        'Stock',
        'Ubicación',
        'Estado'
    ];

    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . '4', $h);
        $sheet->getStyle($col . '4')->getFont()->setBold(true);
        $sheet->getStyle($col . '4')->getAlignment()->setHorizontal('center');
        $col++;
    }

    /* =====================================
       DATOS
    ===================================== */
    $row = 5;
    foreach ($datos as $l) {
        $sheet->setCellValue("A$row", $l['codigo']);
        $sheet->setCellValue("B$row", $l['portada']);
        $sheet->setCellValue("C$row", $l['titulo']);
        $sheet->setCellValue("D$row", $l['autor']);
        $sheet->setCellValue("E$row", $l['descripcion']);
        $sheet->setCellValue("F$row", $l['edicion']);
        $sheet->setCellValue("G$row", $l['revista']);
        $sheet->setCellValue("H$row", $l['codigo_barra']);
        $sheet->setCellValue("I$row", $l['categoria']);
        $sheet->setCellValue("J$row", $l['anio']);
        $sheet->setCellValue("K$row", $l['numero_ejemplares']);
        $sheet->setCellValue("L$row", $l['stock']);
        $sheet->setCellValue("M$row", $l['ubicacion']);
        $sheet->setCellValue("N$row", $l['estado']);
        $row++;
    }

    /* =====================================
       AUTOAJUSTE DE COLUMNAS
    ===================================== */
    foreach (range('A', 'N') as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }

    /* =====================================
       DESCARGA
    ===================================== */
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"Catalogo_Libros_$fechaArchivo.xlsx\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
}