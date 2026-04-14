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
    class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm
          flex items-start sm:items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-xl mt-4 sm:mt-0">

        <!-- Encabezado sticky -->
        <div class="sticky top-0 bg-white z-10 flex items-center justify-between px-6 py-4 border-b rounded-t-xl">
            <h3 id="modalTesisTitle" class="text-lg font-bold text-[#1b4785]">Nueva Tesis</h3>
            <button type="button" onclick="closeModalTesis()"
                    class="text-gray-400 hover:text-red-500 transition rounded-full p-1 hover:bg-gray-100"
                    aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Formulario -->
        <form id="formTesis" class="px-6 py-5" onsubmit="return false;" enctype="multipart/form-data">
            <input type="hidden" id="tesis_id">

            <label class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-600">*</span></label>
            <input id="tesis_titulo" class="input">

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Portada <span class="text-red-600">*</span></label>
            <input id="tesis_portada" type="file" accept="image/*" class="input">
            <p class="text-xs text-gray-500 mt-1 mb-2">Obligatoria al crear. Formatos: JPG, PNG, WEBP o GIF. Máx. 5 MB. Se redimensiona a 600×900 px.</p>
            <div id="tesis_portada_preview_wrap" class="hidden mb-3">
                <img id="tesis_portada_preview" src="" alt="Vista previa de portada"
                     class="w-20 h-28 object-cover rounded border border-gray-200 shadow-sm">
            </div>

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Autor <span class="text-red-600">*</span></label>
            <input id="tesis_autor" class="input">

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Tutor</label>
            <input id="tesis_tutor" class="input">

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Instituto</label>
            <input id="tesis_universidad" class="input">

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Carrera / Categoría <span class="text-red-600">*</span></label>
            <select id="tesis_categoria" class="input"></select>

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Año de creación</label>
            <input id="tesis_anio" type="number" class="input">

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Palabras Clave / Descripción</label>
            <textarea id="tesis_descripcion" class="input"></textarea>

            <label class="block text-sm font-medium text-gray-700 mt-4 mb-1">Archivo PDF</label>
            <input id="tesis_link" type="file" accept=".pdf,application/pdf" class="input">
            <p class="text-xs text-gray-500 mt-1 mb-2">Máx. 50 MB. Si no seleccionas archivo se conserva el PDF actual.</p>
            <div id="tesis_pdf_actual_wrap" class="hidden mb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <a id="tesis_pdf_actual_link" href="#" target="_blank" rel="noopener"
                   class="text-[#1b4785] underline text-sm truncate max-w-xs">Ver PDF actual</a>
            </div>

            <div id="campoEstadoTesis" class="hidden mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select id="tesis_estado" class="input">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
            </div>

            <div class="flex justify-between items-center gap-2 mt-6 pt-4 border-t">
                <p class="text-xs text-gray-500">Campos <span class="text-red-600">*</span> obligatorios</p>
                <div class="flex gap-2">
                    <button type="button" onclick="closeModalTesis()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="button" onclick="onSaveTesis()"
                            class="px-4 py-2 bg-[#1b4785] text-white rounded-lg hover:bg-[#479990] transition text-sm font-medium">
                        Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/tesis.js"></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
