<?php
$pageTitle = 'Tesis';
$activeStudentModule = 'tesis';
include __DIR__ . '/../layouts/student_header.php';
?>

<div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 mb-6 border-l-4 border-[#479990]">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
        <p class="text-xs sm:text-sm text-gray-500">
            Inicio / Panel estudiante / Tesis
        </p>
        <a href="<?= BASE_URL ?>/student/dashboard"
           class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-sm font-medium border border-[#1b4785] text-[#1b4785] hover:bg-[#1b4785] hover:text-white transition">
            Volver al panel
        </a>
    </div>
    <h2 class="text-2xl sm:text-3xl font-bold text-[#1b4785] mb-2">🎓 Repositorio de Tesis</h2>
    <p class="text-gray-600 text-sm sm:text-base">
        Consulta tesis por tema, carrera o autor y accede al documento completo desde esta vista.
    </p>
</div>

<div class="max-w-3xl mx-auto px-1 sm:px-4 mt-2 sm:mt-4 mb-6">
    <input type="text" id="buscadorTesis" placeholder="🔍 Buscar por título, autor, carrera o año..."
           class="w-full p-3 border rounded-lg shadow-sm focus:ring-2 focus:ring-superarse-morado-medio focus:outline-none">
    <p id="contadorResultadosTesis" class="mt-2 text-xs sm:text-sm text-gray-500">
        Mostrando <?= count($tesis ?? []) ?> resultados
    </p>
</div>

<div id="gridTesis"
     class="px-1 sm:px-4 py-4 sm:py-6 min-h-[60vh] grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($tesis as $t): ?>
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition cursor-pointer tesis-card flex flex-col"
             data-titulo="<?= strtolower(htmlspecialchars($t['titulo'])) ?>"
             data-autor="<?= strtolower(htmlspecialchars($t['autor'])) ?>"
             data-carrera="<?= strtolower(htmlspecialchars($t['categoria_nombre'])) ?>"
             data-anio="<?= strtolower((string)($t['anio'] ?? '')) ?>"
             onclick="abrirModal(<?= $t['id'] ?>)">
            <img src="<?= htmlspecialchars($t['portada']) ?>" alt="<?= htmlspecialchars($t['codigo']) ?>" class="w-full h-52 sm:h-56 md:h-60 object-cover">
            <div class="p-4 flex flex-col flex-grow">
                <h3 class="font-bold text-base sm:text-lg text-superarse-morado-oscuro line-clamp-2"><?= htmlspecialchars($t['titulo']) ?></h3>
                <p class="text-gray-600 text-xs sm:text-sm mt-1">Autor: <?= htmlspecialchars($t['autor']) ?></p>
                <p class="text-gray-600 text-xs sm:text-sm">Tutor: <?= htmlspecialchars($t['tutor']) ?></p>
                <p class="text-gray-600 text-xs sm:text-sm">Carrera: <?= htmlspecialchars($t['categoria_nombre']) ?></p>
                <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm mt-3">
                    <div class="bg-blue-500 text-white rounded-lg py-1 text-center">Año: <?= htmlspecialchars($t['anio'] ?? '-') ?></div>
                    <div class="bg-green-500 text-white rounded-lg py-1 text-center">Código: <?= htmlspecialchars($t['codigo']) ?></div>
                    <div class="col-span-2 bg-orange-500 text-white rounded-lg py-1 text-center mt-1">👁️ Visitas: <span id="VisitasTesis-<?= $t['id'] ?>"><?= htmlspecialchars($t['visitas']) ?></span></div>
                    <div class="col-span-2 grid grid-cols-2 gap-2 mt-1">
                        <button type="button"
                                class="bg-[#1b4785] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaTesis('apa7', <?= $t['id'] ?>, event)">
                            Cita APA 7
                        </button>
                        <button type="button"
                                class="bg-[#164c7e] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaTesis('ieee', <?= $t['id'] ?>, event)">
                            Cita IEEE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="noResultsMessageTesis" class="px-4 py-12 text-center text-gray-500 hidden">
    <p class="text-lg">No se encontraron tesis que coincidan con la búsqueda.</p>
</div>

<div id="modalTesis" class="fixed inset-0 bg-black/60 hidden flex items-start sm:items-center justify-center z-50 px-3 sm:px-4 py-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg md:max-w-3xl p-4 sm:p-6 relative max-h-[90vh] overflow-y-auto mt-4 sm:mt-0">
        <button onclick="cerrarModalTesis()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-900 text-2xl font-bold">×</button>
        <div id="modalContent"></div>
    </div>
</div>

<script>
window.APP = {
    BASE_URL: "<?= rtrim(BASE_URL, '/') ?>",
    tesis: <?= json_encode($tesis ?? []) ?>
};
</script>
<script src="<?= BASE_URL ?>/js/estudiantes/tesis.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/../layouts/student_footer.php'; ?>
