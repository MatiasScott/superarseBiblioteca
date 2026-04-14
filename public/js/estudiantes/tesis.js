const APP_BASE_URL = window.APP?.BASE_URL || window.BASE_URL || '';
const tesis = window.APP.tesis || [];

/* ======================
   MODAL TESIS
====================== */
window.abrirModal = function (id) {
    const t = tesis.find(item => item.id == id);
    if (!t) return;

    // Incrementar visitas
    
          fetch(`${APP_BASE_URL}/tesis/sumarVisita/${id}`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                t.visitas = data.visitas;
                const v = document.getElementById(`VisitasTesis-${id}`);
                if (v) v.innerText = data.visitas;
            }
        });
        

    document.getElementById('modalContent').innerHTML = `
        <div class="flex flex-col md:flex-row gap-4">
            <img src="${t.portada}" class="w-full md:w-64 h-64 object-cover rounded">
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-superarse-morado-oscuro mb-2">${t.titulo}</h2>
                <p><strong>Código:</strong> ${t.codigo}</p>
                <p><strong>Autor:</strong> ${t.autor}</p>
                <p><strong>Tutor:</strong> ${t.tutor}</p>
                <p><strong>Carrera:</strong> ${t.categoria_nombre}</p>
                <p><strong>Año:</strong> ${t.anio ?? '-'}</p>
                <p><strong>Palabras Claves:</strong> ${t.descripcion}</p>

                <a href="${t.link_archivo}" target="_blank"
                   class="mt-4 inline-block bg-superarse-morado-oscuro text-white px-4 py-2 rounded">
                    Ver Tesis
                </a>
            </div>
        </div>
    `;

    document.getElementById('modalTesis').classList.remove('hidden');
    document.getElementById('modalTesis').classList.add('flex');
};

window.cerrarModalTesis = function () {
    document.getElementById('modalTesis').classList.add('hidden');
    document.getElementById('modalTesis').classList.remove('flex');
};

/* ======================
   FILTRO TESIS
====================== */
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscadorTesis');
    const cards = document.querySelectorAll('.tesis-card');
    const noResults = document.getElementById('noResultsMessageTesis');
    const contador = document.getElementById('contadorResultadosTesis');

    if (!buscador) return;

    if (contador) {
        contador.innerText = `Mostrando ${cards.length} resultados`;
    }

    buscador.addEventListener('input', () => {
        const filtro = buscador.value.toLowerCase().trim();
        let visibles = 0;

        cards.forEach(card => {
            const texto =
                (card.dataset.titulo || '') +
                (card.dataset.autor || '') +
                (card.dataset.carrera || '') +
                (card.dataset.anio || '');

            const mostrar = texto.includes(filtro);
            card.style.display = mostrar ? 'block' : 'none';
            if (mostrar) visibles++;
        });

        noResults?.classList.toggle('hidden', visibles !== 0);
        if (contador) {
            contador.innerText = `Mostrando ${visibles} resultados`;
        }
    });
});

function inicialesAutorTesis(nombreCompleto) {
    return String(nombreCompleto || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .map((parte) => `${parte.charAt(0).toUpperCase()}.`)
        .join(' ');
}

function copiarTextoTesis(texto) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(texto);
    }

    const area = document.createElement('textarea');
    area.value = texto;
    document.body.appendChild(area);
    area.select();
    document.execCommand('copy');
    document.body.removeChild(area);
    return Promise.resolve();
}

window.generarCitaTesis = function (formato, id, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const t = tesis.find((item) => item.id == id);
    if (!t) return;

    const autor = t.autor || 'Autor desconocido';
    const anio = t.anio || 's.f.';
    const titulo = t.titulo || 'Sin título';
    const universidad = t.universidad || 'Universidad no registrada';

    let cita = '';
    if (String(formato).toLowerCase() === 'ieee') {
        cita = `${inicialesAutorTesis(autor)} ${autor.split(' ').slice(-1)[0]}, "${titulo}," Tesis de grado, ${universidad}, ${anio}.`;
    } else {
        cita = `${autor}. (${anio}). ${titulo} [Tesis de grado, ${universidad}].`;
    }

    copiarTextoTesis(cita)
        .then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Cita generada',
                text: 'La cita fue copiada al portapapeles.',
                confirmButtonColor: '#1b4785'
            });
        })
        .catch(() => {
            Swal.fire({
                icon: 'info',
                title: 'Cita generada',
                text: cita,
                confirmButtonColor: '#1b4785'
            });
        });
};
