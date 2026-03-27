let categorias = [];
let tipos = [];

/* ===========================
   CARGAR CATEGORÍAS Y TIPOS
=========================== */
async function cargarCategorias() {
    try {
        // 1️⃣ Cargar tipos
        const resTipos = await fetch(`${BASE_URL}/categorias/tipos`);
        const tiposJson = await resTipos.json();
        tipos = tiposJson.data || [];

        const selectTipo = document.getElementById('categoriaTipo');
        selectTipo.innerHTML = '<option value="">Seleccione un tipo...</option>';
        tipos.forEach(t => {
            selectTipo.innerHTML += `<option value="${t.id}">${t.nombre}</option>`;
        });

        // 2️⃣ Cargar categorías
        const res = await fetch(`${BASE_URL}/categorias/get`);
        const data = await res.json();
        categorias = data.data || [];

        const tbody = document.getElementById('tablaCategorias');

        if (!data.success || categorias.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-500 text-lg">
                        No hay categorías registradas
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = categorias.map(cat => {
            const estadoClass = cat.estado === 'ACTIVO'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700';

            const tipoNombre =
                cat.tipo_nombre ||
                tipos.find(t => t.id == cat.tipo_id)?.nombre ||
                '—';

            return `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-4 font-medium">${cat.nombre}</td>
                    <td class="px-4 py-4">
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                            ${tipoNombre}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <span class="${estadoClass} px-3 py-1 rounded-full text-xs font-bold">
                            ${cat.estado}
                        </span>
                    </td>
                    <td class="px-4 py-4 space-x-2">
                        <button onclick="editarCategoria(${cat.id})"
                                class="px-3 py-1 bg-yellow-500 text-white rounded">✏️</button>
                        <button onclick="eliminarCategoria(${cat.id})"
                                class="px-3 py-1 bg-red-600 text-white rounded">🗑️</button>
                    </td>
                </tr>`;
        }).join('');

    } catch (error) {
        console.error('Error cargando categorías:', error);
    }
}

/* ===========================
   BUSCADOR
=========================== */
document.getElementById("buscarCategoria").addEventListener("input", function () {
    const filtro = this.value.toLowerCase();
    document.querySelectorAll("#tablaCategorias tr").forEach(fila => {
        const nombre = fila.cells[0]?.textContent.toLowerCase() || "";
        fila.style.display = nombre.includes(filtro) ? "" : "none";
    });
});

/* ===========================
   MODAL - CREAR
=========================== */
function abrirModalCategoria() {
    document.getElementById('modalCategoria').classList.remove('hidden');
    document.getElementById('tituloModal').innerText = 'Nueva Categoría';

    document.getElementById('categoriaId').value = '';
    document.getElementById('categoriaNombre').value = '';
    document.getElementById('categoriaTipo').value = '';

    // Ocultar estado en crear
    document.getElementById('campoEstadoCategoria').classList.add('hidden');
}

/* ===========================
   MODAL - CERRAR
=========================== */
function cerrarModalCategoria() {
    document.getElementById('modalCategoria').classList.add('hidden');
}

/* ===========================
   GUARDAR / ACTUALIZAR
=========================== */
async function guardarCategoria() {
    const id = document.getElementById('categoriaId').value;
    const nombre = document.getElementById('categoriaNombre').value.trim();
    const tipo_id = document.getElementById('categoriaTipo').value;
    const estado = document.getElementById('categoriaEstado')?.value || 'ACTIVO';

    if (!nombre) {
        Swal.fire({
            icon: "warning",
            title: "Nombre obligatorio",
            text: "Por favor ingrese un nombre."
        });
        return;
    }

    if (!tipo_id) {
        Swal.fire({
            icon: "warning",
            title: "Tipo obligatorio",
            text: "Seleccione un tipo de categoría."
        });
        return;
    }

    const url = id
        ? `${BASE_URL}/categorias/update`
        : `${BASE_URL}/categorias/create`;

    const data = { id, nombre, tipo_id, estado };

    const doSave = async () => {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();

        if (result.success) {
            Swal.fire({
                icon: "success",
                title: id ? "Categoría actualizada" : "Categoría creada",
                showConfirmButton: false,
                timer: 1500
            });
            cerrarModalCategoria();
            cargarCategorias();
        } else {
            throw new Error(result.message || "Error al guardar");
        }
    };

    if (id) {
        const confirm = await Swal.fire({
            title: "Confirmar edición",
            text: "¿Desea guardar los cambios?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#1b4785"
        });

        if (confirm.isConfirmed) await doSave();
    } else {
        await doSave();
    }
}

/* ===========================
   EDITAR
=========================== */
function editarCategoria(id) {
    const cat = categorias.find(c => c.id == id);
    if (!cat) return;

    document.getElementById('modalCategoria').classList.remove('hidden');
    document.getElementById('tituloModal').innerText = 'Editar Categoría';

    document.getElementById('categoriaId').value = cat.id;
    document.getElementById('categoriaNombre').value = cat.nombre || '';
    document.getElementById('categoriaTipo').value = cat.tipo_id || '';
    document.getElementById('categoriaEstado').value = cat.estado || 'ACTIVO';

    // Mostrar estado SOLO en editar
    document.getElementById('campoEstadoCategoria').classList.remove('hidden');
}

/* ===========================
   ELIMINAR
=========================== */
async function eliminarCategoria(id) {
    const confirm = await Swal.fire({
        title: "¿Eliminar categoría?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e02424"
    });

    if (!confirm.isConfirmed) return;

    const res = await fetch(`${BASE_URL}/categorias/delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });

    const data = await res.json();

    if (data.success) {
        Swal.fire({
            icon: "success",
            title: "Categoría eliminada",
            timer: 1200,
            showConfirmButton: false
        });
        cargarCategorias();
    } else {
        Swal.fire("Error", data.message || "No se pudo eliminar", "error");
    }
}

/* ===========================
   INIT
=========================== */
document.addEventListener('DOMContentLoaded', cargarCategorias);
