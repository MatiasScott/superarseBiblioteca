const APP_BASE_URL = window.APP?.BASE_URL || window.BASE_URL || '';
const libros = window.APP?.libros || [];
const usuarioLogueado = !!window.APP?.usuarioLogueado;

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
   FILTRAR LIBROS
====================== */
window.filtrarLibros = function () {
    const input = document.getElementById('buscador').value.toLowerCase();
    const contador = document.getElementById('contadorResultadosLibros');
    let visibles = 0;

    document.querySelectorAll('#gridLibros > div').forEach(div => {
        const texto = div.innerText.toLowerCase();
        const mostrar = texto.includes(input);
        div.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });

    document.getElementById('noResultsMessage')
        ?.classList.toggle('hidden', visibles !== 0);

    if (contador) {
        contador.innerText = `Mostrando ${visibles} resultados`;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const contador = document.getElementById('contadorResultadosLibros');
    if (!contador) return;

    const total = document.querySelectorAll('#gridLibros > div').length;
    contador.innerText = `Mostrando ${total} resultados`;
});

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
