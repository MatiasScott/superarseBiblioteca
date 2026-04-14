// Simple helper para validar campos y mostrar errores
const TESIS_PAGE_SIZE = 30;
let tesisData = [];
let tesisFiltered = [];
let tesisCurrentPage = 1;

function validarCamposTesis(data) {
  const errores = [];
  if (!data.titulo || data.titulo.trim() === "") errores.push("Título");
  if (!data.autor || data.autor.trim() === "") errores.push("Autor");
  if (!data.categoria_id || data.categoria_id == "") errores.push("Categoría");
  return errores;
}

// confetti helper (usa canvas-confetti CDN)
function lanzarConfetti() {
  if (typeof confetti === "function") {
    confetti({
      particleCount: 80,
      spread: 60,
      origin: { y: 0.6 }
    });
  }
}

function setTesisCoverPreview(src) {
  const wrap = document.getElementById("tesis_portada_preview_wrap");
  const img = document.getElementById("tesis_portada_preview");

  if (!wrap || !img) return;

  if (src) {
    img.src = src;
    wrap.classList.remove("hidden");
    return;
  }

  img.removeAttribute("src");
  wrap.classList.add("hidden");
}

function bindTesisCoverPreview() {
  const input = document.getElementById("tesis_portada");
  if (!input) return;

  input.addEventListener("change", () => {
    const file = input.files && input.files[0];
    if (!file) {
      setTesisCoverPreview("");
      return;
    }

    setTesisCoverPreview(URL.createObjectURL(file));
  });
}

function ensureTesisPaginationWrap() {
  let wrap = document.getElementById("paginacionTesis");
  if (wrap) return wrap;

  const tableBody = document.getElementById("tablaTesis");
  const tableContainer = tableBody?.closest(".overflow-x-auto");
  if (!tableContainer || !tableContainer.parentNode) return null;

  wrap = document.createElement("div");
  wrap.id = "paginacionTesis";
  wrap.className = "mt-4 flex flex-wrap items-center justify-center gap-2";
  tableContainer.parentNode.insertBefore(wrap, tableContainer.nextSibling);
  return wrap;
}

function renderTesisTable() {
  const tbody = document.getElementById("tablaTesis");
  if (!tbody) return;

  const start = (tesisCurrentPage - 1) * TESIS_PAGE_SIZE;
  const pageRows = tesisFiltered.slice(start, start + TESIS_PAGE_SIZE);

  if (!pageRows.length) {
    tbody.innerHTML = `<tr><td colspan="12" class="text-center py-6">No hay registros</td></tr>`;
    return;
  }

  tbody.innerHTML = pageRows.map(t => `
      <tr class="border-b">
        <td class="px-3 py-2">${escapeHtml(t.codigo)}</td>
        <td class="px-3 py-2">${escapeHtml(t.titulo)}</td>
        <td class="px-3 py-2">
          <div class="w-14 h-20 overflow-hidden rounded shadow-sm">
            <img src="${escapeAttr(t.portada || DEFAULT_COVER)}"
                 class="w-full h-full object-cover" alt="Portada">
          </div>
        </td>
        <td class="px-3 py-2">${escapeHtml(t.autor)}</td>
        <td class="px-3 py-2">${escapeHtml(t.tutor || '')}</td>
        <td class="px-3 py-2">${escapeHtml(t.universidad || '')}</td>
        <td class="px-3 py-2">${escapeHtml(t.categoria_nombre || '')}</td>
        <td class="px-3 py-2">${escapeHtml(t.anio || '')}</td>
        <td class="px-3 py-2">${escapeHtml(t.descripcion || '')}</td>
        <td class="px-3 py-2">
          <a href="${escapeAttr(t.link_archivo || '#')}" target="_blank"
             class="text-blue-600 underline">Ver</a>
        </td>
        <td class="px-3 py-2">
          <span class="${t.estado === 'ACTIVO'
            ? 'bg-green-100 text-green-700'
            : 'bg-red-100 text-red-700'} px-3 py-1 rounded text-sm font-medium">
            ${escapeHtml(t.estado || '')}
          </span>
        </td>
        <td class="px-3 py-2">
          <button onclick="editTesis(${t.id})" class="px-3 py-1 bg-yellow-500 text-white rounded">✏️</button>
          <button onclick="deleteTesis(${t.id})" class="px-3 py-1 bg-red-600 text-white rounded">🗑️</button>
        </td>
      </tr>
    `).join('');
}

function renderTesisPagination() {
  const wrap = ensureTesisPaginationWrap();
  if (!wrap) return;

  const totalPages = Math.ceil(tesisFiltered.length / TESIS_PAGE_SIZE);
  if (totalPages <= 1) {
    wrap.innerHTML = "";
    return;
  }

  const buttons = [];
  for (let i = 1; i <= totalPages; i++) {
    buttons.push(`
      <button
        type="button"
        onclick="changeTesisPage(${i})"
        class="px-3 py-1.5 rounded-md border text-sm ${i === tesisCurrentPage
          ? 'bg-[#1b4785] text-white border-[#1b4785]'
          : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'}">
        ${i}
      </button>
    `);
  }

  wrap.innerHTML = buttons.join('');
}

function applyTesisFilter() {
  const input = document.getElementById("buscarTesis");
  const filtro = (input?.value || "").toLowerCase().trim();

  tesisFiltered = tesisData.filter(t => {
    const titulo = (t.titulo || "").toLowerCase();
    return titulo.includes(filtro);
  });

  tesisCurrentPage = 1;
  renderTesisTable();
  renderTesisPagination();
}

function changeTesisPage(page) {
  const totalPages = Math.max(1, Math.ceil(tesisFiltered.length / TESIS_PAGE_SIZE));
  tesisCurrentPage = Math.min(Math.max(1, page), totalPages);
  renderTesisTable();
  renderTesisPagination();
}

function loadTesis() {
  fetch(`${BASE_URL}/tesis/indexJson`)
    .then(r => r.json())
    .then(data => {
      const selectCategoria = document.getElementById("tesis_categoria");
      selectCategoria.innerHTML = `<option value="">Seleccione...</option>`;

      if (Array.isArray(data.categorias)) {
        data.categorias.forEach(c => {
          selectCategoria.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
        });
      }

      if (!data.success || !Array.isArray(data.tesis) || data.tesis.length === 0) {
        tesisData = [];
        tesisFiltered = [];
        tesisCurrentPage = 1;
        renderTesisTable();
        renderTesisPagination();
        return;
      }

      tesisData = data.tesis;
      applyTesisFilter();
    })
    .catch(err => {
      console.error("Error cargando tesis:", err);
      tesisData = [];
      tesisFiltered = [];
      tesisCurrentPage = 1;
      const tbody = document.getElementById("tablaTesis");
      if (tbody) {
        tbody.innerHTML = `<tr><td colspan="12" class="text-center text-red-600 py-6">Error al cargar datos</td></tr>`;
      }
      renderTesisPagination();
    });
}

document.addEventListener('DOMContentLoaded', () => {
  bindTesisCoverPreview();
  loadTesis();
});

document.getElementById("buscarTesis").addEventListener("input", function () {
  applyTesisFilter();
});

/* ========= ESCAPE ========= */
function escapeHtml(str) {
  if (!str && str !== 0) return "";
  return String(str)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}
function escapeAttr(str) { return escapeHtml(str); }

/* ========= MODAL ========= */
function openModalTesis() {
  document.getElementById("formTesis").reset();
  document.getElementById("tesis_id").value = "";
  document.getElementById("modalTesisTitle").innerText = "Nueva Tesis";
  document.getElementById("campoEstadoTesis").classList.add("hidden");
  setTesisCoverPreview("");
  document.getElementById("tesis_pdf_actual_wrap").classList.add("hidden");
  document.getElementById("modalTesis").classList.remove("hidden");
}
function closeModalTesis() {
  document.getElementById("modalTesis").classList.add("hidden");
}

/* ========= GUARDAR ========= */
function onSaveTesis() {
  const data = {
    id: tesis_id.value || "",
    categoria_id: tesis_categoria.value,
    titulo: tesis_titulo.value,
    autor: tesis_autor.value,
    tutor: tesis_tutor.value,
    universidad: tesis_universidad.value,
    anio: tesis_anio.value,
    descripcion: tesis_descripcion.value
  };

  if (data.id && tesis_estado) data.estado = tesis_estado.value;

  // Portada obligatoria al crear
  const portadaFileTesis = document.getElementById("tesis_portada").files?.[0];
  if (!data.id && !portadaFileTesis) {
    return Swal.fire("Campo obligatorio", "Debes seleccionar una portada para crear la tesis.", "warning");
  }

  const faltantes = validarCamposTesis(data);
  if (faltantes.length) {
    return Swal.fire({
      icon: "warning",
      title: "Faltan campos",
      html: `Complete: <b>${faltantes.join(", ")}</b>`,
      confirmButtonColor: "#1b4785"
    });
  }

  Swal.fire({
    title: data.id ? "Confirmar edición" : "Confirmar creación",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, guardar",
    confirmButtonColor: "#1b4785"
  }).then(r => {
    if (!r.isConfirmed) return;

    const url = data.id ? "/tesis/updateJson" : "/tesis/createJson";
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => {
      formData.append(key, value ?? "");
    });

    if (portadaFileTesis) {
      formData.append("portada_file", portadaFileTesis);
    }

    const pdfFileTesis = document.getElementById("tesis_link").files?.[0];
    if (pdfFileTesis) {
      formData.append("pdf_file", pdfFileTesis);
    }

    fetch(`${BASE_URL}${url}`, {
      method: "POST",
      body: formData
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        Swal.fire("OK", "Guardado correctamente", "success")
          .then(() => lanzarConfetti());
        closeModalTesis();
        loadTesis();
      } else {
        Swal.fire("Error", "No se pudo guardar", "error");
      }
    });
  });
}

/* ========= EDITAR ========= */
function editTesis(id) {
  fetch(`${BASE_URL}/tesis/getJson?id=${id}`)
    .then(r => r.json())
    .then(resp => {
      if (!resp.success) return Swal.fire("Error", "No encontrada", "error");

      const t = resp.data;
      tesis_id.value = t.id ?? "";
      tesis_categoria.value = t.categoria_id ?? "";
      tesis_titulo.value = t.titulo ?? "";
      tesis_autor.value = t.autor ?? "";
      tesis_tutor.value = t.tutor ?? "";
      tesis_universidad.value = t.universidad ?? "";
      tesis_anio.value = t.anio ?? "";
      tesis_descripcion.value = t.descripcion ?? "";
      tesis_portada.value = "";
      tesis_link.value = "";
      setTesisCoverPreview(t.portada || DEFAULT_COVER);

      // Mostrar enlace al PDF actual si existe
      const pdfWrap = document.getElementById("tesis_pdf_actual_wrap");
      const pdfLink = document.getElementById("tesis_pdf_actual_link");
      if (t.link_archivo && pdfWrap && pdfLink) {
        pdfLink.href = t.link_archivo;
        pdfWrap.classList.remove("hidden");
      } else if (pdfWrap) {
        pdfWrap.classList.add("hidden");
      }

      if (t.estado) {
        tesis_estado.value = t.estado;
        campoEstadoTesis.classList.remove("hidden");
      }

      modalTesisTitle.innerText = "Editar Tesis";
      modalTesis.classList.remove("hidden");
    });
}

/* ========= ELIMINAR ========= */
function deleteTesis(id) {
  Swal.fire({
    title: "¿Eliminar?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33"
  }).then(r => {
    if (!r.isConfirmed) return;

    fetch(`${BASE_URL}/tesis/deleteJson`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        Swal.fire("Eliminado", "Correcto", "success");
        loadTesis();
      }
    });
  });
}
