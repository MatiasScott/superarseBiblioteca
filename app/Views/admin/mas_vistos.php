<?php
/**
 * app/Views/admin/mas_vistos.php
 * Módulo: Más Vistos
 */
$pageTitle    = 'Más Vistos';
$activeModule = 'mas-vistos';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

    <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785] mb-5">👁️ Más Vistos</h3>

    <!-- Filtro de rango -->
    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 mb-6 items-start sm:items-end">
        <div class="flex flex-col">
            <label class="font-semibold text-gray-700 text-sm mb-1">Desde</label>
            <input type="date" id="fechaInicio" class="border rounded px-3 py-2"
                   value="<?= date('Y-m-01') ?>">
        </div>

        <div class="flex flex-col">
            <label class="font-semibold text-gray-700 text-sm mb-1">Hasta</label>
            <input type="date" id="fechaFin" class="border rounded px-3 py-2"
                   value="<?= date('Y-m-d') ?>">
        </div>

        <button onclick="filtrarRango()"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-green-400 transition">
            🔍 Filtrar
        </button>
    </div>

    <!-- Sub-pestañas -->
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

    <!-- Gráfico: Libros -->
    <div id="librosChartContainer" class="sub-tab">
        <div class="flex justify-end mb-3">
            <button onclick="descargarExcel('libros')"
                    class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-green-400 transition">
                ⬇️ Excel Libros
            </button>
        </div>
        <div class="w-full h-[420px]">
            <canvas id="chartLibros"></canvas>
        </div>
    </div>

    <!-- Gráfico: Tesis -->
    <div id="tesisChartContainer" class="sub-tab hidden">
        <div class="flex justify-end mb-3">
            <button onclick="descargarExcel('tesis')"
                    class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-blue-400 transition">
                ⬇️ Excel Tesis
            </button>
        </div>
        <div class="w-full h-[420px]">
            <canvas id="chartTesis"></canvas>
        </div>
    </div>

    <!-- Gráfico: Publicaciones -->
    <div id="publicacionesChartContainer" class="sub-tab hidden">
        <div class="flex justify-end mb-3">
            <button onclick="descargarExcel('publicaciones')"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-red-500 transition">
                ⬇️ Excel Publicaciones
            </button>
        </div>
        <div class="w-full h-[420px]">
            <canvas id="chartPublicaciones"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>/js/admin/masVistos.js" defer></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
