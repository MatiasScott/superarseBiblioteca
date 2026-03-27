// Simple helper para validar campos y mostrar errores
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

/* ========= CARGAR TESIS Y CATEGORÍAS ========= */
document.addEventListener("DOMContentLoaded", loadTesis);

function loadTesis() {
  fetch(`/tesis/indexJson`)
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById("tablaTesis");
      const selectCategoria = document.getElementById("tesis_categoria");
      tbody.innerHTML = "";
      selectCategoria.innerHTML = `<option value="">Seleccione...</option>`;

      if (Array.isArray(data.categorias)) {
        data.categorias.forEach(c => {
          selectCategoria.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
        });
      }

      if (!data.success || !Array.isArray(data.tesis) || data.tesis.length === 0) {
        tbody.innerHTML = `<tr><td colspan="11" class="text-center py-6">No hay registros</td></tr>`;
        return;
      }

      data.tesis.forEach(t => {
        tbody.innerHTML += `
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
        `;
      });
    })
    .catch(err => {
      console.error("Error cargando tesis:", err);
      document.getElementById("tablaTesis").innerHTML =
        `<tr><td colspan="11" class="text-center text-red-600 py-6">Error al cargar datos</td></tr>`;
    });
}

document.getElementById("buscarTesis").addEventListener("input", function () {
  const filtro = this.value.toLowerCase();
  const filas = document.querySelectorAll("#tablaTesis tr");

  filas.forEach(fila => {
    const titulo = fila.cells[1]?.textContent.toLowerCase() || "";
    fila.style.display = titulo.includes(filtro) ? "" : "none";
  });
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
    descripcion: tesis_descripcion.value,
    portada: tesis_portada.value,
    link_archivo: tesis_link.value
  };

  if (data.id && tesis_estado) data.estado = tesis_estado.value;

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
    fetch(`${BASE_URL}${url}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
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
      tesis_portada.value = t.portada ?? "";
      tesis_link.value = t.link_archivo ?? "";

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
