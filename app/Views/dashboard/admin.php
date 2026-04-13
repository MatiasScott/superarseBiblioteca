<?php
// app/Views/dashboard/admin.php

require_once __DIR__ . "/../../Models/LibrosModel.php";
require_once __DIR__ . "/../../Models/UserModel.php";
require_once __DIR__ . "/../../Models/SolicitudesModel.php";

$libroModel = new LibrosModel();
$userModel = new UserModel();
$solicitudesModel = new SolicitudesModel();

// CONTADORES DINÁMICOS
$contadorLibros = $libroModel->contarLibros();
$contadorTesis = $libroModel->contarTesis();
$contadorPublicaciones = $libroModel->contarPublicaciones();
$contadorUsuarios = $userModel->contarUsuarios(); // <-- agregado
$contadorPrestamosActivos = $solicitudesModel->contarPrestamosActivos();
$contadorAtrasados = $solicitudesModel->contarAtrasados();
?>

<?php
$pageTitle    = 'Panel de Control';
$activeModule = 'dashboard';
include __DIR__ . '/../layouts/admin_header.php';
?>

<!-- Bienvenida -->
<div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-8 border-l-4 border-[#164c7e]">
    <h2 class="text-3xl sm:text-4xl font-bold text-[#1b4785] mb-1">Panel de Control 🎛️</h2>
    <h3 class="text-xl sm:text-2xl font-semibold text-[#1b4785] mb-2">
        ¡Bienvenido/a, <?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Usuario') ?>! 👋
    </h3>
    <p class="text-gray-500">Gestiona usuarios, libros, tesis, publicaciones y solicitudes desde el menú lateral.</p>
</div>

<!-- Tarjetas de estadísticas -->
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">

    <a href="<?= BASE_URL ?>/admin/libros"
       class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition
              border-t-4 border-[#1b4785] text-center group">
        <div class="text-4xl mb-2">📚</div>
        <p class="text-gray-500 font-semibold text-sm mb-1">Libros</p>
        <p class="text-3xl font-bold text-[#1b4785]"><?= $contadorLibros ?></p>
        <p class="text-xs text-gray-400 mt-1 group-hover:underline">Ver catálogo →</p>
    </a>

    <a href="<?= BASE_URL ?>/admin/publicaciones"
       class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition
              border-t-4 border-green-500 text-center group">
        <div class="text-4xl mb-2">📂</div>
        <p class="text-gray-500 font-semibold text-sm mb-1">Publicaciones</p>
        <p class="text-3xl font-bold text-green-500"><?= $contadorPublicaciones ?></p>
        <p class="text-xs text-gray-400 mt-1 group-hover:underline">Ver catálogo →</p>
    </a>

    <a href="<?= BASE_URL ?>/admin/tesis"
       class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition
              border-t-4 border-yellow-500 text-center group">
        <div class="text-4xl mb-2">🎓</div>
        <p class="text-gray-500 font-semibold text-sm mb-1">Tesis</p>
        <p class="text-3xl font-bold text-yellow-500"><?= $contadorTesis ?></p>
        <p class="text-xs text-gray-400 mt-1 group-hover:underline">Ver repositorio →</p>
    </a>

    <a href="<?= BASE_URL ?>/admin/usuarios"
       class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition
              border-t-4 border-orange-500 text-center group">
        <div class="text-4xl mb-2">👥</div>
        <p class="text-gray-500 font-semibold text-sm mb-1">Usuarios</p>
        <p class="text-3xl font-bold text-orange-500"><?= $contadorUsuarios ?></p>
        <p class="text-xs text-gray-400 mt-1 group-hover:underline">Gestionar →</p>
    </a>

    <a href="<?= BASE_URL ?>/admin/prestamos"
       class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition
              border-t-4 border-[#164c7e] text-center group">
        <div class="text-4xl mb-2">📖</div>
        <p class="text-gray-500 font-semibold text-sm mb-1">Préstamos Activos</p>
        <p class="text-3xl font-bold text-[#164c7e]"><?= $contadorPrestamosActivos ?></p>
        <p class="text-xs text-gray-400 mt-1 group-hover:underline">Ver solicitudes →</p>
    </a>

    <a href="<?= BASE_URL ?>/admin/prestamos"
       class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition
              border-t-4 border-red-500 text-center group">
        <div class="text-4xl mb-2">⏰</div>
        <p class="text-gray-500 font-semibold text-sm mb-1">Atrasados</p>
        <p class="text-3xl font-bold text-red-500"><?= $contadorAtrasados ?></p>
        <p class="text-xs text-gray-400 mt-1 group-hover:underline">Revisar →</p>
    </a>
</div>

<!-- Accesos rápidos -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h4 class="text-lg font-bold text-[#1b4785] mb-4">⚡ Accesos Rápidos</h4>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <?php
        $accesos = [
            ['🔔', 'Préstamos Pendientes', BASE_URL . '/admin/prestamos'],
            ['📚', 'Agregar Libro',        BASE_URL . '/admin/libros'],
            ['🎓', 'Agregar Tesis',        BASE_URL . '/admin/tesis'],
            ['📂', 'Agregar Publicación',  BASE_URL . '/admin/publicaciones'],
            ['👤', 'Nuevo Usuario',        BASE_URL . '/admin/usuarios'],
            ['🏷️', 'Categorías',          BASE_URL . '/admin/categorias'],
            ['📊', 'Estadísticas',         BASE_URL . '/admin/estadisticas'],
            ['👁️', 'Más Vistos',          BASE_URL . '/admin/mas-vistos'],
        ];
        foreach ($accesos as [$ico, $label, $url]) : ?>
            <a href="<?= $url ?>"
               class="flex items-center gap-3 p-3 rounded-lg border border-gray-200
                      hover:bg-[#1b4785] hover:text-white transition group text-sm font-medium">
                <span class="text-xl"><?= $ico ?></span>
                <span><?= htmlspecialchars($label) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
