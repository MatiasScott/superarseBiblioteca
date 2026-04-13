<?php
/**
 * app/Views/admin/publicaciones.php
 * Módulo: Catálogo de Publicaciones
 */
$pageTitle    = 'Catálogo de Publicaciones';
$activeModule = 'publicaciones';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

    <!-- Encabezado -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785]">📂 Catálogo de Publicaciones</h3>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 text-sm">Buscar:</span>
                <input type="text" id="buscarPublicacion"
                       placeholder="🔍 Buscar título..."
                       class="input w-full sm:w-48 px-3 py-2 text-sm">
            </div>
            <button onclick="openModal()"
                    class="bg-[#1b4785] text-white px-5 py-2 rounded-lg text-sm
                           hover:bg-[#479990] transition">
                ➕ Nueva Publicación
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto overflow-y-auto max-h-[60vh] border rounded-xl -mx-2 sm:mx-0">
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
                    <td colspan="11" class="text-center py-6 text-gray-400">Cargando publicaciones…</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- =========================================================
     MODAL PUBLICACIÓN
========================================================== -->
<div id="modalPub"
    class="hidden fixed inset-0 bg-black/40 flex items-start sm:items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-lg max-h-[85vh] overflow-y-auto shadow-xl mt-4 sm:mt-0">

        <h3 id="modalTitle" class="text-xl font-bold mb-3">Nueva Publicación</h3>

        <form id="formPub" onsubmit="return false;">
            <input type="hidden" id="id" name="id">

            <label class="block font-medium mb-1">Portada (URL de imagen)</label>
            <input id="portada" name="portada" class="input">

            <label class="block font-medium mb-1">Título</label>
            <input id="titulo" name="titulo" class="input">

            <label class="block font-medium mb-1">Autor</label>
            <input id="autor" name="autor" class="input">

            <label class="block font-medium mb-1">Revista</label>
            <input id="revista" name="revista" class="input">

            <label class="block font-medium mb-1">Año</label>
            <input id="anio" name="anio" type="number" class="input">

            <label class="block font-medium mb-1">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="input"></textarea>

            <label class="block font-medium mb-1">Categoría</label>
            <select id="categoria_id" name="categoria_id" class="input">
                <option value="">Seleccione una categoría</option>
            </select>

            <label class="block font-medium mb-1">Link PDF</label>
            <input id="link_archivo" name="link_archivo" class="input">

            <div id="campoEstadoPub" class="hidden mb-4">
                <label class="block font-medium mb-1">Estado</label>
                <select id="pub_estado" class="input">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 mt-3">
                <button onclick="closeModal()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                    Cancelar
                </button>
                <button onclick="submitPub()"
                        class="px-4 py-2 bg-[#1b4785] text-white rounded hover:bg-[#479990] transition">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/publicaciones.js"></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
