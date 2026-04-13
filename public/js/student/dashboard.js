async function cargarResumenPrestamosEstudiante() {
    try {
        const res = await fetch(`${BASE_URL}/solicitudes/mis`, { credentials: 'include' });
        if (!res.ok) return;
        const payload = await res.json();
        const data = Array.isArray(payload) ? payload : (payload.data || []);
        if (!Array.isArray(data)) return;

        const activos = data.filter(x => x.estado === 'APROBADA' || x.estado === 'RETRASADO').length;
        const atrasados = data.filter(x => x.estado === 'RETRASADO').length;
        const devueltos = data.filter(x => x.estado === 'ENTREGADO').length;

        const a = document.getElementById('contadorActivos');
        const b = document.getElementById('contadorAtrasados');
        const c = document.getElementById('contadorDevueltos');

        if (a) a.textContent = String(activos);
        if (b) b.textContent = String(atrasados);
        if (c) c.textContent = String(devueltos);
    } catch (_e) {
        // silencio para no romper UX
    }
}

document.addEventListener('DOMContentLoaded', () => {
    cargarResumenPrestamosEstudiante();
});
