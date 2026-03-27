/* =============================
   VARIABLES GLOBALES
============================= */
let intervaloAlertas = null;
let sesionExpirada = false;
let inactivityTimer = null;

const ALERT_REPEAT_MS = 5 * 60 * 1000;      // Alertas repetidas
const INACTIVITY_LIMIT = 5 * 60 * 1000;     // 5 minutos de inactividad

/* =============================
   SESIÓN EXPIRADA
============================= */
function manejarSesionExpirada() {
    if (sesionExpirada) return;
    sesionExpirada = true;

    if (intervaloAlertas) clearInterval(intervaloAlertas);
    if (inactivityTimer) clearTimeout(inactivityTimer);

    Swal.fire({
        icon: "warning",
        title: "Sesión expirada",
        text: "Tu sesión ha finalizado por inactividad.",
        confirmButtonText: "Iniciar sesión",
        allowOutsideClick: false
    }).then(() => {
        window.location.replace(`${BASE_URL}/logout`);

    });
}

/* =============================
   CONTROL DE INACTIVIDAD
============================= */
function resetInactivityTimer() {
    if (sesionExpirada) return;

    if (inactivityTimer) clearTimeout(inactivityTimer);

    inactivityTimer = setTimeout(() => {
        manejarSesionExpirada();
    }, INACTIVITY_LIMIT);
}

// Eventos que cuentan como actividad del usuario
["click", "mousemove", "keydown", "scroll", "touchstart"].forEach(evt => {
    document.addEventListener(evt, resetInactivityTimer, true);
});

/* =============================
   UTILIDADES
============================= */
function parseDateSafe(dateStr) {
    if (!dateStr) return null;
    const d = new Date(dateStr.replace(" ", "T"));
    return isNaN(d) ? null : d;
}

function formatearEstado(p) {
    const map = {
        PENDIENTE: "text-yellow-600",
        APROBADA: "text-green-600",
        ENTREGADO: "text-blue-600",
        RECHAZADA: "text-red-600"
    };
    return `<span class="${map[p.estado] || ""} font-bold">${p.estado}</span>`;
}

/* =============================
   CARGAR MIS PRÉSTAMOS
============================= */
async function cargarMisPrestamos() {
    const tbody = document.querySelector("#tablaMisPrestamos tbody");
    if (!tbody || sesionExpirada) return;

    tbody.innerHTML = `<tr><td colspan="4" class="text-center py-8">Cargando...</td></tr>`;

    try {
        const res = await fetch(`${BASE_URL}/solicitudes/mis`, {
            credentials: "include"
        });

        if (res.status === 401 || res.status === 403) {
            manejarSesionExpirada();
            return;
        }

        const data = await res.json();
        tbody.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-6">
                        No tienes préstamos activos 📚
                    </td>
                </tr>`;
            return;
        }

        data.forEach(p => {
            tbody.innerHTML += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3">${p.libro_titulo}</td>
                    <td class="px-4 py-3">${p.fecha_solicitud}</td>
                    <td class="px-4 py-3">${p.fecha_respuesta || "-"}</td>
                    <td class="px-4 py-3">${formatearEstado(p)}</td>
                </tr>`;
        });

    } catch (e) {
        console.error("Error cargando préstamos:", e);
    }
}

/* =============================
   ALERTAS AUTOMÁTICAS
============================= */
function checkSolicitudesForAlerts() {
    if (sesionExpirada) return;

    fetch(`${BASE_URL}/solicitudes/mis`, { credentials: "include" })
        .then(r => {
            if (r.status === 401 || r.status === 403) {
                manejarSesionExpirada();
                throw new Error("Sesión expirada");
            }
            return r.json();
        })
        .then(data => {
            if (!Array.isArray(data)) return;

            const ahora = Date.now();

            data.forEach(s => {
                const id = s.id;

                /* APROBADA (una sola vez) */
                if (s.estado === "APROBADA") {
                    const key = `noti_aprobada_${id}`;
                    if (!localStorage.getItem(key)) {
                        Swal.fire({
                            icon: "success",
                            title: "Solicitud aprobada",
                            text: s.libro_titulo
                        });
                        localStorage.setItem(key, "1");
                    }
                }

                /* VENCIMIENTO */
                const fechaDev = parseDateSafe(s.fecha_devolucion);
                if (fechaDev && ahora > fechaDev.getTime()) {
                    const key = `alert_vencido_${id}`;
                    const last = Number(localStorage.getItem(key) || 0);

                    if (ahora - last >= ALERT_REPEAT_MS) {
                        Swal.fire({
                            icon: "warning",
                            title: "Préstamo vencido",
                            text: s.libro_titulo
                        });
                        localStorage.setItem(key, ahora);
                    }
                }
            });
        })
        .catch(() => {});
}

/* =============================
   INIT
============================= */
document.addEventListener("DOMContentLoaded", () => {
    cargarMisPrestamos();
    resetInactivityTimer();
    intervaloAlertas = setInterval(checkSolicitudesForAlerts, 5000);
});
