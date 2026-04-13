<?php
require_once __DIR__ . '/../../Models/LibrosModel.php';
require_once __DIR__ . '/../../Models/UserModel.php';

$libroModel = new LibrosModel();
$userModel = new UserModel();

$contadorLibros = $libroModel->contarLibros();
$contadorTesis = $libroModel->contarTesis();
$contadorPublicaciones = $libroModel->contarPublicaciones();

$pageTitle = 'Inicio del Estudiante';
$activeStudentModule = 'dashboard';
include __DIR__ . '/../layouts/student_header.php';
?>

<div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-8 border-l-4 border-[#479990]">
    <h2 class="text-3xl sm:text-4xl font-bold text-[#1b4785] mb-2">
        ¡Bienvenido/a, <?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Usuario') ?>! 👋
    </h2>
    <p class="text-gray-600 text-lg">Accede a recursos, materiales y seguimiento de préstamos.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <a href="<?= BASE_URL ?>/student/libros" class="bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition border-t-4 border-[#1b4785] text-center">
        <div class="text-3xl sm:text-4xl mb-2">📚</div>
        <h3 class="text-gray-600 font-semibold mb-1">Libros</h3>
        <p class="text-2xl sm:text-3xl font-bold text-[#1b4785]"><?= $contadorLibros ?></p>
    </a>

    <a href="<?= BASE_URL ?>/student/tesis" class="bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition border-t-4 border-[#1b4785] text-center">
        <div class="text-3xl sm:text-4xl mb-2">🎓</div>
        <h3 class="text-gray-600 font-semibold mb-1">Tesis</h3>
        <p class="text-2xl sm:text-3xl font-bold text-[#1b4785]"><?= $contadorTesis ?></p>
    </a>

    <a href="<?= BASE_URL ?>/student/publicaciones" class="bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition border-t-4 border-[#1b4785] text-center">
        <div class="text-3xl sm:text-4xl mb-2">📂</div>
        <h3 class="text-gray-600 font-semibold mb-1">Publicaciones</h3>
        <p class="text-2xl sm:text-3xl font-bold text-blue-600"><?= $contadorPublicaciones ?></p>
    </a>

    <a href="<?= BASE_URL ?>/student/prestamos" class="bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition border-t-4 border-[#479990] text-center">
        <div class="text-3xl sm:text-4xl mb-2">📖</div>
        <h3 class="text-gray-600 font-semibold mb-1">Mis Préstamos Activos</h3>
        <p class="text-2xl sm:text-3xl font-bold text-[#479990]" id="contadorActivos">0</p>
    </a>

    <a href="<?= BASE_URL ?>/student/prestamos" class="bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition border-t-4 border-red-500 text-center">
        <div class="text-3xl sm:text-4xl mb-2">⏰</div>
        <h3 class="text-gray-600 font-semibold mb-1">Atrasados</h3>
        <p class="text-2xl sm:text-3xl font-bold text-red-600" id="contadorAtrasados">0</p>
    </a>

    <a href="<?= BASE_URL ?>/student/prestamos" class="bg-white rounded-lg shadow-md p-5 hover:shadow-lg transition border-t-4 border-yellow-500 text-center">
        <div class="text-3xl sm:text-4xl mb-2">✅</div>
        <h3 class="text-gray-600 font-semibold mb-1">Devueltos</h3>
        <p class="text-2xl sm:text-3xl font-bold text-green-600" id="contadorDevueltos">0</p>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-4">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-[#1b4785] mb-4">📰 Noticias de Biblioteca</h3>
        <div class="space-y-4 text-sm text-gray-700">
            <div class="border-l-4 border-[#479990] pl-3">
                <p class="font-semibold text-[#1b4785]">Nuevos libros agregados al catálogo</p>
                <p>Se incorporaron títulos en tecnología, administración y literatura.</p>
            </div>
            <div class="border-l-4 border-[#479990] pl-3">
                <p class="font-semibold text-[#1b4785]">Socialización de la plataforma</p>
                <p>Aprende a buscar artículos y tesis en la próxima jornada.</p>
            </div>
            <div class="border-l-4 border-[#479990] pl-3">
                <p class="font-semibold text-[#1b4785]">Horario actualizado</p>
                <p>Lunes a viernes de 08:00 a 17:00.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">
        <h3 class="text-xl font-bold text-[#1b4785] mb-4">📋 Mi Información</h3>
        <div class="space-y-3">
            <div class="border-b pb-3">
                <p class="text-sm text-gray-600">Correo Electrónico</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['email'] ?? 'N/A') ?></p>
            </div>
            <div class="border-b pb-3">
                <p class="text-sm text-gray-600">Cédula</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['cedula'] ?? 'N/A') ?></p>
            </div>
            <?php if (!empty($_SESSION['carrera'])): ?>
                <div class="border-b pb-3">
                    <p class="text-sm text-gray-600">Carrera</p>
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['carrera']) ?></p>
                </div>
            <?php endif; ?>
            <div>
                <p class="text-sm text-gray-600">Estado</p>
                <p class="font-semibold text-green-600">✅ Activa</p>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/student/dashboard.js" defer></script>

<?php include __DIR__ . '/../layouts/student_footer.php'; ?>
