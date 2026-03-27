<?php
require_once __DIR__ . "/../Models/EstadisticasModel.php";
require_once __DIR__ . "/../../vendor/autoload.php";

class EstadisticasController {

    private $model;

    public function __construct()
    {
        $this->model = new EstadisticasModel();
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // Listado JSON para dashboard
    public function indexJson()
    {
        header("Content-Type: application/json");

        // Validar rol: 1 = admin
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
            echo json_encode(["success" => false, "message" => "No autorizado"]);
            exit;
        }

        $prestamosMes = [];
        $devolucionesMes = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $prestamosMes[] = $this->model->prestamosPorMes($mes)['total'];
            $devolucionesMes[] = $this->model->devolucionesPorMes($mes)['total'];
        }

        $response = [
            "success" => true,
            "prestamosMes" => $prestamosMes,
            "devolucionesMes" => $devolucionesMes,
            "itemsMasPrestados" => $this->model->itemsMasPrestados(),
            "usuariosActivos" => $this->model->usuariosActivos(),
            "totales" => [
                "libros" => $this->model->totalLibros(),
                "tesis" => $this->model->totalTesis(),
                "publicaciones" => $this->model->totalPublicaciones(),
                "usuarios" => $this->model->totalUsuarios(),
                "prestamosActivos" => $this->model->prestamosActivos(),
                "atrasados" => $this->model->prestamosAtrasados()
            ]
        ];

        echo json_encode($response);
        exit;
    }

    // Exportar Excel con todas las estadísticas
    public function exportExcel()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Validar rol: 1 = admin
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
            echo json_encode(["success" => false, "message" => "No autorizado"]);
            exit;
        }

        // Usar PhpSpreadsheet para crear Excel profesional
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Sistema Biblioteca Superarse");
        $spreadsheet->getProperties()->setTitle("Estadísticas Completas");
        $spreadsheet->getProperties()->setDescription("Reporte de estadísticas de la biblioteca");

        // HOJA 1: RESUMEN GENERAL
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle("Resumen General");
        $this->crearResumenGeneral($sheet1);

        // HOJA 2: PRÉSTAMOS MENSUALES
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle("Préstamos Mensuales");
        $this->crearPrestamosMonthly($sheet2);

        // HOJA 3: LIBROS MÁS PRESTADOS
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle("Top Libros");
        $this->crearTopLibros($sheet3);

        // HOJA 4: USUARIOS MÁS ACTIVOS
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle("Top Usuarios");
        $this->crearTopUsuarios($sheet4);

        // Configurar descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Estadisticas_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function crearResumenGeneral(&$sheet)
    {
        $t = [
            "libros" => $this->model->totalLibros(),
            "tesis" => $this->model->totalTesis(),
            "publicaciones" => $this->model->totalPublicaciones(),
            "usuarios" => $this->model->totalUsuarios(),
            "prestamosActivos" => $this->model->prestamosActivos(),
            "atrasados" => $this->model->prestamosAtrasados()
        ];

        // Título
        $sheet->setCellValue('A1', 'ESTADÍSTICAS GENERALES');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('A1:B1');

        // Fecha
        $sheet->setCellValue('A2', 'Fecha: ' . date('d/m/Y H:i:s'));
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        // Datos
        $row = 4;
        $data = [
            ['Total Libros', $t['libros']],
            ['Total Tesis', $t['tesis']],
            ['Total Publicaciones', $t['publicaciones']],
            ['Usuarios Activos', $t['usuarios']],
            ['Préstamos Activos', $t['prestamosActivos']],
            ['Préstamos Atrasados', $t['atrasados']]
        ];

        foreach ($data as $item) {
            $sheet->setCellValue("A$row", $item[0]);
            $sheet->setCellValue("B$row", $item[1]);
            $sheet->getStyle("A$row")->getFont()->setBold(true);
            $sheet->getStyle("B$row")->getAlignment()->setHorizontal('center');
            $sheet->getStyle("B$row")->getFont()->setBold(true)->setSize(12);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
    }

    private function crearPrestamosMonthly(&$sheet)
    {
        // Encabezado
        $sheet->setCellValue('A1', 'PRÉSTAMOS Y DEVOLUCIONES MENSUALES');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:C1');

        // Columnas
        $sheet->setCellValue('A3', 'Mes');
        $sheet->setCellValue('B3', 'Préstamos');
        $sheet->setCellValue('C3', 'Devoluciones');

        $headerStyle = $sheet->getStyle('A3:C3');
        $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1b4785');

        // Datos
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $row = 4;

        for ($mes = 1; $mes <= 12; $mes++) {
            $prestamos = $this->model->prestamosPorMes($mes)['total'];
            $devoluciones = $this->model->devolucionesPorMes($mes)['total'];

            $sheet->setCellValue("A$row", $meses[$mes - 1]);
            $sheet->setCellValue("B$row", $prestamos);
            $sheet->setCellValue("C$row", $devoluciones);

            // Formato numéricamente centrado
            $sheet->getStyle("B$row:C$row")->getAlignment()->setHorizontal('center');

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
    }

    private function crearTopLibros(&$sheet)
    {
        // Encabezado
        $sheet->setCellValue('A1', 'TOP 5 LIBROS MÁS PRESTADOS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:B1');

        // Columnas
        $sheet->setCellValue('A3', 'Título');
        $sheet->setCellValue('B3', 'Veces Prestado');

        $headerStyle = $sheet->getStyle('A3:B3');
        $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF164c7e');

        // Datos
        $libros = $this->model->itemsMasPrestados();
        $row = 4;

        foreach ($libros as $libro) {
            $sheet->setCellValue("A$row", $libro['titulo']);
            $sheet->setCellValue("B$row", $libro['total']);
            $sheet->getStyle("B$row")->getAlignment()->setHorizontal('center');
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(15);
    }

    private function crearTopUsuarios(&$sheet)
    {
        // Encabezado
        $sheet->setCellValue('A1', 'TOP 5 USUARIOS MÁS ACTIVOS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:B1');

        // Columnas
        $sheet->setCellValue('A3', 'Usuario');
        $sheet->setCellValue('B3', 'Préstamos Realizados');

        $headerStyle = $sheet->getStyle('A3:B3');
        $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF479990');

        // Datos
        $usuarios = $this->model->usuariosActivos();
        $row = 4;

        foreach ($usuarios as $usuario) {
            $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $sheet->setCellValue("A$row", $nombreCompleto);
            $sheet->setCellValue("B$row", $usuario['total']);
            $sheet->getStyle("B$row")->getAlignment()->setHorizontal('center');
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
    }
}
