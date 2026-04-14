/* ============================================================================
                                CARGAR LIBROS
============================================================================= */
function getLibroCoverSrc(src) {
  return src && String(src).trim() !== "" ? src : DEFAULT_COVER;
}

function setLibroCoverPreview(src) {
  const wrap = document.getElementById("libro_portada_preview_wrap");
  const img = document.getElementById("libro_portada_preview");

  if (!wrap || !img) return;

  if (src) {
    img.src = src;
    wrap.classList.remove("hidden");
    return;
  }

  img.removeAttribute("src");
  wrap.classList.add("hidden");
}

function bindLibroCoverPreview() {
  const input = document.getElementById("libro_portada");
  if (!input) return;

  input.addEventListener("change", () => {
    const file = input.files && input.files[0];
    if (!file) {
      setLibroCoverPreview("");
      return;
    }

    setLibroCoverPreview(URL.createObjectURL(file));
  });
}

function loadLibros() {
  fetch(`${BASE_URL}/libros/indexJson`)
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById("tablaLibros");
      tbody.innerHTML = "";

/* =============================================================================
                        1️⃣ SIEMPRE cargar categorías
==============================================================================*/
      const selectCat = document.getElementById("libro_categoria");
      selectCat.innerHTML = `<option value="">Seleccione categoría</option>`;

      if (data.categorias && data.categorias.length) {
        data.categorias.forEach(c => {
          selectCat.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
        });
      }

/* =============================================================================
                         2️⃣ Luego validar libros
==============================================================================*/
      if (!data.success || !data.libros.length) {
        tbody.innerHTML =
          `<tr><td colspan="10" class="text-center py-6">No hay libros registrados</td></tr>`;
        return;
      }

/* =============================================================================
                         3️⃣ Renderizar libros
==============================================================================*/
      tbody.innerHTML = data.libros.map(l => `
          <tr class="border-b">
            <td class="px-3 py-2">${l.codigo}</td>
            <td class="px-3 py-2">
              <img src="${getLibroCoverSrc(l.portada)}" class="w-14 h-20 object-cover rounded shadow">
            </td>
            <td class="px-3 py-2">${l.titulo}</td>
            <td class="px-3 py-2">${l.autor}</td>
            <td class="px-3 py-2">${l.edicion}</td>
            <td class="px-3 py-2">${l.categoria_nombre}</td>
            <td class="px-3 py-2">${l.anio}</td>
            <td class="px-3 py-2">${l.numero_ejemplares}</td>
            <td class="px-3 py-2">${l.stock}</td>
            <td class="px-3 py-2">${l.revista}</td>
            <td class="px-3 py-2">${l.ubicacion}</td>
            <td class="px-3 py-2">
              <span class="${l.estado === 'ACTIVO'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700'} px-3 py-1 rounded text-sm font-medium">
                ${l.estado}
              </span>
            </td>
            <td class="px-3 py-2">
              <button onclick="editLibro(${l.id})" class="px-3 py-1 bg-yellow-500 text-white rounded">✏️</button>
              <button onclick="deleteLibro(${l.id})" class="px-3 py-1 bg-red-600 text-white rounded">🗑️</button>
            </td>
          </tr>
        `).join('');
    });
}

document.addEventListener('DOMContentLoaded', () => {
  bindLibroCoverPreview();
  loadLibros();
});

document.getElementById("buscarLibro").addEventListener("input", function() {
  const filtro = this.value.toLowerCase();
  const filas = document.querySelectorAll("#tablaLibros tr");

  filas.forEach(fila => {
    const titulo = fila.cells[2]?.textContent.toLowerCase() || "";
    fila.style.display = titulo.includes(filtro) ? "" : "none";
  });
});

/* =============================================================================
                                 ABRIR / CERRAR MODAL
============================================================================= */
function openModalLibro() {
  document.getElementById("formLibro").reset();
  document.getElementById("libro_id").value = "";
  document.getElementById("modalLibroTitle").innerText = "Nuevo Libro";
  document.getElementById("campoEstadoLibro").classList.add("hidden");
  document.getElementById("libro_stock").readOnly = false;
  setLibroCoverPreview("");

  const modal = document.getElementById("modalLibro");
  const card = document.getElementById("modalCard");

  modal.classList.remove("hidden");

  setTimeout(() => {
    card.classList.remove("opacity-0", "scale-90");
  }, 10);
}

function closeModalLibro() {
  const card = document.getElementById("modalCard");
  const modal = document.getElementById("modalLibro");

  card.classList.add("opacity-0", "scale-90");
  setTimeout(() => modal.classList.add("hidden"), 180);
}

/* =============================================================================
                            GUARDAR (NUEVO/EDITAR)
============================================================================= */
function submitLibro() {
  const id = document.getElementById("libro_id").value;
  const url = id ? "/libros/updateJson" : "/libros/createJson";
  const formData = new FormData();
  formData.append("id", id);
  formData.append("codigo", libro_codigo.value);
  formData.append("titulo", libro_titulo.value);
  formData.append("autor", libro_autor.value);
  formData.append("edicion", libro_edicion.value);
  formData.append("revista", libro_revista.value);
  formData.append("codigo_barra", libro_codigo_barra.value);
  formData.append("categoria_id", libro_categoria.value);
  formData.append("anio", libro_anio.value);
  formData.append("numero_ejemplares", libro_numero_ejemplares.value);
  formData.append("stock", libro_stock.value);
  formData.append("ubicacion", libro_ubicacion.value);
  formData.append("descripcion", libro_descripcion.value);

  const portadaFile = document.getElementById("libro_portada").files?.[0];

  // Portada obligatoria al crear
  if (!id && !portadaFile) {
    Swal.fire("Campo obligatorio", "Debes seleccionar una portada para crear el libro.", "warning");
    return;
  }

  if (portadaFile) {
    formData.append("portada_file", portadaFile);
  }

  if (id && document.getElementById("libro_estado")) {
    formData.append("estado", document.getElementById("libro_estado").value);
  }

  const doSubmit = () => {
    fetch(`${BASE_URL}${url}`, {
      method: "POST",
      body: formData
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        Swal.fire("Guardado", "El libro se registró correctamente", "success");
        closeModalLibro();
        loadLibros();
      } else {
        Swal.fire("Error", resp.message || "No se pudo guardar", "error");
      }
    });
  };

  if (id) {
    Swal.fire({
      title: "Confirmar edición",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Guardar"
    }).then(r => r.isConfirmed && doSubmit());
  } else {
    doSubmit();
  }
}

/* =============================================================================
                                        EDITAR
============================================================================= */
function editLibro(id) {
  fetch(`${BASE_URL}/libros/indexJson`)
    .then(r => r.json())
    .then(resp => {
      const libro = resp.libros.find(l => l.id == id);
      if (!libro) return Swal.fire("Error", "No encontrado", "error");

      openModalLibro();

      libro_id.value = libro.id;
      libro_codigo.value = libro.codigo;
      libro_portada.value = "";
      libro_titulo.value = libro.titulo;
      libro_autor.value = libro.autor;
      libro_edicion.value = libro.edicion;
      libro_revista.value = libro.revista;
      libro_codigo_barra.value = libro.codigo_barra;
      libro_categoria.value = libro.categoria_id;
      libro_anio.value = libro.anio;
      libro_numero_ejemplares.value = libro.numero_ejemplares;
      libro_stock.value = libro.stock;
      libro_ubicacion.value = libro.ubicacion;
      libro_descripcion.value = libro.descripcion;
      setLibroCoverPreview(getLibroCoverSrc(libro.portada));

      if (libro.estado) {
        libro_estado.value = libro.estado;
        campoEstadoLibro.classList.remove("hidden");
      }

      modalLibroTitle.innerText = "Editar Libro";
    });
}

/* =============================================================================
                                 ELIMINAR
============================================================================ */
function deleteLibro(id) {
  Swal.fire({
    title: "¿Eliminar libro?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar"
  }).then(r => {
    if (r.isConfirmed) {
      fetch(`${BASE_URL}/libros/deleteJson`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id })
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.success) {
          Swal.fire("Eliminado", "Libro eliminado", "success");
          loadLibros();
        }
      });
    }
  });
}
