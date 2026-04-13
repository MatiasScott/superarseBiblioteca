<?php
/**
 * app/Views/admin/categorias.php
 * Módulo: Categorías
 */
$pageTitle    = 'Categorías';
$activeModule = 'categorias';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

    <!-- Encabezado -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785]">🏷️ Categorías</h3>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 text-sm">Buscar:</span>
                <input type="text" id="buscarCategoria"
                       placeholder="🔍 Buscar por nombre..."
                       class="input w-full sm:w-56 px-3 py-2 text-sm border rounded">
            </div>
            <button onclick="abrirModalCategoria()"
                    class="bg-[#1b4785] hover:bg-[#479990] text-white
                           px-5 py-2 rounded-lg text-sm transition">
                ➕ Nueva Categoría
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto overflow-y-auto max-h-[60vh] border rounded-xl -mx-2 sm:mx-0">
        <table class="min-w-[600px] w-full text-sm">
            <thead class="bg-gray-100 border-b-2 border-[#1b4785] sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 text-left font-bold">Nombre</th>
                    <th class="px-4 py-3 text-left font-bold">Tipo</th>
                    <th class="px-4 py-3 text-left font-bold">Estado</th>
                    <th class="px-4 py-3 text-left font-bold">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaCategorias">
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-400">Cargando categorías…</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- =========================================================
     MODAL CATEGORÍA
========================================================== -->
<div id="modalCategoria"
     class="hidden fixed inset-0 bg-black/50 flex justify-center items-center z-[9999]">
    <div class="bg-white p-4 sm:p-6 rounded-xl w-[calc(100vw-1.5rem)] max-w-md relative shadow-xl mx-3">

        <button onclick="cerrarModalCategoria()"
                class="absolute top-2 right-3 text-gray-500 hover:text-gray-700 text-xl">
            &times;
        </button>

        <h3 id="tituloModal" class="text-xl font-bold mb-4">Nueva Categoría</h3>

        <input type="hidden" id="categoriaId">

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">Nombre</label>
            <input id="categoriaNombre" type="text"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">Tipo</label>
            <select id="categoriaTipo" class="w-full border rounded px-3 py-2">
                <option value="">Seleccione un tipo…</option>
            </select>
        </div>

        <div class="mb-4 hidden" id="campoEstadoCategoria">
            <label class="block text-gray-700 font-medium mb-1">Estado</label>
            <select id="categoriaEstado" class="w-full border rounded px-3 py-2">
                <option value="ACTIVO">ACTIVO</option>
                <option value="INACTIVO">INACTIVO</option>
            </select>
        </div>

        <div class="flex justify-end gap-2">
            <button onclick="cerrarModalCategoria()"
                    class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition">
                Cancelar
            </button>
            <button onclick="guardarCategoria()"
                    class="px-4 py-2 rounded bg-[#1b4785] text-white hover:bg-[#479990] transition">
                Guardar
            </button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/categorias.js" defer></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
