<?php
$pageTitle = 'Publicaciones';
$activeStudentModule = 'publicaciones';
include __DIR__ . '/../layouts/student_header.php';
?>

<div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 mb-6 border-l-4 border-[#164c7e]">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
        <p class="text-xs sm:text-sm text-gray-500">
            Inicio / Panel estudiante / Publicaciones
        </p>
        <a href="<?= BASE_URL ?>/student/dashboard"
           class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-sm font-medium border border-[#1b4785] text-[#1b4785] hover:bg-[#1b4785] hover:text-white transition">
            Volver al panel
        </a>
    </div>
    <h2 class="text-2xl sm:text-3xl font-bold text-[#1b4785] mb-2">📂 Publicaciones Académicas</h2>
    <p class="text-gray-600 text-sm sm:text-base">
        Revisa artículos y publicaciones por título, autor o año dentro de tu espacio de estudiante.
    </p>
</div>

<div class="max-w-3xl mx-auto px-1 sm:px-4 mt-2 sm:mt-4 mb-6">
    <input id="buscador" type="text" placeholder="🔍 Buscar por título, autor o año..."
           class="w-full p-3 border rounded-lg shadow-sm focus:ring-2 focus:ring-superarse-morado-medio focus:outline-none"
           onkeyup="filtrarPublicaciones()">
    <p id="contadorResultadosPublicaciones" class="mt-2 text-xs sm:text-sm text-gray-500">
        Mostrando <?= count($publicaciones ?? []) ?> resultados
    </p>
</div>

<div id="gridPublicaciones"
     class="px-1 sm:px-4 py-4 sm:py-6 min-h-[60vh] grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($publicaciones as $p): ?>
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition cursor-pointer flex flex-col"
             onclick="abrirModal(<?= $p['id'] ?>)">
            <img src="<?= htmlspecialchars($p['portada']) ?>" alt="<?= htmlspecialchars($p['titulo']) ?>" class="w-full h-52 sm:h-56 md:h-60 object-cover">
            <div class="p-4 flex flex-col flex-grow">
                <h3 class="font-bold text-base sm:text-lg text-superarse-morado-oscuro line-clamp-2">Título: <?= htmlspecialchars($p['titulo']) ?></h3>
                <p class="text-gray-600 text-xs sm:text-sm mt-1">Autor: <?= htmlspecialchars($p['autor']) ?></p>
                <p class="text-gray-500 text-xs sm:text-sm mb-2">Categoría: <?= htmlspecialchars($p['categoria_nombre']) ?></p>
                <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm mt-3">
                    <div class="bg-blue-500 text-white rounded-lg py-1 text-center">Año: <?= htmlspecialchars($p['anio'] ?? '-') ?></div>
                    <div class="bg-green-500 text-white rounded-lg py-1 text-center">Revista: <?= htmlspecialchars($p['revista']) ?></div>
                    <div class="col-span-2 bg-blue-500 text-white rounded-lg py-1 text-center">Código: <?= htmlspecialchars($p['codigo']) ?></div>
                    <div class="col-span-2 bg-orange-500 text-white rounded-lg py-1 text-center mt-1">👁️ Visitas: <span id="visitasPublicacion-<?= $p['id'] ?>"><?= htmlspecialchars($p['visitas']) ?></span></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="noResultsMessagePublicaciones" class="px-4 py-12 text-center text-gray-500 hidden">
    <p class="text-lg">No se encontraron publicaciones que coincidan con la búsqueda.</p>
</div>

<div id="modalPublicacion" class="fixed inset-0 bg-black/60 hidden flex items-start sm:items-center justify-center z-50 px-3 sm:px-4 py-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg md:max-w-3xl p-4 sm:p-6 relative max-h-[90vh] overflow-y-auto mt-4 sm:mt-0">
        <button onclick="cerrarModalPublicacion()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-900 text-2xl font-bold">×</button>
        <div id="modalContent"></div>
    </div>
</div>

<script>
window.APP = window.APP || {};
window.APP.BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
window.APP.publicaciones = <?= json_encode($publicaciones ?? []) ?>;
</script>
<script src="<?= BASE_URL ?>/js/estudiantes/publicaciones.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/../layouts/student_footer.php'; ?>
