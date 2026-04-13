<?php
$pageTitle = 'Mis Préstamos';
$activeStudentModule = 'prestamos';
include __DIR__ . '/../layouts/student_header.php';
?>

<div class="bg-white rounded-xl shadow-lg p-6 sm:p-8 mb-8">
    <h3 class="text-2xl font-bold text-[#1b4785] mb-6">📋 Mis Préstamos</h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-50 border rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Activos</p>
            <p id="contadorActivos" class="text-2xl sm:text-3xl font-bold text-[#479990]">0</p>
        </div>
        <div class="bg-gray-50 border rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Atrasados</p>
            <p id="contadorAtrasados" class="text-2xl sm:text-3xl font-bold text-red-600">0</p>
        </div>
        <div class="bg-gray-50 border rounded-lg p-4 text-center">
            <p class="text-sm text-gray-500">Devueltos</p>
            <p id="contadorDevueltos" class="text-2xl sm:text-3xl font-bold text-green-600">0</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 mb-6 items-end">
        <div>
            <label for="filtroEstadoStudent" class="block text-sm font-semibold text-gray-700 mb-1">Estado</label>
            <select id="filtroEstadoStudent" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">Todos</option>
                <option value="PENDIENTE">Pendiente</option>
                <option value="APROBADA">Aprobada</option>
                <option value="ENTREGADO">Entregado</option>
                <option value="RECHAZADA">Rechazada</option>
                <option value="RETRASADO">Retrasado</option>
            </select>
        </div>

        <div>
            <label for="filtroDesdeStudent" class="block text-sm font-semibold text-gray-700 mb-1">Desde</label>
            <input type="date" id="filtroDesdeStudent" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label for="filtroHastaStudent" class="block text-sm font-semibold text-gray-700 mb-1">Hasta</label>
            <input type="date" id="filtroHastaStudent" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <button id="btnAplicarFiltroStudent"
                class="bg-[#1b4785] text-white px-4 py-2 rounded-lg hover:bg-[#479990] transition text-sm font-semibold">
            Aplicar filtros
        </button>

        <button id="btnLimpiarFiltroStudent"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition text-sm font-semibold">
            Limpiar
        </button>
    </div>

    <div class="overflow-y-auto overflow-x-auto max-h-[60vh] border rounded-lg -mx-2 sm:mx-0">
        <table class="w-full text-sm" id="tablaMisPrestamos">
            <thead class="bg-gray-100 border-b-2 border-[#1b4785] sticky top-0">
                <tr>
                    <th class="px-4 py-3 text-left">Libro</th>
                    <th class="px-4 py-3 text-left">Fecha Solicitud</th>
                    <th class="px-4 py-3 text-left">Fecha Respuesta</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-500">Cargando...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mt-4">
        <p id="studentPaginationInfo" class="text-sm text-gray-500">Mostrando 0 resultados</p>
        <div class="flex gap-2">
            <button id="btnPrevStudent" class="px-3 py-1.5 rounded border text-sm text-gray-700 hover:bg-gray-50" disabled>Anterior</button>
            <button id="btnNextStudent" class="px-3 py-1.5 rounded border text-sm text-gray-700 hover:bg-gray-50" disabled>Siguiente</button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/student/prestamos.js" defer></script>

<?php include __DIR__ . '/../layouts/student_footer.php'; ?>
