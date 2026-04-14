<?php
/**
 * app/Views/admin/libros.php
 * Módulo: Catálogo de Libros
 */
$pageTitle    = 'Catálogo de Libros';
$activeModule = 'libros';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

    <!-- Encabezado -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785]">📚 Catálogo de Libros</h3>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 text-sm">Buscar:</span>
                <input type="text" id="buscarLibro"
                       placeholder="🔍 Buscar título..."
                       class="input w-full sm:w-48 px-3 py-2 text-sm">
            </div>
            <button onclick="openModalLibro()"
                    class="bg-[#1b4785] text-white px-5 py-2 rounded-lg text-sm
                           hover:bg-[#479990] transition">
                ➕ Nuevo Libro
            </button>
            <button onclick="window.location.href = BASE_URL + '/reporte/catalogo-libros'"
                    class="flex items-center gap-2 bg-green-600 text-white
                           px-5 py-2 rounded-xl text-sm font-semibold
                           shadow-md hover:bg-blue-400 transition">
                📥 Exportar Catálogo
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto overflow-y-auto max-h-[60vh] border rounded-xl -mx-2 sm:mx-0">
        <table class="min-w-[1000px] w-full text-sm">
            <thead class="bg-gray-100 border-b-2 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3">Código</th>
                    <th class="px-4 py-3">Portada</th>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Autor</th>
                    <th class="px-4 py-3">Edición</th>
                    <th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3">Año</th>
                    <th class="px-4 py-3">Ejemplares</th>
                    <th class="px-4 py-3">Stock</th>
                    <th class="px-4 py-3">Editorial</th>
                    <th class="px-4 py-3">Ubicación</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaLibros">
                <tr>
                    <td colspan="13" class="text-center py-6 text-gray-400">Cargando libros…</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- =========================================================
     MODAL LIBRO
========================================================== -->
<div id="modalLibro"
    class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm
          flex items-start sm:items-center justify-center z-50 p-3 sm:p-4 overflow-y-auto">
    <div id="modalCard"
        class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-lg max-h-[85vh] overflow-y-auto mt-4 sm:mt-0
                scale-90 opacity-0 transition-all duration-200">

        <h3 id="modalLibroTitle" class="text-xl font-bold mb-3">Nuevo Libro</h3>

        <form id="formLibro" onsubmit="return false;" enctype="multipart/form-data">
            <input type="hidden" id="libro_id">

            <label>Código Institucional</label>
            <input id="libro_codigo" class="input">

            <label>Portada <span class="text-red-600">*</span></label>
            <input id="libro_portada" type="file" accept="image/*" class="input">
            <p class="text-xs text-gray-500 mt-1 mb-2">Obligatoria al crear. Formatos: JPG, PNG, WEBP o GIF. Máx. 5 MB. Se compress a 600×900 px.</p>
            <div id="libro_portada_preview_wrap" class="hidden mb-3">
                <img id="libro_portada_preview" src="" alt="Vista previa de portada" class="w-20 h-28 object-cover rounded border border-gray-200 shadow-sm">
            </div>

            <label>Título</label>
            <input id="libro_titulo" class="input">

            <label>Autor</label>
            <input id="libro_autor" class="input">

            <label>Edición</label>
            <input id="libro_edicion" class="input">

            <label>Editorial</label>
            <input id="libro_revista" class="input">

            <label>Código de Barras</label>
            <input id="libro_codigo_barra" class="input">

            <label>Categoría</label>
            <select id="libro_categoria" class="input"></select>

            <label>Año</label>
            <input id="libro_anio" type="number" class="input">

            <label>Número de ejemplares</label>
            <input id="libro_numero_ejemplares" type="number" class="input">

            <label>Stock</label>
            <p class="text-red-600 font-bold text-xs mt-0 mb-1">
                * Debe ser igual al número de ejemplares.
            </p>
            <input id="libro_stock" type="number" class="input">

            <label>Ubicación</label>
            <input id="libro_ubicacion" class="input">

            <label>Descripción</label>
            <textarea id="libro_descripcion" class="input"></textarea>

            <div id="campoEstadoLibro" class="hidden mb-4">
                <label>Estado</label>
                <select id="libro_estado" class="input">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 mt-3">
                <button onclick="closeModalLibro()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                    Cancelar
                </button>
                <button onclick="submitLibro()"
                        class="px-4 py-2 bg-[#1b4785] text-white rounded hover:bg-[#479990] transition">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/libros.js"></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
