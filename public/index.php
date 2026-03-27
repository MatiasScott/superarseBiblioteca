<?php
// index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', '');

/* ==========================================
   CARGA DE CONTROLADORES
========================================== */
require_once '../app/Controllers/LoginController.php';
require_once '../app/Controllers/DashboardController.php';
require_once '../app/Controllers/LibrosController.php';
require_once '../app/Controllers/AdminController.php';
require_once '../app/Controllers/InicioController.php';
require_once '../app/Controllers/TesisController.php';
require_once '../app/Controllers/PublicacionesController.php';
require_once '../app/Controllers/CategoriaController.php';
require_once '../app/Controllers/SolicitudesController.php';
require_once '../app/Controllers/EstadisticasController.php';
require_once '../app/Controllers/ReporteController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* ==========================================
   VARIABLES DE RUTA
========================================== */
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$relativeUri = rtrim($requestUri, '/');

if ($relativeUri === '') {
    $relativeUri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

/* ==========================================
   RUTAS
========================================== */

switch (true) {

/* ==========================================
       INICIO
========================================== */
    case ($relativeUri === '/' || $relativeUri === '/inicio'):
        (new InicioController())->index();
        break;

/* ==========================================
       LOGIN
========================================== */
    case ($relativeUri === '/login'):
        (new LoginController())->index();
        break;

    case ($relativeUri === '/login/check' && $method === 'POST'):
        (new LoginController())->check();
        break;

    case ($relativeUri === '/dashboard'):
    (new DashboardController())->index();
    break;


/* ==========================================
       DASHBOARD
========================================== */

    case ($relativeUri === '/dashboard'):
        (new DashboardController())->index();
        break;

/* ==========================================
   LIBROS CRUD
========================================== */

// Mostrar listado JSON (para la tabla)
case ($relativeUri === '/libros/indexJson'):
    (new LibrosController())->indexJson();
    break;

// Crear libro
case ($relativeUri === '/libros/createJson' && $method === 'POST'):
    (new LibrosController())->createJson();
    break;

// Actualizar libro
case ($relativeUri === '/libros/updateJson' && $method === 'POST'):
    (new LibrosController())->updateJson();
    break;

// Eliminar libro
case ($relativeUri === '/libros/deleteJson' && $method === 'POST'):
    (new LibrosController())->deleteJson();
    break;
    
/* ==========================================
   SUMAR VISITA LIBRO
========================================== */
case (preg_match('#^/libros/sumarVisita/(\d+)$#', $relativeUri, $matches) === 1):
    (new LibrosController())->sumarVisita($matches[1]);
    break;

// Más vistos (opcional filtro por mes)
case ($relativeUri === '/libros/masVistosJson'):
    (new LibrosController())->masVistosJson($_GET['mes'] ?? null, $_GET['anio'] ?? null);
    break;
case ($relativeUri === '/reporte/librosMasVistos'):
    (new ReporteController())->reporteLibrosMasVistos();
    break;
case ($relativeUri === '/reporte/catalogo-libros'):
    (new ReporteController())->exportarCatalogoLibros();
    break;

/* ==========================================
   TESIS CRUD JSON
========================================== */

case ($relativeUri === '/tesis/indexJson'):
    (new TesisController())->indexJson();
    break;

case ($relativeUri === '/tesis/getJson' && isset($_GET['id'])):
    (new TesisController())->getJson($_GET['id']);
    break;

case ($relativeUri === '/tesis/createJson' && $method === 'POST'):
    (new TesisController())->createJson();
    break;

case ($relativeUri === '/tesis/updateJson' && $method === 'POST'):
    (new TesisController())->updateJson();
    break;

case ($relativeUri === '/tesis/deleteJson' && $method === 'POST'):
    (new TesisController())->deleteJson();
    break;
case (preg_match('#^/tesis/sumarVisita/(\d+)$#', $relativeUri, $matches) === 1):
    (new TesisController())->sumarVisita($matches[1]);
    break;
case ($relativeUri === '/tesis/masVistosJson'):
    (new TesisController())->masVistosJson();
    break;
case ($relativeUri === '/reporte/tesisMasVistos'):
    (new ReporteController())->reporteTesisMasVistas();
    break;



/* ==========================================
   ESTUDIANTE - CATÁLOGO DE LIBROS
========================================== */
case ($relativeUri === '/libros/catalogo'):
    (new LibrosController())->catalogoEstudiante();
    break;

case ($relativeUri === '/tesis'):
    (new TesisController())->catalogo();
    break;
// Publicaciones
case ($relativeUri === '/publicaciones'):
    (new PublicacionesController())->catalogo();
    break;

/* ==========================================
   PUBLICACIONES CRUD
========================================== */

case ($relativeUri === '/publicaciones'):
    echo json_encode([
        "success" => false,
        "message" => "Usa /publicaciones/indexJson para la API"
    ]);
    break;

case ($relativeUri === '/publicaciones/indexJson'):
    (new PublicacionesController())->indexJson();
    break;

case ($relativeUri === '/publicaciones/getJson' && isset($_GET['id'])):
    (new PublicacionesController())->getJson($_GET['id']);
    break;

case ($relativeUri === '/publicaciones/createJson' && $method === 'POST'):
    (new PublicacionesController())->createJson();
    break;

case ($relativeUri === '/publicaciones/updateJson' && $method === 'POST'):
    (new PublicacionesController())->updateJson();
    break;

case ($relativeUri === '/publicaciones/deleteJson' && $method === 'POST'):
    (new PublicacionesController())->deleteJson();
    break;
/* ==========================================
   SUMAR VISITA PUBLICACIÓN
========================================== */
case (preg_match('#^/publicaciones/sumarVisita/(\d+)$#', $relativeUri, $matches) === 1):
    (new PublicacionesController())->sumarVisita($matches[1]);
    break;
    case ($relativeUri === '/publicaciones/masVistosJson'):
    (new PublicacionesController())->masVistosJson();
    break;
   // Reporte Excel - Publicaciones más vistas
case ($relativeUri === '/reporte/publicacionesMasVistos'):
    (new ReporteController())->reportePublicacionesMasVistas();
    break;



   /* ==========================================
       SOLICITUDES
    ========================================== */
case ($relativeUri === '/solicitudes/crear' && $method === 'POST'):
    (new SolicitudesController())->crear();
    break;

case ($relativeUri === '/solicitudes/list'):
    (new SolicitudesController())->listar();
    break;

case ($relativeUri === '/solicitudes/pendientes'):
    (new SolicitudesController())->obtenerPendientes();
    break;

case ($relativeUri === '/solicitudes/aprobar' && $method === 'POST'):
    (new SolicitudesController())->aprobar();
    break;

case ($relativeUri === '/solicitudes/rechazar' && $method === 'POST'):
    (new SolicitudesController())->rechazar();
    break;

case ($relativeUri === '/solicitudes/entregar' && $method === 'POST'):
    (new SolicitudesController())->entregar();
    break;

    
// Estudiante - mis préstamos
case ($relativeUri === '/solicitudes/mis'):
    (new SolicitudesController())->mis();
    break;

// (Rutas de notificaciones de estudiante eliminadas)


    /* ==========================================
       CATEGORÍAS
    ========================================== */
    case ($relativeUri === '/categorias/get'):
        (new CategoriaController())->getCategoriasJson();
        break;

    case ($relativeUri === '/categorias/create'):
        (new CategoriaController())->createCategoria();
        break;

    case ($relativeUri === '/categorias/update'):
        (new CategoriaController())->updateCategoria();
        break;

    case ($relativeUri === '/categorias/delete'):
        (new CategoriaController())->deleteCategoria();
        break;

    case ($relativeUri === '/categorias/tipos'):
        (new CategoriaController())->getTiposJson();
        break;

    /* ==========================================
       ADMIN - USUARIOS
    ========================================== */
    case ($relativeUri === '/admin/usuarios/get'):
        (new AdminController())->getUsersJson();
        break;

    case ($relativeUri === '/admin/usuarios/roles'):
        (new AdminController())->getRolesJson();
        break;

    case ($relativeUri === '/admin/usuarios/create'):
        (new AdminController())->createUser();
        break;

    case ($relativeUri === '/admin/usuarios/update'):
        (new AdminController())->updateUser();
        break;

    case ($relativeUri === '/admin/usuarios/delete'):
        (new AdminController())->deleteUser();
        break;

    /*==========================================
    ESTADISTICAS 
    ==========================================*/
case ($relativeUri === '/estadisticas/api'):
    (new EstadisticasController())->indexJson();
    break;

case ($relativeUri === '/estadisticas/exportExcel'):
    (new EstadisticasController())->exportExcel();
    break;

    /* ==========================================
       LOGOUT
    ========================================== */
    case ($relativeUri === '/logout'):
        session_destroy();
        header("Location: " . BASE_URL . "/");
        exit();

    /* ==========================================
       404
    ========================================== */
    default:
        http_response_code(404);
        echo "404 - Ruta no encontrada: <b>$relativeUri</b>";
        break;
} 