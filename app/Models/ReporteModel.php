<?php
require_once 'Database.php';

class ReporteModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    public function getCategoriasPorTipo(int $tipoId): array
    {
        $sql = "SELECT id, nombre
                FROM categorias
                WHERE tipo_id = ? AND estado = 'ACTIVO'
                ORDER BY nombre ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tipoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAniosTesisDisponibles(): array
    {
        $sql = "SELECT DISTINCT anio
                FROM items_biblioteca
                WHERE tipo_id = 2
                  AND deleted_at IS NULL
                  AND anio IS NOT NULL
                  AND anio <> ''
                ORDER BY anio ASC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getTesisTodas(): array
    {
        $sql = "SELECT i.codigo, i.titulo, i.autor, i.tutor, i.universidad,
                       c.nombre AS carrera, i.anio, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 2
                  AND i.deleted_at IS NULL
                ORDER BY i.titulo ASC";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTesisPorCarrera(int $categoriaId): array
    {
        $sql = "SELECT i.codigo, i.titulo, i.autor, i.tutor, i.universidad,
                       c.nombre AS carrera, i.anio, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 2
                  AND i.deleted_at IS NULL
                  AND i.categoria_id = ?
                ORDER BY i.titulo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTesisPorAnio(int $anio): array
    {
        $sql = "SELECT i.codigo, i.titulo, i.autor, i.tutor, i.universidad,
                       c.nombre AS carrera, i.anio, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 2
                  AND i.deleted_at IS NULL
                  AND i.anio = ?
                ORDER BY i.titulo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anio]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTesisMasVistasAsc(): array
    {
        $sql = "SELECT i.codigo, i.titulo, i.autor, i.tutor, i.universidad,
                       c.nombre AS carrera, i.anio, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 2
                  AND i.deleted_at IS NULL
                ORDER BY i.visitas ASC, i.titulo ASC";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLibrosTodos(): array
    {
        $sql = "SELECT i.codigo, i.titulo, i.autor, c.nombre AS categoria,
                       i.edicion, i.revista AS editorial, i.anio, i.stock,
                       i.ubicacion, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 1
                  AND i.deleted_at IS NULL
                ORDER BY i.titulo ASC";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLibrosPorCategoria(int $categoriaId): array
    {
        $sql = "SELECT i.codigo, i.titulo, i.autor, c.nombre AS categoria,
                       i.edicion, i.revista AS editorial, i.anio, i.stock,
                       i.ubicacion, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 1
                  AND i.deleted_at IS NULL
                  AND i.categoria_id = ?
                ORDER BY i.titulo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLibrosVigentes5Anios(): array
    {
        $anioActual = (int) date('Y');
        $anioMinimo = $anioActual - 5;

        $sql = "SELECT i.codigo, i.titulo, i.autor, c.nombre AS categoria,
                       i.edicion, i.revista AS editorial, i.anio, i.stock,
                       i.ubicacion, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 1
                  AND i.deleted_at IS NULL
                  AND i.anio BETWEEN ? AND ?
                ORDER BY i.anio ASC, i.titulo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$anioMinimo, $anioActual]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPrestamosTotales(): array
    {
        return $this->getPrestamosPorEstados([]);
    }

    public function getPrestamosActivos(): array
    {
        return $this->getPrestamosPorEstados(['APROBADA', 'RETRASADO']);
    }

    public function getPrestamosPendientes(): array
    {
        return $this->getPrestamosPorEstados(['PENDIENTE']);
    }

    public function getPrestamosRechazados(): array
    {
        return $this->getPrestamosPorEstados(['RECHAZADA']);
    }

    public function getPrestamosDevueltos(): array
    {
        return $this->getPrestamosPorEstados(['ENTREGADO']);
    }

    private function getPrestamosPorEstados(array $estados): array
    {
        $sql = "SELECT sp.id,
                       CONCAT(u.nombre, ' ', u.apellido) AS estudiante,
                       i.titulo AS item_titulo,
                       sp.estado,
                       sp.fecha_solicitud,
                       sp.fecha_prestamo,
                       sp.fecha_devolucion,
                       sp.fecha_respuesta,
                       sp.motivo_rechazo
                FROM solicitudes_prestamo sp
                INNER JOIN usuarios u ON u.id = sp.usuario_id
                INNER JOIN items_biblioteca i ON i.id = sp.item_id";

        $params = [];
        if (!empty($estados)) {
            $placeholders = implode(',', array_fill(0, count($estados), '?'));
            $sql .= " WHERE sp.estado IN ($placeholders)";
            $params = $estados;
        }

        $sql .= " ORDER BY sp.fecha_solicitud ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicacionesTodas(): array
    {
        $sql = "SELECT i.codigo, i.titulo, i.autor, i.revista,
                       c.nombre AS categoria, i.anio, i.visitas, i.estado
                FROM items_biblioteca i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.tipo_id = 3
                  AND i.deleted_at IS NULL
                ORDER BY i.titulo ASC";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
