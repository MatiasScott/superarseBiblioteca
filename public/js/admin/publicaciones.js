//const BASE_URL = "";
let categoriasGlobal = [];

document.addEventListener("DOMContentLoaded", loadPublicaciones);

function loadPublicaciones() {
  fetch(`${BASE_URL}/publicaciones/indexJson`)
    .then(r => r.json())
    .then(data => {
      const tbody = document.getElementById("tablaPublicaciones");
      tbody.innerHTML = "";

      if (!data.success) {
        tbody.innerHTML = `<tr><td colspan="11" class="text-center py-6">Error al cargar datos</td></tr>`;
        return;
      }

      if (!data.publicaciones.length) {
        tbody.innerHTML = `<tr><td colspan="11" class="text-center py-6">No hay registros</td></tr>`;
      }

      categoriasGlobal = data.categorias;

      const select = document.getElementById("categoria_id");
      select.innerHTML = `<option value="">Seleccione una categoría</option>`;
      categoriasGlobal.forEach(c => {
        select.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
      });

      data.publicaciones.forEach(p => {
        const cat = categoriasGlobal.find(c => c.id == p.categoria_id);
        const catNombre = cat ? cat.nombre : "N/A";

        tbody.innerHTML += `
          <tr class="border-b">
            <td class="px-3 py-2">${p.codigo}</td>
            <td class="px-3 py-2">
              <div class="w-14 h-20 overflow-hidden rounded shadow">
                <img src="${p.portada}" class="w-full h-full object-cover">
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
      });
    });
}

document.getElementById("buscarPublicacion").addEventListener("input", function () {
  const filtro = this.value.toLowerCase();
  const filas = document.querySelectorAll("#tablaPublicaciones tr");

  filas.forEach(fila => {
    const titulo = fila.cells[2]?.textContent.toLowerCase() || "";
    fila.style.display = titulo.includes(filtro) ? "" : "none";
  });
});

function openModal() {
  document.getElementById("formPub").reset();
  document.getElementById("id").value = "";
  document.getElementById("modalTitle").innerText = "Nueva Publicación";
  document.getElementById("campoEstadoPub").classList.add("hidden");
  document.getElementById("modalPub").classList.remove("hidden");
}

function closeModal() {
  document.getElementById("modalPub").classList.add("hidden");
}

function submitPub() {
  const id = document.getElementById("id").value;
  const url = id ? "/publicaciones/updateJson" : "/publicaciones/createJson";

  const data = {
    id: id,
    portada: document.getElementById("portada").value,
    titulo: document.getElementById("titulo").value,
    autor: document.getElementById("autor").value,
    revista: document.getElementById("revista").value,
    anio: document.getElementById("anio").value,
    descripcion: document.getElementById("descripcion").value,
    categoria_id: document.getElementById("categoria_id").value,
    link_archivo: document.getElementById("link_archivo").value
  };

  if (id && document.getElementById("pub_estado")) {
    data.estado = document.getElementById("pub_estado").value;
  }

  fetch(`${BASE_URL}${url}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data)
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
      document.getElementById("portada").value = p.portada;
      document.getElementById("titulo").value = p.titulo;
      document.getElementById("autor").value = p.autor;
      document.getElementById("revista").value = p.revista;
      document.getElementById("anio").value = p.anio;
      document.getElementById("descripcion").value = p.descripcion;
      document.getElementById("categoria_id").value = p.categoria_id;
      document.getElementById("link_archivo").value = p.link_archivo;

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
