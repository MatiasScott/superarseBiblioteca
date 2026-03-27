/* =============================
   ELEMENTOS
============================= */
const btnCampana = document.getElementById("btnCampana");
const panel = document.getElementById("panelSolicitudes");
const lista = document.getElementById("listaSolicitudes");
const badge = document.getElementById("badgePendientes");

let pendientesPrevios = 0;
let intervaloActualizacion = null;
let sesionExpirada = false;

/* =============================
   MANEJO DE SESIÓN EXPIRADA
============================= */
function manejarSesionExpirada() {
    if (sesionExpirada) return;
    sesionExpirada = true;

    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    if (inactivityTimer) clearTimeout(inactivityTimer);

    Swal.fire({
        icon: "warning",
        title: "Sesión expirada",
        text: "Tu sesión ha finalizado por inactividad.",
        confirmButtonText: "Iniciar sesión",
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then(() => {
        window.location.replace(`${BASE_URL}/logout`);
    });
}

/* =============================
   FETCH SEGURO
============================= */
function fetchSeguro(url) {
    return fetch(url, { credentials: "include" })
        .then(r => {
            if (r.status === 401 || r.status === 403) {
                manejarSesionExpirada();
                throw new Error("Sesión expirada");
            }
            return r.json();
        });
}

/* =============================
   CARGAR SOLICITUDES
============================= */
function cargarPanelSolicitudes() {
    if (sesionExpirada || !lista) return;

    fetchSeguro(`${BASE_URL}/solicitudes/pendientes`)
        .then(res => {
            if (!res.success) return;

            const data = res.data ?? [];
            const count = res.count ?? 0;

            if (badge) {
                badge.textContent = count;
                badge.classList.toggle("hidden", count === 0);
            }

            lista.innerHTML = "";

            if (data.length === 0) {
                lista.innerHTML = `
                    <li class="px-4 py-4 text-sm text-center text-gray-500">
                        No hay solicitudes pendientes
                    </li>`;
                return;
            }

            data.forEach(s => {
                lista.innerHTML += `
                    <li class="px-4 py-3 hover:bg-gray-50 text-sm cursor-pointer border-b"
                        onclick="irASolicitudes()">
                        <div class="font-semibold text-gray-800">
                            👤 ${s.usuario_nombre} ${s.usuario_apellido}
                        </div>
                        <div class="text-xs text-gray-600 mt-1">
                            📘 ${s.libro_titulo}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            🕒 ${s.fecha_solicitud ?? "Hace poco"}
                        </div>
                    </li>`;
            });
        })
        .catch(() => {});
}

/* =============================
   ACTUALIZAR NOTIFICACIONES
============================= */
function actualizarNotificacionesEnTiempoReal() {
    if (sesionExpirada) return;

    fetchSeguro(`${BASE_URL}/solicitudes/pendientes`)
        .then(res => {
            if (!res.success) return;

            const total = res.count ?? 0;

            if (total > pendientesPrevios && pendientesPrevios !== 0) {
                Swal.fire({
                    icon: "info",
                    title: "🔔 Nueva solicitud",
                    text: "Revisa la campana para ver el detalle",
                    confirmButtonText: "Entendido"
                });
            }

            pendientesPrevios = total;

            if (badge) {
                badge.textContent = total;
                badge.classList.toggle("hidden", total === 0);
            }

            if (panel && !panel.classList.contains("hidden")) {
                cargarPanelSolicitudes();
            }
        })
        .catch(() => {});
}

/* =============================
   EVENTOS CAMPANA
============================= */
if (btnCampana && panel) {
    btnCampana.addEventListener("click", e => {
        e.stopPropagation();
        panel.classList.toggle("hidden");
        cargarPanelSolicitudes();
    });

    document.addEventListener("click", e => {
        if (!btnCampana.contains(e.target) && !panel.contains(e.target)) {
            panel.classList.add("hidden");
        }
    });
}

/* =============================
   REDIRECCIÓN
============================= */
function irASolicitudes() {
    if (typeof showTab === "function") {
        showTab('prestamos');
        setTimeout(() => {
            document.querySelector('#prestamos')?.scrollIntoView({ behavior: "smooth" });
        }, 100);
    }
    if (panel) panel.classList.add("hidden");
}

/* =============================
   AUTO LOGOUT
============================= */
const INACTIVITY_LIMIT_MS = 5 * 60 * 1000;
let inactivityTimer = null;

function resetInactivityTimer() {
    if (sesionExpirada) return;

    if (inactivityTimer) clearTimeout(inactivityTimer);

    inactivityTimer = setTimeout(() => {
        manejarSesionExpirada();
    }, INACTIVITY_LIMIT_MS);
}

["click", "mousemove", "keydown", "scroll", "touchstart"].forEach(evt => {
    document.addEventListener(evt, resetInactivityTimer, true);
});

/* =============================
   INIT
============================= */
document.addEventListener("DOMContentLoaded", () => {
    resetInactivityTimer();
    cargarPanelSolicitudes();
    intervaloActualizacion = setInterval(actualizarNotificacionesEnTiempoReal, 5000);
});
