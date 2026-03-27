<?php
// app/Views/dashboard/student.php

require_once __DIR__ . "/../../Models/LibrosModel.php";
require_once __DIR__ . "/../../Models/CategoriaModel.php";

$libroModel = new LibrosModel();
$categoriaModel = new CategoriaModel();

// Cargar listas
$libros = $libroModel->getAll();
$categorias = $categoriaModel->getCategoriasPorTipo(1);

// CONTADORES DINÁMICOS
$contadorLibros = $libroModel->contarLibros();
$contadorTesis = $libroModel->contarTesis();
$contadorPublicaciones = $libroModel->contarPublicaciones();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - Biblioteca Dra. Mery Navas</title>

    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logoSuperarse.png" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    

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
</head>

<body class="bg-gray-50">

<!--------------------------------- NAVBAR ------------------------------------>

<nav class="bg-gradient-to-r from-superarse-morado-oscuro to-superarse-morado-medio text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

<!------------------------ IZQUIERDA: Logo + Título --------------------------->

        <div class="flex items-center gap-3 min-w-0">
            <img src="<?= BASE_URL ?>/assets/img/LOGO SUPERARSE PNG-02.png"
                 alt="Logo Superarse"
                 class="h-8 sm:h-9 md:h-10 w-auto shrink-0">

            <h1 class="font-bold text-lg sm:text-xl md:text-2xl truncate">
                📚 Biblioteca Dra. Mery Navas
            </h1>
        </div>

<!--------------------- DERECHA: Usuario + Logout ----------------------------->

        <div class="flex items-center gap-4 sm:gap-6">

<!-------------------------- Usuario (oculto en móvil) ------------------------>

            <div class="text-right hidden sm:block leading-tight">
                <p class="font-semibold text-sm md:text-base">
                    <?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Usuario'); ?>
                </p>
                <p class="text-xs text-white/80">
                    👤 Solicitante
                </p>
            </div>

<!-------------------------------- Logout ------------------------------------->

            <a href="logout"
               class="bg-superarse-rosa hover:bg-red-600 px-3 sm:px-4 py-2 rounded-lg font-semibold text-sm transition whitespace-nowrap">
                🚪 <span class="hidden sm:inline">Cerrar Sesión</span>
            </a>

        </div>
    </div>
</nav>


<div class="max-w-7xl mx-auto px-4 py-8">

<!---------------------------------- BIENVENIDA ------------------------------->

<div class="bg-white rounded-xl shadow-lg p-8 mb-8 border-l-4 border-superarse-morado-medio">
    <h2 class="text-4xl font-bold text-superarse-morado-oscuro mb-2">
        ¡Bienvenido/a, <?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Usuario'); ?>! 👋
    </h2>
    <p class="text-gray-600 text-lg">
        Accede a los recursos, libros y materiales de la biblioteca digital Superarse.
    </p>
</div>

<!------------------------------- CARDS --------------------------------------->

<div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">

<!------------------------------------ LIBROS --------------------------------->

    <a href="<?= BASE_URL ?>/libros/catalogo"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-superarse-morado-oscuro text-center">
        <div class="text-4xl mb-4">📚</div>
        <h3 class="text-gray-600 font-semibold mb-2">Libros</h3>
        <p class="text-3xl font-bold text-superarse-morado-oscuro"><?= $contadorLibros ?></p>
    </a>

<!------------------------------------- TESIS --------------------------------->

    <a href="<?= BASE_URL ?>/tesis"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-superarse-morado-oscuro text-center">
        <div class="text-4xl mb-4">🎓</div>
        <h3 class="text-gray-600 font-semibold mb-2">Tesis</h3>
        <p class="text-3xl font-bold text-superarse-morado-oscuro"><?= $contadorTesis ?></p>
    </a>

<!------------------------------------ PUBLICACIONES -------------------------->

    <a href="<?= BASE_URL ?>/publicaciones"
        class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-superarse-morado-oscuro text-center">
        <div class="text-4xl mb-4">📂</div>
        <h3 class="text-gray-600 font-semibold mb-2">Publicaciones</h3>
        <p class="text-3xl font-bold text-blue-600"><?= $contadorPublicaciones ?></p>
    </a>

<!----------------------------------- MIS PRÉSTAMOS --------------------------->

    <div onclick="scrollToPrestamos()"
         class="cursor-pointer bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-superarse-morado-medio text-center">
        <div class="text-4xl mb-4">📖</div>
        <h3 class="text-gray-600 font-semibold mb-2">Mis Préstamos Activos</h3>
        <p class="text-3xl font-bold text-superarse-morado-medio" id="contadorActivos">0</p>
    </div>

<!------------------------------------- ATRASADOS ----------------------------->

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-superarse-rosa text-center">
        <div class="text-4xl mb-4">⏰</div>
        <h3 class="text-gray-600 font-semibold mb-2">Atrasados</h3>
        <p class="text-3xl font-bold text-red-600" id="contadorAtrasados">0</p>
    </div>

<!--------------------------------------- DEVUELTOS --------------------------->

    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition border-t-4 border-yellow-500 text-center">
        <div class="text-4xl mb-4">✅</div>
        <h3 class="text-gray-600 font-semibold mb-2">Devoluciones Totales</h3>
        <p class="text-3xl font-bold text-green-600" id="contadorDevueltos">0</p>
    </div>
</div>

<!----------------------------- NOTICIAS + MI INFO ---------------------------->

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

<!--------------------------------- NOTICIAS ---------------------------------->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h3 class="text-2xl font-bold text-superarse-morado-oscuro mb-6 flex items-center gap-2">
            📰 Noticias de la Biblioteca
        </h3>

        <div class="space-y-5 text-gray-700">

            <div class="border-l-4 border-superarse-morado-medio pl-4">
                <p class="font-semibold text-superarse-morado-oscuro">
                    📌 Nuevos libros agregados al catálogo
                </p>
                <p class="text-sm text-gray-600">
                    Se incorporaron 350 nuevos títulos en tecnología, administración y literatura.
                </p>
            </div>

            <div class="border-l-4 border-superarse-morado-medio pl-4">
                <p class="font-semibold text-superarse-morado-oscuro">
                    📚 Socialización de la plataforma
                </p>
                <p class="text-sm text-gray-600">
                    Aprende a buscar artículos y tesis. Fecha: 28 de enero.
                </p>
            </div>

            <div class="border-l-4 border-superarse-morado-medio pl-4">
                <p class="font-semibold text-superarse-morado-oscuro">
                    ⏰ Horario actualizado
                </p>
                <p class="text-sm text-gray-600">
                    Lunes a viernes de 08:00 a 17:00.
                </p>
            </div>

        </div>
    </div>

<!----------------------------------------- MI INFO --------------------------->

    <div class="bg-white rounded-xl shadow-lg p-8 col-span-2">
        <h3 class="text-2xl font-bold text-superarse-morado-oscuro mb-6">
            📋 Mi Información
        </h3>

        <div class="space-y-4">

            <div class="border-b pb-4">
                <p class="text-sm text-gray-600">Correo Electrónico</p>
                <p class="font-semibold text-gray-800">
                    <?= htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?>
                </p>
            </div>

            <div class="border-b pb-4">
                <p class="text-sm text-gray-600">Cédula</p>
                <p class="font-semibold text-gray-800">
                    <?= htmlspecialchars($_SESSION['cedula'] ?? 'N/A'); ?>
                </p>
            </div>

            <?php if (!empty($_SESSION['carrera'])): ?>
                <div class="border-b pb-4">
                    <p class="text-sm text-gray-600">Carrera</p>
                    <p class="font-semibold text-gray-800">
                        <?= htmlspecialchars($_SESSION['carrera']); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div>
                <p class="text-sm text-gray-600">Estado de Cuenta</p>
                <p class="font-semibold text-green-600">✅ Activa</p>
            </div>

        </div>
    </div>

</div>

<!----------------------------- MIS PRÉSTAMOS --------------------------------->

<div id="prestamosActivos" class="bg-white rounded-xl shadow-lg p-8 mb-8">

    <h3 class="text-2xl font-bold text-superarse-morado-oscuro mb-6 flex items-center gap-2">
        📋 Mis Préstamos Activos
    </h3>

    <div class="overflow-y-auto overflow-x-auto max-h-72 border rounded-lg">
        <table class="w-full text-sm" id="tablaMisPrestamos">

            <thead class="bg-gray-100 border-b-2 border-superarse-morado-oscuro sticky top-0">
                <tr>
                    <th class="px-4 py-3 text-left">Libro</th>
                    <th class="px-4 py-3 text-left">Fecha Solicitud</th>
                    <th class="px-4 py-3 text-left">Fecha Respuesta</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td colspan="5" class="text-center py-8">Cargando...</td>
                </tr>
            </tbody>

        </table>
    </div>
</div>
</div>

<!--------------------------------JS------------------------------------------->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>const BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/js/student/student.js" defer></script>

<!------------------------------ FOOTER --------------------------------------->

   <?php include(__DIR__ . '/../footer.php'); ?>
</body>
</html>
    