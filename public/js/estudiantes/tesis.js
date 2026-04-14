const APP_BASE_URL = window.APP?.BASE_URL || window.BASE_URL || '';
const tesis = window.APP.tesis || [];

const PAGESIZE_TESIS = 30;
let filteredTesis = [];
let currentTesisPage = 1;

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
   RENDERIZAR TABLA TESIS
====================== */
function renderTesisTable() {
    const grid = document.getElementById('gridTesis');
    if (!grid) return;
    
    const start = (currentTesisPage - 1) * PAGESIZE_TESIS;
    const end = start + PAGESIZE_TESIS;
    const page = filteredTesis.slice(start, end);
    
    grid.innerHTML = '';
    
    page.forEach(t => {
        const div = document.createElement('div');
        div.className = 'bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition cursor-pointer flex flex-col';
        div.onclick = () => abrirModal(t.id);
        div.innerHTML = `
            <img src="${htmlEscapeTesis(t.portada)}" alt="${htmlEscapeTesis(t.codigo)}" class="w-full h-52 sm:h-56 md:h-60 object-cover">
            <div class="p-4 flex flex-col flex-grow">
                <h3 class="font-bold text-base sm:text-lg text-superarse-morado-oscuro line-clamp-2">
                    ${htmlEscapeTesis(t.titulo)}
                </h3>
                <p class="text-gray-600 text-xs sm:text-sm mt-1">
                    Autor: ${htmlEscapeTesis(t.autor)}
                </p>
                <p class="text-gray-600 text-xs sm:text-sm">
                    Tutor: ${htmlEscapeTesis(t.tutor)}
                </p>
                <p class="text-gray-600 text-xs sm:text-sm">
                    Carrera: ${htmlEscapeTesis(t.categoria_nombre)}
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm mt-3">
                    <div class="bg-blue-500 text-white rounded-lg py-1 text-center">
                        Año: ${htmlEscapeTesis(t.anio ?? '-')}
                    </div>
                    <div class="bg-green-500 text-white rounded-lg py-1 text-center">
                        Código: ${htmlEscapeTesis(t.codigo)}
                    </div>
                    <div class="col-span-2 bg-orange-500 text-white rounded-lg py-1 text-center mt-1">
                        👁️ Visitas: <span id="VisitasTesis-${t.id}">${t.visitas}</span>
                    </div>
                    <div class="col-span-2 grid grid-cols-2 gap-2 mt-1">
                        <button type="button"
                                class="bg-[#1b4785] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaTesis('apa7', ${t.id}, event)">
                            Cita APA 7
                        </button>
                        <button type="button"
                                class="bg-[#164c7e] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaTesis('ieee', ${t.id}, event)">
                            Cita IEEE
                        </button>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(div);
    });
}

function htmlEscapeTesis(s) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(s || '').replace(/[&<>"']/g, c => map[c]);
}

/* ======================
   RENDERIZAR PAGINACIÓN TESIS
====================== */
function renderTesisPagination() {
    const paginationDiv = document.getElementById('paginationTesis');
    if (!paginationDiv) return;
    
    const totalPages = Math.ceil(filteredTesis.length / PAGESIZE_TESIS);
    paginationDiv.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    const nav = document.createElement('nav');
    nav.className = 'flex justify-center gap-2 my-6';
    
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = `px-3 py-1 rounded border ${i === currentTesisPage ? 'bg-superarse-morado-oscuro text-white' : 'border-gray-300 hover:bg-gray-100'}`;
        btn.onclick = () => changeTesisPage(i);
        nav.appendChild(btn);
    }
    
    paginationDiv.appendChild(nav);
}

window.changeTesisPage = function (page) {
    currentTesisPage = page;
    renderTesisTable();
    renderTesisPagination();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

/* ======================
   APLICAR FILTRO TESIS
====================== */
window.applyTesisFilter = function () {
    const buscador = document.getElementById('buscadorTesis');
    const filtro = buscador ? buscador.value.toLowerCase().trim() : '';
    
    filteredTesis = tesis.filter(t => {
        const texto = (t.titulo || '') + (t.autor || '') + (t.categoria_nombre || '') + (t.anio || '');
        return texto.toLowerCase().includes(filtro);
    });
    
    currentTesisPage = 1;
    renderTesisTable();
    renderTesisPagination();
};

/* ======================
   FILTRO TESIS (Auto-init)
====================== */
function initTesisPage() {
    const buscador = document.getElementById('buscadorTesis');
    const grid = document.getElementById('gridTesis');
    
    if (!grid) {
        console.warn('[tesis.js] Grid no encontrado. Reintentando en 100ms...');
        setTimeout(initTesisPage, 100);
        return;
    }
    
    filteredTesis = tesis || [];
    renderTesisTable();
    renderTesisPagination();
    
    if (buscador) {
        buscador.addEventListener('input', () => applyTesisFilter());
    }
}

// Ejecuta cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTesisPage);
} else {
    // DOM ya está cargado (script ejecutado tardíamente)
    initTesisPage();
}

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
