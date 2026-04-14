/* ===========================
   VALIDACIONES
=========================== */
function soloNumeros10(input) {
    input.value = input.value.replace(/\D/g, '');
    if (input.value.length > 10) {
        input.value = input.value.slice(0, 10);
    }
}

/* ===========================
   VARIABLES GLOBALES
=========================== */
let usuariosData = [];
let rolesData = [];
let buscadorUsuariosInicializado = false;
let usuariosCargando = false;
let rolesCargando = false;
const USUARIOS_PAGE_SIZE = 30;
let usuariosFiltered = [];
let usuariosCurrentPage = 1;

/* ===========================
   MODAL
=========================== */
function abrirFormularioUsuario() {
    document.getElementById('usuarioId').value = '';
    document.getElementById('formUsuario').reset();
    document.getElementById('tituloModal').innerText = 'Nuevo Usuario';
    document.getElementById('cedula').readOnly = false;
    document.getElementById('campoEstado').classList.add('hidden');
    document.getElementById('modalUsuario').classList.remove('hidden');
}

function cerrarModalUsuario() {
    document.getElementById('modalUsuario').classList.add('hidden');
}

/* ===========================
   ROLES
=========================== */
function cargarRoles() {
    if (rolesData.length || rolesCargando) {
        return;
    }

    rolesCargando = true;
    fetch(BASE_URL + '/admin/usuarios/roles')
        .then(r => r.json())
        .then(data => {
            const rolSelect = document.getElementById('rol');
            rolSelect.innerHTML = '<option value="">Seleccionar rol...</option>';

            rolesData = data.data || data;

            rolesData.forEach(rol => {
                const option = document.createElement('option');
                option.value = rol.id;
                option.text = rol.nombre;
                rolSelect.appendChild(option);
            });
        })
        .finally(() => {
            rolesCargando = false;
        });
}

/* ===========================
   USUARIOS
=========================== */
function cargarUsuarios(forceReload = false) {
    if (!forceReload && usuariosData.length) {
        aplicarFiltroUsuarios();
        return;
    }

    if (usuariosCargando) {
        return;
    }

    usuariosCargando = true;

    fetch(BASE_URL + '/admin/usuarios/get')
        .then(r => r.json())
        .then(data => {
            usuariosData = data.data || data;
            aplicarFiltroUsuarios();
        })
        .finally(() => {
            usuariosCargando = false;
        });
}

function ensureUsuariosPaginationWrap() {
    let wrap = document.getElementById('paginacionUsuarios');
    if (wrap) return wrap;

    const tableBody = document.getElementById('usuariosTableBody');
    const tableContainer = tableBody?.closest('.overflow-x-auto');
    if (!tableContainer || !tableContainer.parentNode) return null;

    wrap = document.createElement('div');
    wrap.id = 'paginacionUsuarios';
    wrap.className = 'mt-4 flex flex-wrap items-center justify-center gap-2';
    tableContainer.parentNode.insertBefore(wrap, tableContainer.nextSibling);
    return wrap;
}

function renderizarPaginacionUsuarios() {
    const wrap = ensureUsuariosPaginationWrap();
    if (!wrap) return;

    const totalPages = Math.ceil(usuariosFiltered.length / USUARIOS_PAGE_SIZE);
    if (totalPages <= 1) {
        wrap.innerHTML = '';
        return;
    }

    const buttons = [];
    for (let i = 1; i <= totalPages; i++) {
        buttons.push(`
            <button
                type="button"
                onclick="cambiarPaginaUsuarios(${i})"
                class="px-3 py-1.5 rounded-md border text-sm ${i === usuariosCurrentPage
                    ? 'bg-[#1b4785] text-white border-[#1b4785]'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'}">
                ${i}
            </button>
        `);
    }

    wrap.innerHTML = buttons.join('');
}

function aplicarFiltroUsuarios() {
    const buscador = document.getElementById('buscarUsuario');
    const filtro = (buscador?.value || '').toLowerCase().trim();

    usuariosFiltered = usuariosData.filter(usuario => {
        const nombre = `${usuario.nombre || ''} ${usuario.apellido || ''}`.toLowerCase();
        const cedula = (usuario.cedula || '').toLowerCase();
        return nombre.includes(filtro) || cedula.includes(filtro);
    });

    usuariosCurrentPage = 1;
    renderizarUsuarios();
    renderizarPaginacionUsuarios();
}

function cambiarPaginaUsuarios(page) {
    const totalPages = Math.max(1, Math.ceil(usuariosFiltered.length / USUARIOS_PAGE_SIZE));
    usuariosCurrentPage = Math.min(Math.max(1, page), totalPages);
    renderizarUsuarios();
    renderizarPaginacionUsuarios();
}

function renderizarUsuarios() {
    const tbody = document.getElementById('usuariosTableBody');
    const start = (usuariosCurrentPage - 1) * USUARIOS_PAGE_SIZE;
    const pageRows = usuariosFiltered.slice(start, start + USUARIOS_PAGE_SIZE);

    const rows = pageRows.map(usuario => {
        const estadoClass =
            usuario.estado === 'ACTIVO'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700';

        return `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-4">${usuario.nombre} ${usuario.apellido}</td>
                <td class="px-4 py-4">${usuario.cedula}</td>
                <td class="px-4 py-4">${usuario.email}</td>
                <td class="px-4 py-4">${usuario.carrera ?? ''}</td>
                <td class="px-4 py-4">${usuario.curso ?? ''}</td>
                <td class="px-4 py-4">
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                        ${usuario.rol_nombre}
                    </span>
                </td>
                <td class="px-4 py-4">
                    <span class="${estadoClass} px-3 py-1 rounded-full text-xs font-bold">
                        ${usuario.estado}
                    </span>
                </td>
                <td class="px-4 py-4 space-x-2">
                    <button onclick="editarUsuario(${usuario.id})"
                        class="px-3 py-1 bg-yellow-500 text-white rounded">✏️</button>
                    <button onclick="eliminarUsuario(${usuario.id})"
                        class="px-3 py-1 bg-red-600 text-white rounded">🗑️</button>
                </td>
            </tr>
        `;
    }).join('');

    tbody.innerHTML = rows || `
        <tr>
            <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                No hay usuarios para mostrar
            </td>
        </tr>
    `;
}

/* ===========================
   EDITAR
=========================== */
function editarUsuario(id) {
    const usuario = usuariosData.find(u => parseInt(u.id) === parseInt(id));
    if (!usuario) return;

    document.getElementById('usuarioId').value = usuario.id;
    document.getElementById('nombre').value = usuario.nombre;
    document.getElementById('apellido').value = usuario.apellido;
    document.getElementById('cedula').value = usuario.cedula;
    document.getElementById('cedula').readOnly = true;
    document.getElementById('email').value = usuario.email;
    document.getElementById('rol').value = usuario.rol_id;
    document.getElementById('telefono').value = usuario.telefono || '';
    document.getElementById('direccion').value = usuario.direccion || '';
    document.getElementById('carrera').value = usuario.carrera || '';
    document.getElementById('curso').value = usuario.curso || '';
    document.getElementById('estado').value = usuario.estado;

    document.getElementById('tituloModal').innerText = 'Editar Usuario';
    document.getElementById('campoEstado').classList.remove('hidden');

    actualizarCamposDinamicos();
    document.getElementById('modalUsuario').classList.remove('hidden');
}

/* ===========================
   CAMPOS DINÁMICOS
=========================== */
function actualizarCamposDinamicos() {
    const rolId = parseInt(document.getElementById('rol').value);
    const rol = rolesData.find(r => parseInt(r.id) === rolId);
    if (!rol) return;

    const nombreRol = rol.nombre.toLowerCase();

    if (
        nombreRol.includes('estudiante') ||
        nombreRol.includes('administrador') ||
        nombreRol.includes('solicitante')
    ) {
        document.getElementById('campoContrasena').classList.remove('hidden');
    } else {
        document.getElementById('campoContrasena').classList.add('hidden');
    }

    if (
        nombreRol.includes('estudiante') ||
        nombreRol.includes('solicitante')
    ) {
        document.getElementById('campoCarrera').classList.remove('hidden');
        document.getElementById('campoCurso').classList.remove('hidden');
    } else {
        document.getElementById('campoCarrera').classList.add('hidden');
        document.getElementById('campoCurso').classList.add('hidden');
    }
}

/* ===========================
   GUARDAR
=========================== */
function guardarUsuario(event) {
    event.preventDefault();

    const usuarioId = document.getElementById('usuarioId').value.trim();
    const endpoint = usuarioId
        ? BASE_URL + '/admin/usuarios/update'
        : BASE_URL + '/admin/usuarios/create';

    const data = {
        nombre: nombre.value,
        apellido: apellido.value,
        cedula: cedula.value,
        email: email.value,
        rol_id: rol.value,
        telefono: telefono.value,
        direccion: direccion.value,
        carrera: carrera.value,
        curso: curso.value
    };

    const pass = document.getElementById('contrasena')?.value.trim();
    if (pass) data.contrasena = pass;

    if (usuarioId) {
        data.id = usuarioId;
        data.estado = estado.value;
    }

    if (data.cedula.length !== 10) {
        Swal.fire('Cédula inválida', 'Debe tener 10 dígitos', 'warning');
        return;
    }

    if (data.telefono && data.telefono.length !== 10) {
        Swal.fire('Teléfono inválido', 'Debe tener 10 dígitos', 'warning');
        return;
    }

    fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: usuarioId ? 'Usuario actualizado' : 'Usuario creado',
                    timer: 2000,
                    showConfirmButton: false
                });
                cerrarModalUsuario();
                cargarUsuarios(true);
            } else {
                Swal.fire('Error', res.message || 'No se pudo guardar', 'error');
            }
        });
}

/* ===========================
   ELIMINAR
=========================== */
function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + '/admin/usuarios/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire('Eliminado', 'Usuario eliminado', 'success');
                        cargarUsuarios(true);
                    } else {
                        Swal.fire('Error', 'No se pudo eliminar', 'error');
                    }
                });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (!buscadorUsuariosInicializado) {
        const buscador = document.getElementById('buscarUsuario');
        if (buscador) {
            buscador.addEventListener('input', function () {
                aplicarFiltroUsuarios();
            });
            buscadorUsuariosInicializado = true;
        }
    }

    cargarRoles();
    cargarUsuarios();
});
