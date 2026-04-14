//const BASE_URL = "";
const PUBLICACIONES_PAGE_SIZE = 30;
let categoriasGlobal = [];
let publicacionesData = [];
let publicacionesFiltered = [];
let publicacionesCurrentPage = 1;

function clearFileInput(id) {
  const input = document.getElementById(id);
  if (!input) return;

  try {
    input.value = "";
  } catch (e) {
    // Fallback para navegadores que bloquean manipulación del input file.
    const clone = input.cloneNode(true);
    input.parentNode.replaceChild(clone, input);
  }
}

function getPublicacionCoverSrc(src) {
  return src && String(src).trim() !== "" ? src : DEFAULT_COVER;
}

function setPublicacionCoverPreview(src) {
  const wrap = document.getElementById("pub_portada_preview_wrap");
  const img = document.getElementById("pub_portada_preview");

  if (!wrap || !img) return;

  if (src) {
    img.src = src;
    wrap.classList.remove("hidden");
    return;
  }

  img.removeAttribute("src");
  wrap.classList.add("hidden");
}

function bindPublicacionCoverPreview() {
  const input = document.getElementById("portada");
  if (!input) return;

  input.addEventListener("change", () => {
    const file = input.files && input.files[0];
    if (!file) {
      setPublicacionCoverPreview("");
      return;
    }

    setPublicacionCoverPreview(URL.createObjectURL(file));
  });
}

function ensurePublicacionesPaginationWrap() {
  let wrap = document.getElementById("paginacionPublicaciones");
  if (wrap) return wrap;

  const tableBody = document.getElementById("tablaPublicaciones");
  const tableContainer = tableBody?.closest(".overflow-x-auto");
  if (!tableContainer || !tableContainer.parentNode) return null;

  wrap = document.createElement("div");
  wrap.id = "paginacionPublicaciones";
  wrap.className = "mt-4 flex flex-wrap items-center justify-center gap-2";
  tableContainer.parentNode.insertBefore(wrap, tableContainer.nextSibling);
  return wrap;
}

function renderPublicacionesTable() {
  const tbody = document.getElementById("tablaPublicaciones");
  if (!tbody) return;

  const start = (publicacionesCurrentPage - 1) * PUBLICACIONES_PAGE_SIZE;
  const pageRows = publicacionesFiltered.slice(start, start + PUBLICACIONES_PAGE_SIZE);

  if (!pageRows.length) {
    tbody.innerHTML = `<tr><td colspan="11" class="text-center py-6">No hay registros</td></tr>`;
    return;
  }

  tbody.innerHTML = pageRows.map(p => {
    const cat = categoriasGlobal.find(c => c.id == p.categoria_id);
    const catNombre = cat ? cat.nombre : "N/A";

    return `
      <tr class="border-b">
        <td class="px-3 py-2">${p.codigo}</td>
        <td class="px-3 py-2">
          <div class="w-14 h-20 overflow-hidden rounded shadow">
            <img src="${getPublicacionCoverSrc(p.portada)}" class="w-full h-full object-cover">
          </div>
        </td>
        <td class="px-3 py-2">${p.titulo}</td>
        <td class="px-3 py-2">${p.autor}</td>
        <td class="px-3 py-2">${p.revista}</td>
        <td class="px-3 py-2">${p.anio}</td>
        <td class="px-3 py-2">${p.descripcion}</td>
        <td class="px-3 py-2">${catNombre}</td>
        <td class="px-3 py-2">
          <a href="${p.link_archivo}" target="_blank" class="text-blue-600 underline">Ver</a>
        </td>
        <td class="px-3 py-2">
          <span class="${p.estado === 'ACTIVO' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} px-3 py-1 rounded text-sm font-medium">
            ${p.estado}
          </span>
        </td>
        <td class="px-3 py-2">
          <button onclick="editPub(${p.id})" class="px-3 py-1 bg-yellow-500 text-white rounded">✏️</button>
          <button onclick="deletePub(${p.id})" class="px-3 py-1 bg-red-600 text-white rounded">🗑️</button>
        </td>
      </tr>`;
  }).join('');
}

function renderPublicacionesPagination() {
  const wrap = ensurePublicacionesPaginationWrap();
  if (!wrap) return;

  const totalPages = Math.ceil(publicacionesFiltered.length / PUBLICACIONES_PAGE_SIZE);
  if (totalPages <= 1) {
    wrap.innerHTML = "";
    return;
  }

  const buttons = [];
  for (let i = 1; i <= totalPages; i++) {
    buttons.push(`
      <button
        type="button"
        onclick="changePublicacionesPage(${i})"
        class="px-3 py-1.5 rounded-md border text-sm ${i === publicacionesCurrentPage
          ? 'bg-[#1b4785] text-white border-[#1b4785]'
          : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'}">
        ${i}
      </button>
    `);
  }

  wrap.innerHTML = buttons.join('');
}

function applyPublicacionesFilter() {
  const input = document.getElementById("buscarPublicacion");
  const filtro = (input?.value || "").toLowerCase().trim();

  publicacionesFiltered = publicacionesData.filter(p => {
    const titulo = (p.titulo || "").toLowerCase();
    return titulo.includes(filtro);
  });

  publicacionesCurrentPage = 1;
  renderPublicacionesTable();
  renderPublicacionesPagination();
}

function changePublicacionesPage(page) {
  const totalPages = Math.max(1, Math.ceil(publicacionesFiltered.length / PUBLICACIONES_PAGE_SIZE));
  publicacionesCurrentPage = Math.min(Math.max(1, page), totalPages);
  renderPublicacionesTable();
  renderPublicacionesPagination();
}

function loadPublicaciones() {
  fetch(`${BASE_URL}/publicaciones/indexJson`)
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        publicacionesData = [];
        publicacionesFiltered = [];
        publicacionesCurrentPage = 1;
        const tbody = document.getElementById("tablaPublicaciones");
        if (tbody) {
          tbody.innerHTML = `<tr><td colspan="11" class="text-center py-6">Error al cargar datos</td></tr>`;
        }
        renderPublicacionesPagination();
        return;
      }

      categoriasGlobal = data.categorias;

      const select = document.getElementById("categoria_id");
      select.innerHTML = `<option value="">Seleccione una categoría</option>`;
      categoriasGlobal.forEach(c => {
        select.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
      });

      publicacionesData = Array.isArray(data.publicaciones) ? data.publicaciones : [];
      applyPublicacionesFilter();
    });
}

document.addEventListener('DOMContentLoaded', () => {
  bindPublicacionCoverPreview();
  loadPublicaciones();
});

document.getElementById("buscarPublicacion").addEventListener("input", function () {
  applyPublicacionesFilter();
});

function openModal() {
  document.getElementById("formPub").reset();
  document.getElementById("id").value = "";
  clearFileInput("portada");
  clearFileInput("link_archivo");
  document.getElementById("modalTitle").innerText = "Nueva Publicación";
  document.getElementById("campoEstadoPub").classList.add("hidden");
  setPublicacionCoverPreview("");
  document.getElementById("pub_pdf_actual_wrap").classList.add("hidden");
  document.getElementById("modalPub").classList.remove("hidden");
}

function closeModal() {
  document.getElementById("modalPub").classList.add("hidden");
}

function submitPub() {
  const id = document.getElementById("id").value;
  const url = id ? "/publicaciones/updateJson" : "/publicaciones/createJson";
  const formData = new FormData();
  formData.append("id", id);
  formData.append("titulo", document.getElementById("titulo").value);
  formData.append("autor", document.getElementById("autor").value);
  formData.append("revista", document.getElementById("revista").value);
  formData.append("anio", document.getElementById("anio").value);
  formData.append("descripcion", document.getElementById("descripcion").value);
  formData.append("categoria_id", document.getElementById("categoria_id").value);

  const portadaFilePub = document.getElementById("portada").files?.[0];

  // Portada obligatoria al crear
  if (!id && !portadaFilePub) {
    Swal.fire("Campo obligatorio", "Debes seleccionar una portada para crear la publicación.", "warning");
    return;
  }

  if (portadaFilePub) {
    formData.append("portada_file", portadaFilePub);
  }

  const pdfFilePub = document.getElementById("link_archivo").files?.[0];
  if (pdfFilePub) {
    formData.append("pdf_file", pdfFilePub);
  }

  if (id && document.getElementById("pub_estado")) {
    formData.append("estado", document.getElementById("pub_estado").value);
  }

  fetch(`${BASE_URL}${url}`, {
    method: "POST",
    body: formData
  })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        Swal.fire("OK", "Guardado correctamente", "success");
        closeModal();
        loadPublicaciones();
      } else {
        Swal.fire("Error", "No se pudo guardar", "error");
      }
    });
}

function editPub(id) {
  fetch(`${BASE_URL}/publicaciones/getJson?id=` + id)
    .then(r => r.json())
    .then(resp => {
      if (!resp.success) return Swal.fire("Error", "No se encontró", "error");

      const p = resp.data;

      document.getElementById("id").value = p.id;
      clearFileInput("portada");
      document.getElementById("titulo").value = p.titulo;
      document.getElementById("autor").value = p.autor;
      document.getElementById("revista").value = p.revista;
      document.getElementById("anio").value = p.anio;
      document.getElementById("descripcion").value = p.descripcion;
      document.getElementById("categoria_id").value = p.categoria_id;
      clearFileInput("link_archivo");
      setPublicacionCoverPreview(getPublicacionCoverSrc(p.portada));

      // Mostrar enlace al PDF actual si existe
      const pdfWrapPub = document.getElementById("pub_pdf_actual_wrap");
      const pdfLinkPub = document.getElementById("pub_pdf_actual_link");
      if (p.link_archivo && pdfWrapPub && pdfLinkPub) {
        pdfLinkPub.href = p.link_archivo;
        pdfWrapPub.classList.remove("hidden");
      } else if (pdfWrapPub) {
        pdfWrapPub.classList.add("hidden");
      }

      if (p.estado) {
        document.getElementById("pub_estado").value = p.estado;
        document.getElementById("campoEstadoPub").classList.remove("hidden");
      }

      document.getElementById("modalTitle").innerText = "Editar Publicación";
      document.getElementById("modalPub").classList.remove("hidden");
    });
}

function deletePub(id) {
  Swal.fire({
    title: "¿Eliminar Publicación?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí"
  }).then(r => {
    if (!r.isConfirmed) return;

    fetch(`${BASE_URL}/publicaciones/deleteJson`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    })
      .then(r => r.json())
      .then(resp => {
        if (resp.success) {
          Swal.fire("Eliminado", "Correcto", "success");
          loadPublicaciones();
        }
      });
  });
}
