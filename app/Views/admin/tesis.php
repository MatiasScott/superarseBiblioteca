<?php
/**
 * app/Views/admin/tesis.php
 * Módulo: Repositorio de Tesis
 */
$pageTitle    = 'Repositorio de Tesis';
$activeModule = 'tesis';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

    <!-- Encabezado -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785]">🎓 Repositorio de Tesis</h3>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 text-sm">Buscar:</span>
                <input type="text" id="buscarTesis"
                       placeholder="🔍 Buscar título..."
                       class="input w-full sm:w-48 px-3 py-2 text-sm">
            </div>
            <button onclick="openModalTesis()"
                    class="bg-[#1b4785] text-white px-5 py-2 rounded-lg text-sm
                           hover:bg-[#479990] transition">
                ➕ Nueva Tesis
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto overflow-y-auto max-h-[60vh] border rounded-xl -mx-2 sm:mx-0">
        <table class="min-w-[1100px] w-full text-sm">
            <thead class="bg-gray-100 border-b-2 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3">Código</th>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Portada</th>
                    <th class="px-4 py-3">Autor</th>
                    <th class="px-4 py-3">Tutor</th>
                    <th class="px-4 py-3">Instituto</th>
                    <th class="px-4 py-3">Carrera/Categoría</th>
                    <th class="px-4 py-3">Año</th>
                    <th class="px-4 py-3">Palabras Clave</th>
                    <th class="px-4 py-3">PDF</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaTesis">
                <tr>
                    <td colspan="12" class="text-center py-6 text-gray-400">Cargando tesis…</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- =========================================================
     MODAL TESIS
========================================================== -->
<div id="modalTesis"
    class="hidden fixed inset-0 bg-black/40 flex items-start sm:items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-lg max-h-[85vh] overflow-y-auto shadow-xl mt-4 sm:mt-0">

        <h3 id="modalTesisTitle" class="text-xl font-bold mb-3">Nueva Tesis</h3>

        <form id="formTesis" onsubmit="return false;">
            <input type="hidden" id="tesis_id">

            <label class="block font-medium">Título <span class="text-red-600">*</span></label>
            <input id="tesis_titulo" class="input">

            <label class="block font-medium">Portada (URL)</label>
            <input id="tesis_portada" class="input">

            <label class="block font-medium">Autor <span class="text-red-600">*</span></label>
            <input id="tesis_autor" class="input">

            <label class="block font-medium">Tutor</label>
            <input id="tesis_tutor" class="input">

            <label class="block font-medium">Instituto</label>
            <input id="tesis_universidad" class="input">

            <label class="block font-medium">Carrera/Categoría <span class="text-red-600">*</span></label>
            <select id="tesis_categoria" class="input"></select>

            <label class="block font-medium">Año de creación</label>
            <input id="tesis_anio" type="number" class="input">

            <label class="block font-medium">Palabras Clave / Descripción</label>
            <textarea id="tesis_descripcion" class="input"></textarea>

            <label class="block font-medium">Link PDF</label>
            <input id="tesis_link" class="input">

            <div id="campoEstadoTesis" class="hidden mb-4">
                <label class="block font-medium">Estado</label>
                <select id="tesis_estado" class="input">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
            </div>

            <div class="flex justify-between items-center gap-2 mt-3">
                <p class="text-xs text-gray-500">
                    Campos <span class="text-red-600">*</span> obligatorios
                </p>
                <div class="flex gap-2">
                    <button type="button" onclick="closeModalTesis()"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                        Cancelar
                    </button>
                    <button type="button" onclick="onSaveTesis()"
                            class="px-4 py-2 bg-[#1b4785] text-white rounded hover:bg-[#479990] transition">
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/tesis.js"></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
