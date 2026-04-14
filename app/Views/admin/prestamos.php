<?php
/**
 * app/Views/admin/prestamos.php
 * Módulo: Solicitudes de Préstamo
 */
$pageTitle    = 'Solicitudes de Préstamo';
$activeModule = 'prestamos';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

    <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785] mb-5">
        📖 Solicitudes de Préstamo
    </h3>

    <!-- Filtros -->
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 items-end">

        <div>
            <label for="filtroMes" class="block font-semibold text-gray-700 mb-1">
                Filtrar por mes
            </label>
            <input type="month" id="filtroMes"
                   class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label for="filtroEstado" class="block font-semibold text-gray-700 mb-1">
                Filtrar por estado
            </label>
            <select id="filtroEstado" class="w-full border rounded-lg px-3 py-2">
                <option value="">Todos los estados</option>
                <option value="PENDIENTE">Pendiente</option>
                <option value="APROBADA">Aprobada</option>
                <option value="ENTREGADO">Entregado</option>
                <option value="RECHAZADA">Rechazada</option>
                <option value="RETRASADO">Retrasado</option>
            </select>
        </div>

        <button onclick="aplicarFiltro()"
                class="bg-[#1b4785] text-white px-4 py-2 rounded-lg hover:bg-[#479990] transition">
            🔍 Aplicar filtro
        </button>

        <button onclick="limpiarFiltro()"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
            ✖ Mostrar todas
        </button>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto border rounded-xl -mx-2 sm:mx-0">
        <table class="min-w-[940px] w-full text-sm" id="tablaSolicitudes">
            <thead class="bg-gray-100 border-b-2 border-[#1b4785] sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Apellido</th>
                    <th class="px-4 py-2 text-left">Carrera</th>
                    <th class="px-4 py-2 text-left">Celular</th>
                    <th class="px-4 py-2 text-left">Curso</th>
                    <th class="px-4 py-2 text-left">Libro</th>
                    <th class="px-4 py-2 text-left">Stock</th>
                    <th class="px-4 py-2 text-left">Fecha Solicitud</th>
                    <th class="px-4 py-2 text-left">Fecha Respuesta</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                    <th class="px-4 py-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="11" class="text-center py-6 text-gray-400">Cargando solicitudes…</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/admin/prestamos.js?v=20260414a"></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
