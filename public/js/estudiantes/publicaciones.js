const APP_BASE_URL = window.APP?.BASE_URL || window.BASE_URL || '';
const publicaciones = window.APP?.publicaciones || [];

const PAGESIZE_PUBLICACIONES = 30;
let filteredPublicaciones = [];
let currentPublicacionesPage = 1;

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
   RENDERIZAR TABLA PUBLICACIONES
========================== */
function renderPublicacionesTable() {
    const grid = document.getElementById('gridPublicaciones');
    if (!grid) return;
    
    const start = (currentPublicacionesPage - 1) * PAGESIZE_PUBLICACIONES;
    const end = start + PAGESIZE_PUBLICACIONES;
    const page = filteredPublicaciones.slice(start, end);
    
    grid.innerHTML = '';
    
    page.forEach(p => {
        const div = document.createElement('div');
        div.className = 'bg-white rounded-xl shadow-lg hover:shadow-2xl transition cursor-pointer';
        div.onclick = () => abrirModal(p.id);
        div.innerHTML = `
            <img src="${htmlEscapePublicaciones(p.portada)}" class="w-full h-64 object-cover">
            <div class="p-4">
                <h3 class="font-bold text-lg">${htmlEscapePublicaciones(p.titulo)}</h3>
                <p class="text-gray-600">${htmlEscapePublicaciones(p.autor)}</p>
                <p class="text-gray-500 text-sm">${htmlEscapePublicaciones(p.categoria_nombre)}</p>
                <div class="grid grid-cols-2 gap-2 text-sm mt-2 text-center">
                    <div class="bg-superarse-amarillo text-white rounded p-1">
                        Año: ${htmlEscapePublicaciones(p.anio ?? '-')}
                    </div>
                    <div class="bg-superarse-morado-medio text-white rounded p-1">
                        Revista: ${htmlEscapePublicaciones(p.revista)}
                    </div>
                    <div class="col-span-2 bg-superarse-rosa text-white rounded p-1">
                        Código: ${htmlEscapePublicaciones(p.codigo)}
                    </div>
                    <div class="col-span-2 bg-blue-500 text-white rounded p-1 mt-2">
                        👁️ Visitas: <span id="visitasPublicacion-${p.id}">${p.visitas}</span>
                    </div>
                    <div class="col-span-2 grid grid-cols-2 gap-2 mt-1">
                        <button type="button"
                                class="bg-[#1b4785] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaPublicacion('apa7', ${p.id}, event)">
                            Cita APA 7
                        </button>
                        <button type="button"
                                class="bg-[#164c7e] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaPublicacion('ieee', ${p.id}, event)">
                            Cita IEEE
                        </button>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(div);
    });
}

function htmlEscapePublicaciones(s) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(s || '').replace(/[&<>"']/g, c => map[c]);
}

/* ==========================
   RENDERIZAR PAGINACIÓN PUBLICACIONES
========================== */
function renderPublicacionesPagination() {
    const paginationDiv = document.getElementById('paginationPublicaciones');
    if (!paginationDiv) return;
    
    const totalPages = Math.ceil(filteredPublicaciones.length / PAGESIZE_PUBLICACIONES);
    paginationDiv.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    const nav = document.createElement('nav');
    nav.className = 'flex justify-center gap-2 my-6';
    
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = `px-3 py-1 rounded border ${i === currentPublicacionesPage ? 'bg-superarse-morado-oscuro text-white' : 'border-gray-300 hover:bg-gray-100'}`;
        btn.onclick = () => changePublicacionesPage(i);
        nav.appendChild(btn);
    }
    
    paginationDiv.appendChild(nav);
}

window.changePublicacionesPage = function (page) {
    currentPublicacionesPage = page;
    renderPublicacionesTable();
    renderPublicacionesPagination();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

/* ==========================
   FILTRO PUBLICACIONES
========================== */
window.filtrarPublicaciones = function () {
    const texto = document.getElementById("buscador").value.toLowerCase();
    
    filteredPublicaciones = publicaciones.filter(p =>
        p.titulo.toLowerCase().includes(texto) ||
        p.autor.toLowerCase().includes(texto) ||
        String(p.anio ?? '').includes(texto)
    );
    
    currentPublicacionesPage = 1;
    renderPublicacionesTable();
    renderPublicacionesPagination();
};

/* ==========================
   INICIALIZAR PUBLICACIONES (Auto-init)
========================== */
function initPublicacionesPage() {
    const buscador = document.getElementById('buscador');
    const grid = document.getElementById('gridPublicaciones');
    
    if (!grid) {
        console.warn('[publicaciones.js] Grid no encontrado. Reintentando en 100ms...');
        setTimeout(initPublicacionesPage, 100);
        return;
    }
    
    filteredPublicaciones = publicaciones || [];
    renderPublicacionesTable();
    renderPublicacionesPagination();
    
    if (buscador) {
        buscador.addEventListener('keyup', () => filtrarPublicaciones());
    }
}

// Ejecuta cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPublicacionesPage);
} else {
    // DOM ya está cargado (script ejecutado tardíamente)
    initPublicacionesPage();
}

function inicialesAutorPublicacion(nombreCompleto) {
    return String(nombreCompleto || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .map((parte) => `${parte.charAt(0).toUpperCase()}.`)
        .join(' ');
}

function copiarTextoPublicacion(texto) {
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

window.generarCitaPublicacion = function (formato, id, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const p = publicaciones.find((item) => item.id == id);
    if (!p) return;

    const autor = p.autor || 'Autor desconocido';
    const anio = p.anio || 's.f.';
    const titulo = p.titulo || 'Sin titulo';
    const revista = p.revista || 'Revista no registrada';

    let cita = '';
    if (String(formato).toLowerCase() === 'ieee') {
        cita = `${inicialesAutorPublicacion(autor)} ${autor.split(' ').slice(-1)[0]}, "${titulo}," ${revista}, ${anio}.`;
    } else {
        cita = `${autor}. (${anio}). ${titulo}. ${revista}.`;
    }

    copiarTextoPublicacion(cita)
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
