let studentAlertInterval = null;
let currentPage = 1;
const perPage = 10;

function parseDateSafe(dateStr) {
    if (!dateStr) return null;
    const d = new Date(String(dateStr).replace(' ', 'T'));
    return isNaN(d.getTime()) ? null : d;
}

function formatDateCell(dateStr, emptyText = 'Sin respuesta') {
    const d = parseDateSafe(dateStr);
    if (!d) return `<span class="text-gray-400 italic text-xs">${emptyText}</span>`;
    return d.toLocaleString('es-EC');
}

function formatEstado(estado) {
    const classes = {
        PENDIENTE: 'text-yellow-600',
        APROBADA: 'text-green-600',
        ENTREGADO: 'text-blue-600',
        RECHAZADA: 'text-red-600',
        RETRASADO: 'text-orange-600'
    };
    return `<span class="${classes[estado] || 'text-gray-600'} font-bold">${estado || '-'}</span>`;
}

async function cargarMisPrestamosEstudiante() {
    const tbody = document.querySelector('#tablaMisPrestamos tbody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8">Cargando...</td></tr>';

    try {
        const estado = document.getElementById('filtroEstadoStudent')?.value || '';
        const desde = document.getElementById('filtroDesdeStudent')?.value || '';
        const hasta = document.getElementById('filtroHastaStudent')?.value || '';

        const query = new URLSearchParams({
            page: String(currentPage),
            per_page: String(perPage),
            estado,
            desde,
            hasta
        });

        const res = await fetch(`${BASE_URL}/solicitudes/mis?${query.toString()}`, { credentials: 'include' });

        if (res.status === 401 || res.status === 403) {
            window.location.replace(`${BASE_URL}/logout`);
            return;
        }

        const payload = await res.json();
        const data = Array.isArray(payload) ? payload : (payload.data || []);
        const pagination = Array.isArray(payload)
            ? { page: 1, total_pages: 1, total: data.length }
            : (payload.pagination || { page: 1, total_pages: 1, total: data.length });

        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6">No tienes prestamos registrados</td></tr>';
            actualizarContadoresPrestamos([]);
            actualizarPaginacion(pagination);
            return;
        }

        tbody.innerHTML = data.map(p => `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3">${p.libro_titulo || '-'}</td>
                <td class="px-4 py-3">${formatDateCell(p.fecha_solicitud, 'Sin fecha')}</td>
                <td class="px-4 py-3">${formatDateCell(p.fecha_respuesta, 'Sin respuesta')}</td>
                <td class="px-4 py-3">${formatEstado(p.estado)}</td>
            </tr>
        `).join('');

        actualizarContadoresPrestamos(data);
        actualizarPaginacion(pagination);
    } catch (_e) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-red-600">Error al cargar prestamos</td></tr>';
    }
}

function actualizarContadoresPrestamos(data) {
    const activos = data.filter(x => x.estado === 'APROBADA' || x.estado === 'RETRASADO').length;
    const atrasados = data.filter(x => x.estado === 'RETRASADO').length;
    const devueltos = data.filter(x => x.estado === 'ENTREGADO').length;

    const a = document.getElementById('contadorActivos');
    const b = document.getElementById('contadorAtrasados');
    const c = document.getElementById('contadorDevueltos');

    if (a) a.textContent = String(activos);
    if (b) b.textContent = String(atrasados);
    if (c) c.textContent = String(devueltos);
}

function checkSolicitudesForAlerts() {
    fetch(`${BASE_URL}/solicitudes/mis`, { credentials: 'include' })
        .then(r => {
            if (r.status === 401 || r.status === 403) {
                window.location.replace(`${BASE_URL}/logout`);
                throw new Error('Sesion expirada');
            }
            return r.json();
        })
        .then(data => {
            if (!Array.isArray(data)) return;

            const now = Date.now();
            data.forEach(s => {
                const id = s.id;
                if (s.estado === 'APROBADA') {
                    const key = `noti_aprobada_${id}`;
                    if (!localStorage.getItem(key)) {
                        Swal.fire({ icon: 'success', title: 'Solicitud aprobada', text: s.libro_titulo || 'Tu solicitud fue aprobada' });
                        localStorage.setItem(key, '1');
                    }
                }

                const fechaDev = parseDateSafe(s.fecha_devolucion);
                if (fechaDev && now > fechaDev.getTime()) {
                    const key = `alert_vencido_${id}`;
                    const last = Number(localStorage.getItem(key) || 0);
                    if (now - last >= 5 * 60 * 1000) {
                        Swal.fire({ icon: 'warning', title: 'Prestamo vencido', text: s.libro_titulo || 'Tienes un prestamo vencido' });
                        localStorage.setItem(key, String(now));
                    }
                }
            });
        })
        .catch(() => {});
}

function actualizarPaginacion(pagination) {
    const info = document.getElementById('studentPaginationInfo');
    const prev = document.getElementById('btnPrevStudent');
    const next = document.getElementById('btnNextStudent');

    const page = Number(pagination.page || 1);
    const totalPages = Number(pagination.total_pages || 1);
    const total = Number(pagination.total || 0);

    if (info) {
        info.textContent = `Pagina ${page} de ${totalPages} - ${total} resultado(s)`;
    }

    if (prev) prev.disabled = page <= 1;
    if (next) next.disabled = page >= totalPages;
}

function inicializarFiltrosYPaginacion() {
    document.getElementById('btnAplicarFiltroStudent')?.addEventListener('click', () => {
        currentPage = 1;
        cargarMisPrestamosEstudiante();
    });

    document.getElementById('btnLimpiarFiltroStudent')?.addEventListener('click', () => {
        const estado = document.getElementById('filtroEstadoStudent');
        const desde = document.getElementById('filtroDesdeStudent');
        const hasta = document.getElementById('filtroHastaStudent');

        if (estado) estado.value = '';
        if (desde) desde.value = '';
        if (hasta) hasta.value = '';

        currentPage = 1;
        cargarMisPrestamosEstudiante();
    });

    document.getElementById('btnPrevStudent')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage -= 1;
            cargarMisPrestamosEstudiante();
        }
    });

    document.getElementById('btnNextStudent')?.addEventListener('click', () => {
        currentPage += 1;
        cargarMisPrestamosEstudiante();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    inicializarFiltrosYPaginacion();
    cargarMisPrestamosEstudiante();
    studentAlertInterval = setInterval(checkSolicitudesForAlerts, 15000);
});
