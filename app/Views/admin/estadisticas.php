<?php
/**
 * app/Views/admin/estadisticas.php
 * Módulo: Estadísticas
 */
$pageTitle    = 'Estadísticas';
$activeModule = 'estadisticas';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-8">

    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <h2 class="text-2xl sm:text-3xl font-bold text-[#1b4785]">📊 Estadísticas Completas</h2>
        <button onclick="descargarExcelEstadisticas()"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
            📥 Descargar Excel
        </button>
    </div>

    <!-- Contadores -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8" id="contadoresEstadisticas">
        <p class="text-center text-gray-400 col-span-6 py-6">Cargando estadísticas…</p>
    </div>

    <!-- Gráficos -->
    <canvas id="graficoPrestamos" class="mb-8" height="100"></canvas>
    <canvas id="graficoItems"     class="mb-8" height="100"></canvas>
    <canvas id="graficoUsuarios"  class="mb-8" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>/js/admin/estadisticas.js" defer></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
