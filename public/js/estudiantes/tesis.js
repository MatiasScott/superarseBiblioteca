const { BASE_URL } = window.APP;
const tesis = window.APP.tesis || [];

/* ======================
   MODAL TESIS
====================== */
window.abrirModal = function (id) {
    const t = tesis.find(item => item.id == id);
    if (!t) return;

    // Incrementar visitas
    
          fetch(`${BASE_URL}/tesis/sumarVisita/${id}`, { method: 'POST' })
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

    if (!buscador) return;

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
    });
});
