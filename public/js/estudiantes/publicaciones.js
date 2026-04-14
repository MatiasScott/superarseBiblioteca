const APP_BASE_URL = window.APP?.BASE_URL || window.BASE_URL || '';
const publicaciones = window.APP?.publicaciones || [];

/* ==========================
   MODAL PUBLICACIÓN
========================== */
window.abrirModal = function (id) {
    const p = publicaciones.find(item => item.id == id);
    if (!p) return;

    fetch(`${APP_BASE_URL}/publicaciones/sumarVisita/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                 document.getElementById(`visitasPublicacion-${id}`).innerText = data.visitas;
                p.visitas = data.visitas; // actualizar local
            }
        });

    document.getElementById('modalContent').innerHTML = `
        <div class="flex flex-col md:flex-row gap-4">
            <img src="${p.portada}" class="w-full md:w-64 h-64 object-cover rounded">
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-superarse-morado-oscuro mb-2">${p.titulo}</h2>
                <p><strong>Autor:</strong> ${p.autor}</p>
                <p><strong>Revista:</strong> ${p.revista}</p>
                <p><strong>Categoría:</strong> ${p.categoria_nombre}</p>
                <p><strong>Año:</strong> ${p.anio ?? '-'}</p>
                <p><strong>Descripción:</strong> ${p.descripcion}</p>

                <a href="${p.link_archivo}" target="_blank"
                   class="mt-4 inline-block bg-superarse-morado-oscuro text-white px-4 py-2 rounded">
                    Ver Publicación
                </a>
            </div>
        </div>
    `;

    document.getElementById('modalPublicacion').classList.remove('hidden');
    document.getElementById('modalPublicacion').classList.add('flex');
};

window.cerrarModalPublicacion = function () {
    document.getElementById('modalPublicacion').classList.add('hidden');
    document.getElementById('modalPublicacion').classList.remove('flex');
};

/* ==========================
   FILTRO PUBLICACIONES
========================== */
window.filtrarPublicaciones = function () {
    const texto = document.getElementById("buscador").value.toLowerCase();
    const contenedor = document.getElementById("gridPublicaciones");
    const contador = document.getElementById('contadorResultadosPublicaciones');
    let visibles = 0;

    contenedor.innerHTML = "";

    publicaciones
        .filter(p =>
            p.titulo.toLowerCase().includes(texto) ||
            p.autor.toLowerCase().includes(texto) ||
            String(p.anio ?? '').includes(texto)
        )
        .forEach(p => {
            contenedor.innerHTML += `
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition cursor-pointer"
                     onclick="abrirModal(${p.id})">

                    <img src="${p.portada}" class="w-full h-64 object-cover">

                    <div class="p-4">
                        <h3 class="font-bold text-lg">${p.titulo}</h3>
                        <p class="text-gray-600">${p.autor}</p>
                        <p class="text-gray-500 text-sm">${p.categoria_nombre}</p>

                        <div class="grid grid-cols-2 gap-2 text-sm mt-2 text-center">
                            <div class="bg-superarse-amarillo text-white rounded p-1">
                                Año: ${p.anio ?? '-'}
                            </div>
                            <div class="bg-superarse-morado-medio text-white rounded p-1">
                                Revista: ${p.revista}
                            </div>
                            <div class="col-span-2 bg-superarse-rosa text-white rounded p-1">
                                Código: ${p.codigo}
                            </div>
                            <div class="col-span-2 bg-blue-500 text-white rounded p-1 mt-2">
                                👁️ Visitas:
                                <span id="visitasPublicacion-${p.id}">
                                    ${p.visitas}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            visibles++;
        });

    document.getElementById('noResultsMessagePublicaciones')
        ?.classList.toggle('hidden', visibles !== 0);

    if (contador) {
        contador.innerText = `Mostrando ${visibles} resultados`;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const contador = document.getElementById('contadorResultadosPublicaciones');
    if (!contador) return;

    contador.innerText = `Mostrando ${publicaciones.length} resultados`;
});
