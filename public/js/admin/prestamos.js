/* =============================
   CARGAR SOLICITUDES
============================= */
function cargarSolicitudes(filtroMes = "", filtroEstado = "") {

    const url = `${BASE_URL}/solicitudes/list?mes=${filtroMes}&estado=${filtroEstado}`;

    fetch(url, { credentials: "include" })
        .then(r => r.json())
        .then(res => {

            const tbody = document.querySelector("#tablaSolicitudes tbody");
            tbody.innerHTML = "";

            const data = res.data ?? [];

            if (!res.success || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center py-6 text-gray-500">
                            No hay solicitudes registradas
                        </td>
                    </tr>`;
                return;
            }

            data.forEach(s => {

                const estado = s.estado || "-";

                const estadoClass = obtenerClaseEstado(estado);
                const botones = generarBotones(estado, s.id);

                tbody.innerHTML += `
                    <tr>
                        <td class="px-4 py-3">${s.usuario_nombre || "-"}</td>
                        <td class="px-4 py-3">${s.usuario_apellido || "-"}</td>
                        <td class="px-4 py-3">${s.carrera || "-"}</td>
                        <td class="px-4 py-3">${s.telefono || "-"}</td>
                        <td class="px-4 py-3">${s.curso || "-"}</td>
                        <td class="px-4 py-3">${s.libro_titulo || "-"}</td>
                        <td class="px-4 py-3">${s.stock ?? 0} / ${s.numero_ejemplares ?? 0}</td>
                        <td class="px-4 py-3">${formatearFecha(s.fecha_solicitud)}</td>
                        <td class="px-4 py-3">${formatearFecha(s.fecha_respuesta)}</td>
                        <td class="px-4 py-3">
                            <span class="${estadoClass}">${estado}</span>
                        </td>
                        <td class="px-4 py-3">${botones}</td>
                    </tr>`;
            });
        })
        .catch(err => {
            document.querySelector("#tablaSolicitudes tbody").innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-red-600 py-6">
                        Error al cargar solicitudes
                    </td>
                </tr>`;
            console.error(err);
        });
}

/* =============================
   UTILIDADES
============================= */
function formatearFecha(fecha) {
    return fecha ? new Date(fecha).toLocaleString() : "-";
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
    const mesActual = new Date().toISOString().slice(0, 7);
    document.getElementById("filtroMes").value = mesActual;
    document.getElementById("filtroEstado").value = "";
    cargarSolicitudes(mesActual, "");
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
document.addEventListener("DOMContentLoaded", () => {
    limpiarFiltro();
});
