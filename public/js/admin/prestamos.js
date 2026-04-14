/* =============================
   CARGAR SOLICITUDES
============================= */
const SOLICITUDES_PAGE_SIZE = 30;
let solicitudesData = [];
let solicitudesCurrentPage = 1;

function ensureSolicitudesPaginationWrap() {
    let wrap = document.getElementById("paginacionSolicitudes");
    if (wrap) return wrap;

    const tableBody = document.querySelector("#tablaSolicitudes tbody");
    const tableContainer = tableBody?.closest(".overflow-x-auto");
    if (!tableContainer || !tableContainer.parentNode) return null;

    wrap = document.createElement("div");
    wrap.id = "paginacionSolicitudes";
    wrap.className = "mt-4 flex flex-wrap items-center justify-center gap-2";
    tableContainer.parentNode.insertBefore(wrap, tableContainer.nextSibling);
    return wrap;
}

function renderizarPaginacionSolicitudes() {
    const wrap = ensureSolicitudesPaginationWrap();
    if (!wrap) return;

    const totalPages = Math.ceil(solicitudesData.length / SOLICITUDES_PAGE_SIZE);
    if (totalPages <= 1) {
        wrap.innerHTML = "";
        return;
    }

    const buttons = [];
    for (let i = 1; i <= totalPages; i++) {
        buttons.push(`
            <button
                type="button"
                onclick="cambiarPaginaSolicitudes(${i})"
                class="px-3 py-1.5 rounded-md border text-sm ${i === solicitudesCurrentPage
                    ? 'bg-[#1b4785] text-white border-[#1b4785]'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'}">
                ${i}
            </button>
        `);
    }

    wrap.innerHTML = buttons.join('');
}

function renderizarSolicitudesTabla() {
    const tbody = document.querySelector("#tablaSolicitudes tbody");
    if (!tbody) return;

    const start = (solicitudesCurrentPage - 1) * SOLICITUDES_PAGE_SIZE;
    const pageRows = solicitudesData.slice(start, start + SOLICITUDES_PAGE_SIZE);

    if (!pageRows.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-6 text-gray-500">
                    No hay solicitudes registradas
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML = pageRows.map(s => {
        const estado = s.estado || "-";
        const estadoClass = obtenerClaseEstado(estado);
        const botones = generarBotones(estado, s.id);

        return `
            <tr>
                <td class="px-4 py-3">${s.usuario_nombre || "-"}</td>
                <td class="px-4 py-3">${s.usuario_apellido || "-"}</td>
                <td class="px-4 py-3">${s.carrera || "-"}</td>
                <td class="px-4 py-3">${s.telefono || "-"}</td>
                <td class="px-4 py-3">${s.curso || "-"}</td>
                <td class="px-4 py-3">${s.libro_titulo || "-"}</td>
                <td class="px-4 py-3">${s.stock ?? 0} / ${s.numero_ejemplares ?? 0}</td>
                <td class="px-4 py-3">${formatearFecha(s.fecha_solicitud)}</td>
                <td class="px-4 py-3">${formatearFechaRespuesta(s.fecha_respuesta)}</td>
                <td class="px-4 py-3">
                    <span class="${estadoClass}">${estado}</span>
                </td>
                <td class="px-4 py-3">${botones}</td>
            </tr>`;
    }).join('');
}

function cambiarPaginaSolicitudes(page) {
    const totalPages = Math.max(1, Math.ceil(solicitudesData.length / SOLICITUDES_PAGE_SIZE));
    solicitudesCurrentPage = Math.min(Math.max(1, page), totalPages);
    renderizarSolicitudesTabla();
    renderizarPaginacionSolicitudes();
}

function cargarSolicitudes(filtroMes = "", filtroEstado = "") {

    const url = `${BASE_URL}/solicitudes/list?mes=${filtroMes}&estado=${filtroEstado}`;

    fetch(url, { credentials: "include" })
        .then(r => r.json())
        .then(res => {
            const data = res.data ?? [];
            solicitudesData = res.success ? data : [];
            solicitudesCurrentPage = 1;
            renderizarSolicitudesTabla();
            renderizarPaginacionSolicitudes();
        })
        .catch(err => {
            document.querySelector("#tablaSolicitudes tbody").innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-red-600 py-6">
                        Error al cargar solicitudes
                    </td>
                </tr>`;
            const wrap = ensureSolicitudesPaginationWrap();
            if (wrap) wrap.innerHTML = "";
            console.error(err);
        });
}

/* =============================
   UTILIDADES
============================= */
function formatearFecha(fecha) {
    if (!fecha) return '—';
    const d = new Date(fecha);
    return isNaN(d.getTime()) ? '—' : d.toLocaleString('es-EC');
}

function formatearFechaRespuesta(fecha) {
    if (!fecha) return '<span class="text-gray-400 italic text-xs">Sin respuesta</span>';
    const d = new Date(fecha);
    return isNaN(d.getTime())
        ? '<span class="text-gray-400 italic text-xs">Sin respuesta</span>'
        : d.toLocaleString('es-EC');
}

function obtenerClaseEstado(estado) {
    const clases = {
        PENDIENTE: "bg-yellow-100 text-yellow-800 font-bold px-3 py-1 rounded-full text-xs",
        APROBADA: "bg-blue-100 text-blue-700 font-bold px-3 py-1 rounded-full text-xs",
        ENTREGADO: "bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-xs",
        RECHAZADA: "bg-red-100 text-red-700 font-bold px-3 py-1 rounded-full text-xs",
        RETRASADO: "bg-orange-100 text-orange-700 font-bold px-3 py-1 rounded-full text-xs"
    };
    return clases[estado] || "bg-gray-100 text-gray-700 font-bold px-3 py-1 rounded-full text-xs";
}

function generarBotones(estado, id) {
    if (estado === "PENDIENTE") {
        return `
            <button onclick="aprobar(${id})" class="bg-green-600 text-white px-3 py-1 rounded">Aprobar</button>
            <button onclick="rechazar(${id})" class="bg-red-600 text-white px-3 py-1 rounded ml-2">Rechazar</button>`;
    }

    if (estado === "APROBADA") {
        return `<button onclick="entregar(${id})" class="bg-blue-600 text-white px-3 py-1 rounded">Entregar</button>`;
    }

    if (estado === "RETRASADO") {
        return `
            <span class="text-orange-600 font-bold mr-2">Retrasado</span>
            <button onclick="entregar(${id})" class="bg-blue-700 text-white px-3 py-1 rounded">
                Marcar Devuelto
            </button>`;
    }

    return `<span class="text-gray-500">Sin acciones</span>`;
}

/* =============================
   FILTROS
============================= */
function aplicarFiltro() {
    cargarSolicitudes(
        document.getElementById("filtroMes").value,
        document.getElementById("filtroEstado").value
    );
}

function limpiarFiltro() {
    document.getElementById("filtroMes").value = "";
    document.getElementById("filtroEstado").value = "";
    cargarSolicitudes("", "");
}

/* =============================
   ACCIONES
============================= */
function aprobar(id) {
    Swal.fire({
        title: "¿Aprobar solicitud?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, aprobar"
    }).then(r => {
        if (r.isConfirmed) enviarAccion("aprobar", { id });
    });
}

function rechazar(id) {
    Swal.fire({
        title: "Motivo del rechazo",
        input: "textarea",
        showCancelButton: true,
        inputValidator: v => !v && "Debe ingresar un motivo"
    }).then(r => {
        if (r.isConfirmed) enviarAccion("rechazar", { id, motivo: r.value });
    });
}

function entregar(id) {
    Swal.fire({
        title: "¿Libro devuelto?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí"
    }).then(r => {
        if (r.isConfirmed) enviarAccion("entregar", { id });
    });
}

function enviarAccion(ruta, data) {
    fetch(`${BASE_URL}/solicitudes/${ruta}`, {
        method: "POST",
        body: new URLSearchParams(data),
        credentials: "include"
    })
    .then(r => r.json())
    .then(res => {
        Swal.fire("Proceso exitoso", res.msg, "success");
        aplicarFiltro();
    });
}

/* =============================
   INIT
============================= */
document.addEventListener('DOMContentLoaded', () => {
    cargarSolicitudes(); // todas las solicitudes, sin filtro de mes
});
