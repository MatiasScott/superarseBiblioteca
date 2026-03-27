const { libros, tesis, publicaciones } = window.INICIO_DATA;
const BASE_URL = window.APP.BASE_URL;

const inputBuscador = document.querySelector("#buscadorGlobal");
const contenedor = document.querySelector("#contenedorResultados");
const lista = document.querySelector("#listaResultados");

function buscarGlobal(texto) {
    texto = texto.toLowerCase();
    let resultados = [];

    // 📘 Libros
    libros.forEach(l => {
        if (l.titulo.toLowerCase().includes(texto)) {
            resultados.push({
                tipo: "Libro",
                titulo: l.titulo,
                autor: l.autor,
                url: `${BASE_URL}/libros/catalogo`
            });
        }
    });

    // 📗 Tesis
    tesis.forEach(t => {
        if (t.titulo.toLowerCase().includes(texto)) {
            resultados.push({
                tipo: "Tesis",
                titulo: t.titulo,
                autor: t.autor,
                carrera: t.categoria_nombre,
                url: `${BASE_URL}/tesis`
            });
        }
    });

    // 📙 Publicaciones
    publicaciones.forEach(p => {
        if (p.titulo.toLowerCase().includes(texto)) {
            resultados.push({
                tipo: "Publicación",
                titulo: p.titulo,
                autor: p.autor,
                url: `${BASE_URL}/publicaciones`
            });
        }
    });

    mostrarResultados(resultados);
}

function mostrarResultados(data) {
    lista.innerHTML = "";

    if (data.length === 0) {
        contenedor.classList.add("hidden");
        return;
    }

    contenedor.classList.remove("hidden");

    data.forEach(item => {
        lista.innerHTML += `
            <a href="${item.url}" class="p-4 bg-white/80 rounded-xl shadow hover:scale-105 transition block">
                <h3 class="text-lg font-bold">${item.titulo}</h3>
                <p class="text-sm"><strong>Tipo:</strong> ${item.tipo}</p>
                <p class="text-sm"><strong>Autor:</strong> ${item.autor}</p>
                ${item.carrera ? `<p class="text-sm"><strong>Carrera:</strong> ${item.carrera}</p>` : ""}
            </a>
        `;
    });
}

inputBuscador.addEventListener("input", function () {
    const texto = this.value.trim();

    if (texto.length >= 2) {
        buscarGlobal(texto);
    } else {
        contenedor.classList.add("hidden");
    }
});
