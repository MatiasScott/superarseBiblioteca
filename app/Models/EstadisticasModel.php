<?php
require_once __DIR__ . "/Database.php";

class EstadisticasModel {

    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // Conteo total de libros, tesis, publicaciones
    public function totalLibros() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM items_biblioteca WHERE tipo_id = 1 AND deleted_at IS NULL");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function totalTesis() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM items_biblioteca WHERE tipo_id = 2 AND deleted_at IS NULL");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function totalPublicaciones() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM items_biblioteca WHERE tipo_id = 3 AND deleted_at IS NULL");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Usuarios activos
    public function totalUsuarios() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE estado='ACTIVO'");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Préstamos activos y atrasados
    public function prestamosActivos() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM solicitudes_prestamo WHERE estado IN ('APROBADA','ENTREGADO')");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function prestamosAtrasados() {
        $stmt = $this->conn->query("
            SELECT COUNT(*) AS total 
            FROM solicitudes_prestamo
            WHERE estado='APROBADA' AND fecha_devolucion < CURDATE()
        ");
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Préstamos por mes
    public function prestamosPorMes($mes)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total
            FROM solicitudes_prestamo
            WHERE MONTH(fecha_prestamo) = ? AND estado IN ('APROBADA','ENTREGADO')
        ");
        $stmt->execute([$mes]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Devoluciones por mes
    public function devolucionesPorMes($mes)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS total
            FROM solicitudes_prestamo
            WHERE MONTH(fecha_devolucion) = ? AND fecha_devolucion IS NOT NULL
        ");
        $stmt->execute([$mes]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Top 5 items más prestados
    public function itemsMasPrestados()
    {
        $stmt = $this->conn->query("
            SELECT i.titulo, COUNT(s.id) AS total
            FROM solicitudes_prestamo s
            INNER JOIN items_biblioteca i ON i.id = s.item_id
            WHERE s.estado IN ('APROBADA','ENTREGADO')
            GROUP BY s.item_id
            ORDER BY total DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Top 5 usuarios más activos
    public function usuariosActivos()
    {
        $stmt = $this->conn->query("
            SELECT u.nombre, u.apellido, COUNT(s.id) AS total
            FROM solicitudes_prestamo s
            INNER JOIN usuarios u ON u.id = s.usuario_id
            WHERE s.estado IN ('APROBADA','ENTREGADO')
            GROUP BY s.usuario_id
            ORDER BY total DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Exportar Excel
    public function exportExcel()
    {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=estadisticas_completas.xls");

        echo "Mes\tPrestamos\tDevoluciones\n";
        for ($mes = 1; $mes <= 12; $mes++) {
            $prestamos = $this->prestamosPorMes($mes)['total'];
            $devoluciones = $this->devolucionesPorMes($mes)['total'];
            echo "$mes\t$prestamos\t$devoluciones\n";
        }
        exit();
    }
}
