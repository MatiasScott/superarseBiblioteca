const APP_BASE_URL = window.APP?.BASE_URL || window.BASE_URL || '';
const libros = window.APP?.libros || [];
const usuarioLogueado = !!window.APP?.usuarioLogueado;

const PAGESIZE_LIBROS = 30;
let filteredLibros = [];
let currentLibrosPage = 1;

/* ======================
   MODAL BONITO ALERTA
====================== */
let accionAlerta = null;

window.mostrarAlerta = function (titulo, mensaje, accion = null) {
    document.getElementById("alertaTitulo").innerText = titulo;
    document.getElementById("alertaMensaje").innerText = mensaje;
    accionAlerta = accion;

    const modal = document.getElementById("modalAlerta");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
};

window.cerrarAlerta = function () {
    const modal = document.getElementById("modalAlerta");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    accionAlerta = null;
};

window.aceptarAlerta = function () {
    if (typeof accionAlerta === "function") accionAlerta();
    cerrarAlerta();
};

/* ======================
   MODAL DE LIBRO
====================== */
window.abrirModal = function (id) {
    const libro = libros.find(l => l.id == id);
    if (!libro) return;

    fetch(`${APP_BASE_URL}/libros/sumarVisita/${id}`, { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                libro.visitas = data.visitas;
                const v = document.getElementById(`visitasLibro-${id}`);
                if (v) v.innerText = data.visitas;
            }
        });

    const btnPrestamo = libro.stock > 0
        ? `<button onclick="solicitarPrestamo(${libro.id})"
            class="mt-4 bg-superarse-morado-oscuro text-white px-4 py-2 rounded">
            Solicitar Préstamo</button>`
        : `<button disabled
            class="mt-4 bg-gray-400 text-white px-4 py-2 rounded">
            ❌ No hay ejemplares disponibles</button>`;

    document.getElementById('modalContent').innerHTML = `
        <div class="flex gap-4">
            <img src="${libro.portada}" class="w-64 h-64 object-cover rounded">
            <div>
                 <p><strong>Autor:</strong> ${libro.autor}</p>
                <p><strong>Categoría:</strong> ${libro.categoria_nombre}</p>
                <p><strong>Año:</strong> ${libro.anio}</p>
                <p><strong>Edición:</strong> ${libro.edicion}</p>
                <p><strong>Descripción:</strong> ${libro.descripcion}</p>
                <p><strong>Total ejemplares:</strong> ${libro.numero_ejemplares}</p>
                <p><strong>Stock:</strong> ${libro.stock}</p>
                <p><strong>Código:</strong> ${libro.codigo}</p>
                ${btnPrestamo}
            </div>
        </div>
    `;

    document.getElementById('modalLibro').classList.remove('hidden');
    document.getElementById('modalLibro').classList.add('flex');
};

window.cerrarModal = function () {
    document.getElementById('modalLibro').classList.add('hidden');
    document.getElementById('modalLibro').classList.remove('flex');
};

/* ======================
   SOLICITAR PRÉSTAMO
====================== */
window.solicitarPrestamo = function (id) {
    if (!usuarioLogueado) {
        mostrarAlerta(
            "Inicia sesión",
            "Debes iniciar sesión para solicitar un préstamo. Si no tienes credenciales comunícate con nathaly.ortiz@superarse.edu.ec",
            () => window.location.href = `${APP_BASE_URL}/login`
        );
        return;
    }

    fetch(`${APP_BASE_URL}/solicitudes/crear`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `item_id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            mostrarAlerta("Solicitud enviada", "El administrador la revisará");
            cerrarModal();
        } else {
            mostrarAlerta("Error", data.msg);
        }
    });
};

/* ======================
   RENDERIZAR TABLA LIBROS
====================== */
function renderLibrosTable() {
    const grid = document.getElementById('gridLibros');
    if (!grid) return;
    
    const start = (currentLibrosPage - 1) * PAGESIZE_LIBROS;
    const end = start + PAGESIZE_LIBROS;
    const page = filteredLibros.slice(start, end);
    
    grid.innerHTML = '';
    
    page.forEach(libro => {
        const div = document.createElement('div');
        div.className = 'bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition cursor-pointer flex flex-col';
        div.onclick = () => abrirModal(libro.id);
        div.innerHTML = `
            <img src="${htmlEscapeLibros(libro.portada)}" alt="${htmlEscapeLibros(libro.titulo)}" class="w-full h-52 sm:h-56 md:h-60 object-cover">
            <div class="p-4 flex flex-col flex-grow">
                <h3 class="font-bold text-base sm:text-lg text-superarse-morado-oscuro line-clamp-2">
                    ${htmlEscapeLibros(libro.titulo)}
                </h3>
                <p class="text-gray-600 text-xs sm:text-sm mt-1">
                    Autor: ${htmlEscapeLibros(libro.autor)}
                </p>
                <p class="text-gray-500 text-xs sm:text-sm mb-3">
                    Categoria: ${htmlEscapeLibros(libro.categoria_nombre)}
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm mt-auto">
                    <div class="bg-blue-500 text-white rounded-lg py-1 text-center">
                        Ubicación: ${htmlEscapeLibros(libro.ubicacion)}
                    </div>
                    <div class="bg-blue-500 text-white rounded-lg py-1 text-center">
                        Edición: ${htmlEscapeLibros(libro.edicion)}
                    </div>
                    <div class="bg-green-500 text-white rounded-lg py-1 text-center">
                        Stock: ${libro.stock}
                    </div>
                    <div class="bg-green-500 text-white rounded-lg py-1 text-center">
                        Código: ${htmlEscapeLibros(libro.codigo)}
                    </div>
                    <div class="col-span-2 bg-orange-500 text-white rounded-lg py-1 text-center mt-1">
                        👁️ Visitas: <span id="visitasLibro-${libro.id}">${libro.visitas}</span>
                    </div>
                    <div class="col-span-2 grid grid-cols-2 gap-2 mt-1">
                        <button type="button"
                                class="bg-[#1b4785] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaLibro('apa7', ${libro.id}, event)">
                            Cita APA 7
                        </button>
                        <button type="button"
                                class="bg-[#164c7e] text-white rounded-lg py-1 text-center hover:bg-[#479990] transition"
                                onclick="generarCitaLibro('ieee', ${libro.id}, event)">
                            Cita IEEE
                        </button>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(div);
    });
}

function htmlEscapeLibros(s) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(s || '').replace(/[&<>"']/g, c => map[c]);
}

/* ======================
   RENDERIZAR PAGINACIÓN LIBROS
====================== */
function renderLibrosPagination() {
    const paginationDiv = document.getElementById('paginationLibros');
    if (!paginationDiv) return;
    
    const totalPages = Math.ceil(filteredLibros.length / PAGESIZE_LIBROS);
    paginationDiv.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    const nav = document.createElement('nav');
    nav.className = 'flex justify-center gap-2 my-6';
    
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = `px-3 py-1 rounded border ${i === currentLibrosPage ? 'bg-superarse-morado-oscuro text-white' : 'border-gray-300 hover:bg-gray-100'}`;
        btn.onclick = () => changeLibrosPage(i);
        nav.appendChild(btn);
    }
    
    paginationDiv.appendChild(nav);
}

window.changeLibrosPage = function (page) {
    currentLibrosPage = page;
    renderLibrosTable();
    renderLibrosPagination();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

/* ======================
   APLICAR FILTRO LIBROS
====================== */
window.applyLibrosFilter = function () {
    const buscador = document.getElementById('buscador');
    const filtro = buscador ? buscador.value.toLowerCase().trim() : '';
    
    filteredLibros = libros.filter(libro => {
        const texto = (libro.titulo || '') + (libro.autor || '') + (libro.categoria_nombre || '');
        return texto.toLowerCase().includes(filtro);
    });
    
    currentLibrosPage = 1;
    renderLibrosTable();
    renderLibrosPagination();
};

/* ======================
   FILTRAR LIBROS (Auto-init)
====================== */
function initLibrosPage() {
    const buscador = document.getElementById('buscador');
    const grid = document.getElementById('gridLibros');
    
    if (!grid) {
        console.warn('[libros.js] Grid no encontrado. Reintentando en 100ms...');
        setTimeout(initLibrosPage, 100);
        return;
    }
    
    filteredLibros = libros || [];
    renderLibrosTable();
    renderLibrosPagination();
    
    if (buscador) {
        buscador.addEventListener('keyup', () => applyLibrosFilter());
    }
}

// Ejecuta cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLibrosPage);
} else {
    // DOM ya está cargado (script ejecutado tardíamente)
    initLibrosPage();
}

function inicialesAutor(nombreCompleto) {
    return String(nombreCompleto || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .map((parte) => `${parte.charAt(0).toUpperCase()}.`)
        .join(' ');
}

function copiarTexto(texto) {
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

window.generarCitaLibro = function (formato, id, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const libro = libros.find((l) => l.id == id);
    if (!libro) return;

    const autor = libro.autor || 'Autor desconocido';
    const anio = libro.anio || 's.f.';
    const titulo = libro.titulo || 'Sin título';
    const edicion = libro.edicion || 's. ed.';
    const editorial = libro.revista || 'Editorial no registrada';

    let cita = '';
    if (String(formato).toLowerCase() === 'ieee') {
        cita = `${inicialesAutor(autor)} ${autor.split(' ').slice(-1)[0]}, "${titulo}", ${edicion} ed., ${editorial}, ${anio}.`;
    } else {
        cita = `${autor}. (${anio}). ${titulo} (${edicion}). ${editorial}.`;
    }

    copiarTexto(cita)
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
