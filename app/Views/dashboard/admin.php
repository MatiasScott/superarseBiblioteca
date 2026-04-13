<?php
// app/Views/dashboard/admin.php

require_once __DIR__ . "/../../Models/LibrosModel.php";
require_once __DIR__ . "/../../Models/CategoriaModel.php";
require_once __DIR__ . "/../../Models/UserModel.php";
require_once __DIR__ . "/../../Models/SolicitudesModel.php";

$libroModel = new LibrosModel();
$categoriaModel = new CategoriaModel();
$userModel = new UserModel();
$solicitudesModel = new SolicitudesModel();

$libros = $libroModel->getAll();
$categorias = $categoriaModel->getCategoriasPorTipo(1);

// CONTADORES DINÁMICOS
$contadorLibros = $libroModel->contarLibros();
$contadorTesis = $libroModel->contarTesis();
$contadorPublicaciones = $libroModel->contarPublicaciones();
$contadorUsuarios = $userModel->contarUsuarios(); // <-- agregado
$contadorPrestamosActivos = $solicitudesModel->contarPrestamosActivos();
$contadorAtrasados = $solicitudesModel->contarAtrasados();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - Biblioteca Dra. Mery Navas</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logoSuperarse.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'superarse-morado-oscuro': '#1b4785ff',
                    'superarse-morado-medio': '#479990ff',
                    'superarse-rosa': '#164c7eff',
                }
            }
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
    /* Asegura que SweetAlert2 se muestre por encima de los modales personalizados */
    .swal2-container {
        z-index: 20000 !important;
    }
    .swal2-popup {
        z-index: 20001 !important;
    }
    </style>

</head>

<body class="bg-gray-50">
    <!-- Navbar -->
 <nav class="bg-gradient-to-r from-superarse-morado-oscuro to-superarse-morado-medio text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

        <!-- IZQUIERDA: Logo + Título -->
        <div class="flex items-center gap-3 min-w-0">
            <img src="<?= BASE_URL ?>/assets/img/LOGO SUPERARSE PNG-02.png"
                 alt="Logo"
                 class="h-9 w-auto shrink-0">

            <!-- Título responsive -->
            <h1 class="font-bold text-lg sm:text-xl md:text-2xl truncate">
                📚  Biblioteca Dra. Mery Navas
                <span class="hidden md:inline">- Administración</span>
            </h1>
        </div>

        <!-- DERECHA: Campana + Usuario + Logout -->
        <div class="flex items-center gap-4 sm:gap-6 relative">

            <!-- Campana -->
            <div class="relative">
                <button id="btnCampana"
                        class="relative text-2xl focus:outline-none hover:scale-110 transition"
                        aria-label="Notificaciones">
                    🔔
                    <span id="badgePendientes"
                          class="hidden absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full"> 0
                    </span>
                </button>

                <!-- PANEL DE SOLICITUDES -->
                <div id="panelSolicitudes"
                     class="hidden absolute right-0 mt-3 w-[90vw] sm:w-96 bg-white text-gray-800 rounded-xl shadow-lg z-50">

                    <div class="px-4 py-3 font-semibold bg-gray-100">
                        Solicitudes pendientes
                    </div>

                    <ul id="listaSolicitudes" class="divide-y max-h-72 overflow-y-auto">
                        <li class="px-4 py-4 text-sm text-center text-gray-500">
                            Cargando...
                        </li>
                    </ul>

                    <a href="#prestamos"
                       class="block text-center py-2 bg-gray-50 text-sm font-semibold text-superarse-morado-medio hover:bg-gray-100">
                        Ver todas las solicitudes
                    </a>
                </div>
            </div>

            <!-- Usuario (oculta texto en móvil) -->
            <div class="text-right hidden sm:block leading-tight">
                <p class="font-semibold text-sm md:text-base">
                    <?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Administrador') ?>
                </p>
                <p class="text-xs text-white/80">
                    💼 Administrador
                </p>
            </div>

            <!-- Logout -->
            <a href="logout"
               class="bg-superarse-rosa hover:bg-red-600 px-3 sm:px-4 py-2 rounded-lg font-semibold text-sm transition whitespace-nowrap">
                🚪 <span class="hidden sm:inline">Cerrar Sesión</span>
            </a>

        </div>
    </div>
</nav>
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8 border-l-4 border-superarse-rosa">
            <h2 class="text-4xl font-bold text-superarse-morado-oscuro mb-2">
                Panel de Control 🎛️
            </h2>
             <h2 class="text-4xl font-bold text-superarse-morado-oscuro mb-2">
        ¡Bienvenido/a, <?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Usuario'); ?>! 👋
    </h2>
            <p class="text-gray-600 text-lg">
                Gestiona usuarios, libros, tesis, publicaciones y solicitudes .
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-superarse-morado-oscuro items-center text-center">
                <div class="text-4xl mb-2">📚</div>
                <h3 class="text-gray-600 font-semibold mb-2">Total de Libros</h3>
                <p class="text-3xl font-bold text-superarse-morado-oscuro"><?= $contadorLibros ?></p>
                <p class="text-sm text-gray-500 mt-2">Stock total</p>
            </div>

             <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-green-500 items-center text-center">
                <div class="text-4xl mb-2">📂</div>
                <h3 class="text-gray-600 font-semibold mb-2">Total de Publicaciones </h3>
                <p class="text-3xl font-bold text-green-500"><?= $contadorPublicaciones?></p>
                <p class="text-sm text-gray-500 mt-2">Stock total</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-yellow-500 items-center text-center">
                <div class="text-4xl mb-2">🎓</div>
                <h3 class="text-gray-600 font-semibold mb-2">Total de Tesis</h3>
                <p class="text-3xl font-bold text-yellow-500"><?= $contadorTesis ?></p>
                <p class="text-sm text-gray-500 mt-2">Stock total</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-orange-500 items-center text-center">
                <div class="text-4xl mb-2">👥</div>
                <h3 class="text-gray-600 font-semibold mb-2">Usuarios Activos</h3>
                <p class="text-3xl font-bold text-orange-500"><?= $contadorUsuarios ?></p>
                <p class="text-sm text-gray-500 mt-2">Estudiantes y Administradores</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-superarse-rosa items-center text-center">
                <div class="text-4xl mb-2">📖</div>
                <h3 class="text-gray-600 font-semibold mb-2">Préstamos Activos</h3>
                <p class="text-3xl font-bold text-superarse-rosa"><?= $contadorPrestamosActivos ?></p>

                <p class="text-sm text-gray-500 mt-2">Libros en circulación</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-green-500 items-center text-center">
                <div class="text-4xl mb-2">⏰</div>
                <h3 class="text-gray-600 font-semibold mb-2">Atrasados</h3>
                <p class="text-3xl font-bold text-green-500"><?= $contadorAtrasados ?></p>
                <p class="text-sm text-gray-500 mt-2">Requieren seguimiento</p>
            </div>  
        </div>
        
        <!-- Tabs Navigation -->
        <div class="bg-white rounded-lg shadow-md mb-8 overflow-hidden">
            <div class="flex flex-wrap border-b">
                <button onclick="showTab('prestamos')" class="tab-btn active px-6 py-4 font-semibold text-superarse-morado-oscuro border-b-4 border-superarse-morado-oscuro transition">
                    📖 Préstamos
                </button>
                <button onclick="showTab('libros')" class="tab-btn px-6 py-4 font-semibold text-gray-600 border-b-4 border-transparent hover:text-superarse-morado-oscuro transition">
                    📚 Libros
                </button>
                <button onclick="showTab('Publicaciones')" class="tab-btn px-6 py-4 font-semibold text-gray-600 border-b-4 border-transparent hover:text-superarse-morado-oscuro transition">
                    📂 Publicaciones
                </button>
                <button onclick="showTab('Tesis')" class="tab-btn px-6 py-4 font-semibold text-gray-600 border-b-4 border-transparent hover:text-superarse-morado-oscuro transition">
                    🎓 Tesis
                </button>
                <button onclick="showTab('usuarios')" class="tab-btn px-6 py-4 font-semibold text-gray-600 border-b-4 border-transparent hover:text-superarse-morado-oscuro transition">
                    👥 Usuarios
                </button>
                <button onclick="showTab('categorias')" class="tab-btn px-6 py-4 font-semibold text-gray-600 border-b-4 border-transparent hover:text-superarse-morado-oscuro transition">
                    🏷️ Categorías
                </button>
               <button onclick="showTab('estadistica')" class="tab-btn px-6 py-4 font-semibold text-gray-600 border-b-4 border-transparent hover:text-superarse-morado-oscuro transition">
                    📊 Estadísticas
               </button>
               <button onclick="showTab('masVistos')" class="tab-btn px-6 py-4 font-semibold text-gray-600 border-b-4 border-transparent hover:text-superarse-morado-oscuro transition">
            👁️ Más Vistos
               </button>
            </div>
        </div>
        
<!----------------------------------------------------------------------------->
<!-------------------------- TABLA DE SOLICITUDES ----------------------------->

<div id="prestamos"
     class="tab-content bg-white rounded-2xl shadow-lg
            p-4 sm:p-6 lg:p-8 mb-8">

    <h3 class="text-xl sm:text-2xl font-bold text-superarse-morado-oscuro mb-5">
        Solicitudes de Préstamo
    </h3>

  <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

    <div>
        <label for="filtroMes" class="block font-semibold text-gray-700 mb-1">
            Filtrar por mes
        </label>
        <input type="month" id="filtroMes"
               class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
        <label for="filtroEstado" class="block font-semibold text-gray-700 mb-1">
            Filtrar por estado
        </label>
        <select id="filtroEstado"
                class="w-full border rounded-lg px-3 py-2">
            <option value="">Todos</option>
            <option value="PENDIENTE">Pendiente</option>
            <option value="APROBADA">Aprobada</option>
            <option value="ENTREGADO">Entregado</option>
            <option value="RECHAZADA">Rechazada</option>
            <option value="RETRASADO">Retrasado</option>
        </select>
    </div>

    <button onclick="aplicarFiltro()"
            class="bg-superarse-morado-oscuro text-white
                   px-4 py-2 rounded-lg
                   hover:bg-green-500 transition">
        Aplicar
    </button>

    <button onclick="limpiarFiltro()"
            class="bg-gray-300 text-gray-700
                   px-4 py-2 rounded-lg
                   hover:bg-gray-400 transition">
        Limpiar
    </button>
</div>

   <div class="overflow-x-auto overflow-y-auto max-h-[22rem] border rounded-xl">

    <table class="min-w-[900px] w-full text-sm" id="tablaSolicitudes">

        <thead class="bg-gray-100 border-b-2 border-superarse-morado-oscuro sticky top-0 z-10">
            <tr>
                <th class="px-4 py-2 font-bold text-left">Nombre</th>
                <th class="px-4 py-2 font-bold text-left">Apellido</th>
                <th class="px-4 py-2 font-bold text-left">Carrera</th>
                 <th class="px-4 py-2 font-bold text-left">Celular</th>
                <th class="px-4 py-2 font-bold text-left">Curso</th>
                <th class="px-4 py-2 font-bold text-left">Libro</th>
                <th class="px-4 py-2 font-bold text-left">Stock</th>
                <th class="px-4 py-2 font-bold text-left">Fecha Solicitud</th>
                <th class="px-4 py-2 font-bold text-left">Fecha Respuesta</th>
                <th class="px-4 py-2 font-bold text-left">Estado</th>
                <th class="px-4 py-2 font-bold text-left">Acciones</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td colspan="10" class="text-center py-6 text-gray-500">
                    Cargando...
                </td>
            </tr>
        </tbody>

    </table>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
const DEFAULT_COVER = `${BASE_URL}/assets/img/default-cover.png`;
</script>

<script src="<?= BASE_URL ?>/js/admin/prestamos.js" defer></script>


<!----------------------------------------------------------------------------->
<!-----------------------------------LIBROS TAB ------------------------------->
<div id="libros" class="tab-content hidden bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
    <!-- Título -->
    <h3 class="text-xl sm:text-2xl font-bold text-superarse-morado-oscuro">
        Catálogo de Libros
    </h3>
    <!-- Buscador + Botón -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-700 text-sm">
                Buscador:
            </span>
            <input type="text"
                   id="buscarLibro"
                   placeholder="🔍 Buscar título..."
                   class="input w-full sm:w-48 px-3 py-2 text-sm">
        </div>
        <button onclick="openModalLibro()"
                class="bg-superarse-morado-oscuro text-white
                       px-5 py-2 rounded-lg text-sm
                       hover:bg-superarse-morado-medio transition">
            ➕ Nuevo Libro
        </button>
         <button
    onclick="window.location.href = '<?= BASE_URL ?>/reporte/catalogo-libros'"
    class="flex items-center gap-2
           bg-green-600 text-white
           px-5 py-2.5 rounded-xl text-sm font-semibold
           shadow-md hover:shadow-lg
           hover:bg-blue-400 transition
           active:scale-95
           transition-all duration-200"
>
    📥
    <span>Exportar Catálogo</span>
</button>
    </div>
</div>

 <div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl">

    <table class="min-w-[1000px] w-full text-sm">

        <thead class="bg-gray-100 border-b-2 sticky top-0 z-10">
            <tr>
                <th class="px-4 py-3">Código</th>
                <th class="px-4 py-3">Portada</th>
                <th class="px-4 py-3">Título</th>
                <th class="px-4 py-3">Autor</th>
                <th class="px-4 py-3">Edición</th>
                <th class="px-4 py-3">Categoría</th>
                <th class="px-4 py-3">Año</th>
                <th class="px-4 py-3">Ejemplares</th>
                <th class="px-4 py-3">Stock</th>
                <th class="px-4 py-3">Editorial</th>
                <th class="px-4 py-3">Ubicación</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Acciones</th>
            </tr>
        </thead>

        <tbody id="tablaLibros">
            <tr>
                <td colspan="12" class="text-center py-6 text-gray-500">
                    Cargando...
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>


<!-- MODAL LIBRO ANIMADO -->
<div id="modalLibro" class="hidden fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50 transition">
  <div id="modalCard" class="bg-white rounded-xl p-6 w-full max-w-md sm:max-w-lg max-h-[80vh] overflow-y-auto scale-90 opacity-0 transition-all duration-200">
    <h3 id="modalLibroTitle" class="text-xl font-bold mb-3">Nuevo Libro</h3>

    <form id="formLibro" onsubmit="return false;">
      <input type="hidden" id="libro_id">

      <label>Código Institucional</label>
      <input id="libro_codigo" class="input">

      <label>Portada (URL)</label>
      <input id="libro_portada" class="input">

      <label>Título</label>
      <input id="libro_titulo" class="input">

      <label>Autor</label>
      <input id="libro_autor" class="input">

      <label>Edición</label>
      <input id="libro_edicion" class="input">

      <label>Editorial</label>
      <input id="libro_revista" class="input">

      <label>Codigo de Barras</label>
      <input id="libro_codigo_barra" class="input">

      <label>Categoría</label>
      <select id="libro_categoria" class="input"></select>

      <label>Año</label>
      <input id="libro_anio" type="number" class="input">

      <label>Número de ejemplares</label>
      <input id="libro_numero_ejemplares" type="number" class="input">

      <label>Stock</label>
      <p style="color: red; font-weight: bold; font-size: 0.9em; margin-top: 5px; margin-bottom: 5px;">
        * Importante: Tiene que ser igual al Número de ejemplares.
    </p>
      <input id="libro_stock" type="number" class="input">

      <label>Ubicación</label>
      <input id="libro_ubicacion" class="input">

      <label>Descripción</label>
      <textarea id="libro_descripcion" class="input"></textarea>

      <div id="campoEstadoLibro" class="hidden mb-4">
        <label>Estado</label>
        <select id="libro_estado" class="input">
          <option value="ACTIVO">ACTIVO</option>
          <option value="INACTIVO">INACTIVO</option>
        </select>
      </div>

      <div class="flex justify-end gap-2 mt-3">
        <button onclick="closeModalLibro()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
        <button onclick="submitLibro()" class="px-4 py-2 bg-superarse-morado-oscuro text-white rounded hover:bg-purple-900 transition">
          Guardar
        </button>
      </div>
    </form>

  </div>
</div>

<style>
.input { width: 100%; border: 1px solid #ccc; padding: 8px; border-radius: 6px; margin-bottom: 8px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>/js/admin/libros.js"></script>


<!----------------------------------------------------------------------------->
<!---------------------------- PUBLICACIONES TABLA ---------------------------->

<div id="Publicaciones" class="tab-content hidden bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">

  <h3 class="text-xl sm:text-2xl font-bold text-superarse-morado-oscuro">
        Catálogo de Publicaciones
    </h3>

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">

        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-700 text-sm">
                Buscador:
            </span>

            <input type="text"
                   id="buscarPublicacion"
                   placeholder="🔍 Buscar título..."
                   class="input w-full sm:w-48 px-3 py-2 text-sm">
        </div>

        <button onclick="openModal()"
                class="bg-superarse-morado-oscuro text-white
                       px-5 py-2 rounded-lg text-sm
                       hover:bg-superarse-morado-medio transition">
            ➕ Nueva Publicación
        </button>
    </div>
</div>

 <div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl">

    <table class="min-w-[950px] w-full text-sm">

        <thead class="bg-gray-100 border-b-2 sticky top-0 z-10">
            <tr>
                <th class="px-4 py-3">Código</th>
                <th class="px-4 py-3">Portada</th>
                <th class="px-4 py-3">Título</th>
                <th class="px-4 py-3">Autor</th>
                <th class="px-4 py-3">Revista</th>
                <th class="px-4 py-3">Año</th>
                <th class="px-4 py-3">Descripción</th>
                <th class="px-4 py-3">Categoría</th>
                <th class="px-4 py-3">PDF</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Acciones</th>
            </tr>
        </thead>

        <tbody id="tablaPublicaciones">
            <tr>
                <td colspan="10"
                    class="text-center py-6 text-gray-500">
                    Cargando...
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>

<!--------------------------------- MODAL ------------------------------------->

<div id="modalPub" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-md sm:max-w-lg max-h-[80vh] overflow-y-auto">
    <h3 id="modalTitle" class="text-xl font-bold mb-3">Nueva Publicación</h3>

    <form id="formPub" onsubmit="return false;">
      <input type="hidden" id="id" name="id">

      <label>Portada (URL de Imagen)</label>
      <input id="portada" name="portada" class="w-full border px-3 py-2 mb-2">

      <label>Título</label>
      <input id="titulo" name="titulo" class="w-full border px-3 py-2 mb-2">

      <label>Autor</label>
      <input id="autor" name="autor" class="w-full border px-3 py-2 mb-2">

      <label>Revista</label>
      <input id="revista" name="revista" class="w-full border px-3 py-2 mb-2">

      <label>Año</label>
      <input id="anio" name="anio" type="number" class="w-full border px-3 py-2 mb-2">

      <label>Descripción</label>
      <textarea id="descripcion" name="descripcion" class="w-full border px-3 py-2 mb-2"></textarea>

      <label>Categoría</label>
      <select id="categoria_id" name="categoria_id" class="w-full border px-3 py-2 mb-2">
        <option value="">Seleccione una categoría</option>
      </select>

      <label>Link PDF</label>
      <input id="link_archivo" name="link_archivo" class="w-full border px-3 py-2 mb-2">

      <div id="campoEstadoPub" class="hidden mb-4">
        <label>Estado</label>
        <select id="pub_estado" class="w-full border px-3 py-2">
          <option value="ACTIVO">ACTIVO</option>
          <option value="INACTIVO">INACTIVO</option>
        </select>
      </div>

      <div class="flex justify-end gap-2 mt-3">
        <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
        <button onclick="submitPub()" class="px-4 py-2 bg-superarse-morado-oscuro text-white rounded">
          Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/publicaciones.js"></script>

<!----------------------------------------------------------------------------->
<!---------------------------- TESIS TABLA ------------------------------------>

<div id="Tesis" class="tab-content hidden bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
    <h3 class="text-xl sm:text-2xl font-bold text-superarse-morado-oscuro">
        Repositorio de Tesis
    </h3>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">

        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-700 text-sm">
                Buscador:
            </span>
            <input type="text"
                   id="buscarTesis"
                   placeholder="🔍 Buscar título..."
                   class="input w-full sm:w-48 px-3 py-2 text-sm">
        </div>
        <button onclick="openModalTesis()"
                class="bg-superarse-morado-oscuro text-white
                       px-5 py-2 rounded-lg text-sm
                       hover:bg-superarse-morado-medio transition">
            ➕ Nueva Tesis
        </button>
    </div>
</div>
  <div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl">

    <table class="min-w-[1100px] w-full text-sm">

        <thead class="bg-gray-100 border-b-2 sticky top-0 z-10">
            <tr>
                <th class="px-4 py-3">Código</th>
                <th class="px-4 py-3">Título</th>
                <th class="px-4 py-3">Portada</th>
                <th class="px-4 py-3">Autor</th>
                <th class="px-4 py-3">Tutor</th>
                <th class="px-4 py-3">Instituto</th>
                <th class="px-4 py-3">Carrera/Categoria</th>
                <th class="px-4 py-3">Año</th>
                <th class="px-4 py-3">Palabras Clave</th>
                <th class="px-4 py-3">PDF</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Acciones</th>
            </tr>
        </thead>

        <tbody id="tablaTesis">
            <tr>
                <td colspan="11"
                    class="text-center py-6 text-gray-500">
                    Cargando...
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>

<!-- ========================= MODAL TESIS ================================= -->

<div id="modalTesis" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl p-6 w-full max-w-md sm:max-w-lg max-h-[85vh] overflow-y-auto shadow-lg">
    <h3 id="modalTesisTitle" class="text-xl font-bold mb-3">Nueva Tesis</h3>

    <form id="formTesis" onsubmit="return false;">
      <input type="hidden" id="tesis_id">

      <label class="block font-medium">Título <span class="text-red-600">*</span></label>
      <input id="tesis_titulo" class="w-full border px-3 py-2 mb-2" />

      <label class="block font-medium">Portada (URL)</label>
      <input id="tesis_portada" class="w-full border px-3 py-2 mb-2" />

      <label class="block font-medium">Autor <span class="text-red-600">*</span></label>
      <input id="tesis_autor" class="w-full border px-3 py-2 mb-2" />

      <label class="block font-medium">Tutor</label>
      <input id="tesis_tutor" class="w-full border px-3 py-2 mb-2" />

      <label class="block font-medium">Instituto</label>
      <input id="tesis_universidad" class="w-full border px-3 py-2 mb-2" />

      <label class="block font-medium">Carrera/Categoria <span class="text-red-600">*</span></label>
      <select id="tesis_categoria" class="w-full border px-3 py-2 mb-2"></select>

      <label class="block font-medium">Año de creación</label>
      <input id="tesis_anio" type="number" class="w-full border px-3 py-2 mb-2" />

      <label class="block font-medium">Palabras Claves / Descripción</label>
      <textarea id="tesis_descripcion" class="w-full border px-3 py-2 mb-2"></textarea>

      <label class="block font-medium">Link PDF</label>
      <!-- aquí usamos id tesis_link (coincide con link_archivo en backend) -->
      <input id="tesis_link" class="w-full border px-3 py-2 mb-2" />

      <div id="campoEstadoTesis" class="hidden mb-4">
        <label class="block font-medium">Estado</label>
        <select id="tesis_estado" class="w-full border px-3 py-2">
          <option value="ACTIVO">ACTIVO</option>
          <option value="INACTIVO">INACTIVO</option>
        </select>
      </div>

      <div class="flex justify-between items-center gap-2 mt-3">
        <div class="text-sm text-gray-500">Campos marcados con <span class="text-red-600">*</span> son obligatorios</div>
        <div class="flex gap-2">
          <button type="button" onclick="closeModalTesis()" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
          <button type="button" onclick="onSaveTesis()" class="px-4 py-2 bg-superarse-morado-oscuro text-white rounded">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/tesis.js"></script>

<!----------------------------------------------------------------------------->
<!----------------------------TABLA DE USUARIOS ------------------------------->

<div id="usuarios" class="tab-content hidden bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">


<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">

    <h3 class="text-xl sm:text-2xl font-bold text-superarse-morado-oscuro">
        Gestión de Usuarios
    </h3>

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">

        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-700 text-sm">
                Buscador:
            </span>

            <input type="text"
                   id="buscarUsuario"
                   placeholder="🔍 Nombre o Cédula..."
                   class="input w-full sm:w-56 px-3 py-2 text-sm">
        </div>

        <button onclick="abrirFormularioUsuario()"
                class="bg-superarse-morado-oscuro text-white
                       px-5 py-2 rounded-lg text-sm
                       hover:bg-superarse-morado-medio transition">
            ➕ Nuevo Usuario
        </button>
    </div>
</div>

   <div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl">

    <table class="min-w-[900px] w-full text-sm">

        <thead class="bg-gray-100 border-b-2 border-superarse-morado-oscuro sticky top-0 z-10">
            <tr>
                <th class="px-4 py-3 text-left font-bold">Nombre</th>
                <th class="px-4 py-3 text-left font-bold">Cédula</th>
                <th class="px-4 py-3 text-left font-bold">Email</th>
                <th class="px-4 py-3 text-left font-bold">Carrera</th>
                <th class="px-4 py-3 text-left font-bold">Curso</th>
                <th class="px-4 py-3 text-left font-bold">Rol</th>
                <th class="px-4 py-3 text-left font-bold">Estado</th>
                <th class="px-4 py-3 text-left font-bold">Acciones</th>
            </tr>
        </thead>

        <tbody id="usuariosTableBody">
            <tr>
                <td colspan="8"
                    class="px-4 py-6 text-center text-gray-500">
                    Cargando usuarios...
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>

<div id="modalUsuario" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6 max-h-[80vh] overflow-y-auto">
        <h2 id="tituloModal" class="text-2xl font-bold text-superarse-morado-oscuro mb-4">Nuevo Usuario</h2>
        
        <form id="formUsuario" onsubmit="guardarUsuario(event)">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <input type="text" id="nombre" placeholder="Nombre" class="border rounded px-3 py-2 w-full" required>
                <input type="text" id="apellido" placeholder="Apellido" class="border rounded px-3 py-2 w-full" required>
            </div>

            <input type="text" id="cedula" placeholder="Cédula" maxlength="10" inputmode="numeric" class="border rounded px-3 py-2 w-full mb-4" required oninput="soloNumeros10(this)">
            <input type="email" id="email" placeholder="Email" class="border rounded px-3 py-2 w-full mb-4" required>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Rol</label>
                <select id="rol" class="border rounded px-3 py-2 w-full" required onchange="actualizarCamposDinamicos()">
                    <option value="">Seleccionar rol...</option>
                </select>
            </div>

            <input type="tel" id="telefono" placeholder="Teléfono" maxlength="10" inputmode="numeric" class="border rounded px-3 py-2 w-full mb-4" oninput="soloNumeros10(this)">
            <input type="text" id="direccion" placeholder="Dirección" class="border rounded px-3 py-2 w-full mb-4">

            <!-- Campos dinámicos para estudiantes -->
            <div id="campoCarrera" class="hidden mb-4">
                <input type="text" id="carrera" placeholder="Carrera" class="border rounded px-3 py-2 w-full">
            </div>

            <div id="campoCurso" class="hidden mb-4">
                <input type="text" id="curso" placeholder="Curso" class="border rounded px-3 py-2 w-full">
            </div>

            <div id="campoContrasena" class="hidden mb-4 relative">
    <input 
        type="password" 
        id="contrasena" 
        placeholder="Contraseña (opcional)" 
        class="border rounded px-3 py-2 w-full pr-10"
    >

    <i 
        id="toggleIcon"
        class="fa-solid fa-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-500"
        onclick="togglePassword()"
    ></i>
</div>
<script>
function togglePassword() {
    const input = document.getElementById("contrasena");
    const icon = document.getElementById("toggleIcon");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>


            <div id="campoEstado" class="hidden mb-4">
                <label class="block text-sm font-semibold mb-2">Estado</label>
                <select id="estado" class="border rounded px-3 py-2 w-full">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
            </div>

            <input type="hidden" id="usuarioId">

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-superarse-rosa hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition">
                    Guardar
                </button>
                <button type="button" onclick="cerrarModalUsuario()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/usuarios.js" defer></script>

<!----------------------------------------------------------------------------->
<!------------------------------ TABLA CATEGORIAS ----------------------------->

<div id="categorias" class="tab-content hidden bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">
 <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
    <h3 class="text-xl sm:text-2xl font-bold text-superarse-morado-oscuro">
        Categorías de Libros
    </h3>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-700 text-sm">
                Buscador:
            </span>
            <input type="text"
                   id="buscarCategoria"
                   placeholder="🔍 Buscar por nombre..."
                   class="input w-full sm:w-56 px-3 py-2 text-sm border rounded">
        </div>
        <button onclick="abrirModalCategoria()"
                class="bg-superarse-morado-oscuro hover:bg-superarse-morado-medio
                       text-white px-5 py-2 rounded-lg text-sm transition">
            ➕ Nueva Categoría
        </button>
    </div>
</div>

<div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl">

    <table class="min-w-[600px] w-full text-sm">

        <thead class="bg-gray-100 border-b-2 border-superarse-morado-oscuro sticky top-0 z-10">
            <tr>
                <th class="px-4 py-3 text-left font-bold">Nombre</th>
                <th class="px-4 py-3 text-left font-bold">Tipo</th>
                <th class="px-4 py-3 text-left font-bold">Estado</th>
                <th class="px-4 py-3 text-left font-bold">Acciones</th>
            </tr>
        </thead>

        <tbody id="tablaCategorias">
            <tr>
                <td colspan="4"
                    class="text-center py-6 text-gray-500">
                    Cargando...
                </td>
            </tr>
        </tbody>
    </table>
</div>

</div>

<!------------------------ Modal Crear / Editar ------------------------------>
<div id="modalCategoria" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-[9999]">
    <div class="bg-white p-6 rounded-xl w-96 relative z-[10000]">
        <h3 id="tituloModal" class="text-xl font-bold mb-4">Nueva Categoría</h3>

        <input type="hidden" id="categoriaId">

        <div class="mb-4">
            <label class="block text-gray-700">Nombre</label>
            <input id="categoriaNombre" type="text" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Tipo</label>
           <select id="categoriaTipo" class="w-full border rounded px-3 py-2">
    <option value="">Seleccione un tipo...</option>
</select>

        </div>

      <div class="mb-4 hidden" id="campoEstadoCategoria">
    <label class="block text-gray-700">Estado</label>
    <select id="categoriaEstado" class="w-full border rounded px-3 py-2">
        <option value="ACTIVO">ACTIVO</option>
        <option value="INACTIVO">INACTIVO</option>
    </select>
</div>
        <div class="flex justify-end gap-2">
            <button onclick="cerrarModalCategoria()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancelar</button>
            <button onclick="guardarCategoria()" class="px-4 py-2 rounded bg-superarse-morado-oscuro text-white hover:bg-superarse-morado-medio">Guardar</button>
        </div>

        <button onclick="cerrarModalCategoria()" class="absolute top-2 right-3 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/categorias.js" defer></script>


<!----------------------------------------------------------------------------->
<!-------------------------------- ESTADISTICAS ------------------------------->

<div id="estadistica" class="tab-content hidden bg-white rounded-xl shadow-lg p-8 mb-8">
    <h2 class="text-3xl font-bold text-superarse-morado-oscuro mb-6">📊 Estadísticas Completas</h2>

     <button onclick="descargarExcelEstadisticas()"
        class="bg-green-600 text-white px-4 py-2 rounded mb-4 hover:bg-green-700">
    📥 Descargar Excel
</button>

    <!-- Contadores -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8" id="contadoresEstadisticas">
        <p class="text-center text-gray-500 col-span-6 py-6">Cargando...</p>
    </div>

    <!-- Gráficos -->
    <canvas id="graficoPrestamos" class="mb-8" height="100"></canvas>
    <canvas id="graficoItems" class="mb-8" height="100"></canvas>
    <canvas id="graficoUsuarios" class="mb-8" height="100"></canvas>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- BASE_URL disponible para JS externo -->


<!-- Tu JS separado -->
<script src="<?= BASE_URL ?>/js/admin/estadisticas.js" defer></script>


<!----------------------------------------------------------------------------->
<!----------------------------- MAS VISTOS ------------------------------------>

<div id="masVistos" class="tab-content hidden bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

<div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 mb-6 items-start sm:items-end">
    <div class="flex flex-col">
        <label class="font-semibold text-gray-700 text-sm">Desde</label>
        <input type="date"
               id="fechaInicio"
               class="border rounded px-3 py-2"
               value="<?= date('Y-m-01') ?>">
    </div>

    <div class="flex flex-col">
        <label class="font-semibold text-gray-700 text-sm">Hasta</label>
        <input type="date"
               id="fechaFin"
               class="border rounded px-3 py-2"
               value="<?= date('Y-m-d') ?>">
    </div>

    <button onclick="filtrarRango()"
            class="w-full sm:w-auto px-6 py-2
                   bg-blue-600 text-white rounded-lg
                   hover:bg-green-400 transition">
        🔍 Filtrar
    </button>
</div>

<!-- ================= SUBTABS ================= -->

<div class="flex gap-3 mb-6 overflow-x-auto pb-2">
    <button onclick="showSubTab('libros')"
            class="flex-shrink-0 px-5 py-2 bg-purple-700 text-white rounded-lg
            hover:bg-purple-400 transition">
        📚 Libros
    </button>

    <button onclick="showSubTab('tesis')"
            class="flex-shrink-0 px-5 py-2 bg-green-600 text-white rounded-lg
            hover:bg-blue-400 transition">
        🎓 Tesis
    </button>

    <button onclick="showSubTab('publicaciones')"
            class="flex-shrink-0 px-5 py-2 bg-orange-500 text-white rounded-lg
            hover:bg-red-500 transition">
        📰 Publicaciones
    </button>
</div>

<!-- ================= 📚 LIBROS ================= -->
<div id="librosChartContainer" class="sub-tab">

    <div class="flex justify-end mb-3">
        <button onclick="descargarExcel('libros')"
                class="px-4 py-2 bg-blue-700 text-white rounded-lg
                hover:bg-green-400 transition">
            ⬇️ Excel Libros
        </button>
    </div>

    <div class="w-full h-[420px]">
        <canvas id="chartLibros"></canvas>
    </div>
</div>

<!-- ================= 🎓 TESIS ================= -->
<div id="tesisChartContainer" class="sub-tab hidden">

    <div class="flex justify-end mb-3">
        <button onclick="descargarExcel('tesis')"
                class="px-4 py-2 bg-green-700 text-white rounded-lg
                hover:blue-400 transition">
            ⬇️ Excel Tesis
        </button>
    </div>

    <div class="w-full h-[420px]">
        <canvas id="chartTesis"></canvas>
    </div>
</div>

<!-- ================= 📰 PUBLICACIONES ================= -->
<div id="publicacionesChartContainer" class="sub-tab hidden">

    <div class="flex justify-end mb-3">
        <button onclick="descargarExcel('publicaciones')"
                class="px-4 py-2 bg-orange-600 text-white rounded-lg
                hover:bg-red-500 transition">
            ⬇️ Excel Publicaciones
        </button>
    </div>

    <div class="w-full h-[420px]">
        <canvas id="chartPublicaciones"></canvas>
    </div>
</div>

</div>

<!------------------------SCRIP DE MAS VISTOS --------------------------------->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>/js/admin/masVistos.js" defer></script>

<!--------------------------SCRIP DE NOTIFICACIONES --------------------------->

<script src="<?= BASE_URL ?>/js/admin/notificaciones.js?v=<?= time() ?>"></script>

</div>
</div>

<!-----------------------------FOOTER------------------------------------------>

   <?php include(__DIR__ . '/../footer.php'); ?>
   
</body>
</html>
