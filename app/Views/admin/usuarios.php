<?php
/**
 * app/Views/admin/usuarios.php
 * Módulo: Gestión de Usuarios
 */
$pageTitle    = 'Gestión de Usuarios';
$activeModule = 'usuarios';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">

    <!-- Encabezado -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
        <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785]">👥 Gestión de Usuarios</h3>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 text-sm">Buscar:</span>
                <input type="text" id="buscarUsuario"
                       placeholder="🔍 Nombre o Cédula..."
                       class="input w-full sm:w-56 px-3 py-2 text-sm">
            </div>
            <button onclick="abrirFormularioUsuario()"
                    class="bg-[#1b4785] text-white px-5 py-2 rounded-lg text-sm
                           hover:bg-[#479990] transition">
                ➕ Nuevo Usuario
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto border rounded-xl -mx-2 sm:mx-0">
        <table class="min-w-[900px] w-full text-sm">
            <thead class="bg-gray-100 border-b-2 border-[#1b4785] sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 text-left font-bold">Nombre</th>
                    <th class="px-4 py-3 text-left font-bold">Cédula</th>
                    <th class="px-4 py-3 text-left font-bold">Email</th>
                    <th class="px-4 py-3 text-left font-bold">Carrera</th>
                    <th class="px-4 py-3 text-left font-bold">Curso</th>
                    <th class="px-4 py-3 text-left font-bold">Rol</th>
                    <th class="px-4 py-3 text-left font-bold">Estado</th>
                    <th class="px-4 py-3 text-left font-bold">Acciones</th>
                </tr>
            </thead>
            <tbody id="usuariosTableBody">
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-400">
                        Cargando usuarios…
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- =========================================================
     MODAL USUARIO
========================================================== -->
<div id="modalUsuario"
     class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-3 sm:mx-4 p-4 sm:p-6 max-h-[85vh] overflow-y-auto">

        <h2 id="tituloModal" class="text-2xl font-bold text-[#1b4785] mb-4">Nuevo Usuario</h2>

        <form id="formUsuario" onsubmit="guardarUsuario(event)">

            <div class="grid grid-cols-2 gap-4 mb-4">
                <input type="text" id="nombre" placeholder="Nombre"
                       class="border rounded px-3 py-2 w-full" required>
                <input type="text" id="apellido" placeholder="Apellido"
                       class="border rounded px-3 py-2 w-full" required>
            </div>

            <input type="text" id="cedula" placeholder="Cédula" maxlength="10"
                   inputmode="numeric"
                   class="border rounded px-3 py-2 w-full mb-4"
                   required oninput="soloNumeros10(this)">

            <input type="email" id="email" placeholder="Email"
                   class="border rounded px-3 py-2 w-full mb-4" required>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Rol</label>
                <select id="rol" class="border rounded px-3 py-2 w-full" required
                        onchange="actualizarCamposDinamicos()">
                    <option value="">Seleccionar rol…</option>
                </select>
            </div>

            <input type="tel" id="telefono" placeholder="Teléfono" maxlength="10"
                   inputmode="numeric"
                   class="border rounded px-3 py-2 w-full mb-4"
                   oninput="soloNumeros10(this)">

            <input type="text" id="direccion" placeholder="Dirección"
                   class="border rounded px-3 py-2 w-full mb-4">

            <div id="campoCarrera" class="hidden mb-4">
                <input type="text" id="carrera" placeholder="Carrera"
                       class="border rounded px-3 py-2 w-full">
            </div>

            <div id="campoCurso" class="hidden mb-4">
                <input type="text" id="curso" placeholder="Curso"
                       class="border rounded px-3 py-2 w-full">
            </div>

            <div id="campoContrasena" class="hidden mb-4 relative">
                <input type="password" id="contrasena"
                       placeholder="Contraseña (opcional)"
                       class="border rounded px-3 py-2 w-full pr-10">
                <i id="toggleIcon"
                   class="fa-solid fa-eye absolute right-3 top-1/2 -translate-y-1/2
                          cursor-pointer text-gray-500"
                   onclick="togglePassword()"></i>
            </div>

            <div id="campoEstado" class="hidden mb-4">
                <label class="block text-sm font-semibold mb-2">Estado</label>
                <select id="estado" class="border rounded px-3 py-2 w-full">
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
            </div>

            <input type="hidden" id="usuarioId">

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-[#164c7e] hover:bg-red-600 text-white
                               font-bold py-2 px-4 rounded-lg transition">
                    Guardar
                </button>
                <button type="button" onclick="cerrarModalUsuario()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800
                               font-bold py-2 px-4 rounded-lg transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('contrasena');
    const icon  = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>

<script src="<?= BASE_URL ?>/js/admin/usuarios.js?v=20260414a" defer></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
