<?php
$pageTitle = 'Mi Perfil';
$activeStudentModule = 'perfil';
include __DIR__ . '/../layouts/student_header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-6 sm:p-8">
        <h3 class="text-2xl font-bold text-[#1b4785] mb-6">👤 Mi Perfil</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Nombres completos</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'N/A') ?></p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Correo</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['email'] ?? 'N/A') ?></p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Cédula</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['cedula'] ?? 'N/A') ?></p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Carrera</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['carrera'] ?? 'N/A') ?></p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Curso</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['curso'] ?? 'N/A') ?></p>
            </div>
            <div class="border rounded-lg p-4">
                <p class="text-sm text-gray-500">Estado de cuenta</p>
                <p class="font-semibold text-green-600">✅ Activa</p>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/student/perfil.js" defer></script>

<?php include __DIR__ . '/../layouts/student_footer.php'; ?>
