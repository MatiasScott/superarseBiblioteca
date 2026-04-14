(function () {
    function buildReportItemCards() {
        document.querySelectorAll('.reportItem').forEach((el) => {
            const key = el.dataset.key;
            const label = el.dataset.label;
            el.className = 'border rounded-lg p-3 flex flex-col gap-2';
            el.innerHTML = `
                <span class="text-sm font-semibold text-gray-700">${label}</span>
                <div class="flex gap-2">
                    <button class="btnReport" data-key="${key}" data-format="xlsx">Excel</button>
                    <button class="btnReport" data-key="${key}" data-format="pdf">PDF</button>
                </div>
            `;
        });
    }

    function fillSelect(selectId, options, firstLabel) {
        const select = document.getElementById(selectId);
        if (!select) return;

        select.innerHTML = `<option value="">${firstLabel}</option>`;
        options.forEach((opt) => {
            const option = document.createElement('option');
            option.value = opt.id ?? opt;
            option.textContent = opt.nombre ?? opt;
            select.appendChild(option);
        });
    }

    function loadFilters() {
        fetch(`${BASE_URL}/reporte/filtros`)
            .then((res) => res.json())
            .then((data) => {
                if (!data.ok) return;

                fillSelect('tesisCarrera', data.tesisCarreras || [], 'Seleccione carrera');
                fillSelect('librosCategoria', data.librosCategorias || [], 'Seleccione categoría');
                fillSelect('tesisAnio', data.aniosTesis || [], 'Seleccione año');
            })
            .catch(() => {
                Swal.fire('Error', 'No fue posible cargar filtros de reportes.', 'error');
            });
    }

    function getFilterValue(button) {
        if (!button.classList.contains('with-filter')) {
            return null;
        }

        const filterId = button.dataset.filter;
        const param = button.dataset.param;
        const select = document.getElementById(filterId);

        if (!select || !select.value) {
            Swal.fire('Falta información', 'Debes seleccionar el filtro requerido.', 'warning');
            return false;
        }

        return { param, value: select.value };
    }

    function triggerDownload(button) {
        const key = button.dataset.key;
        const format = button.dataset.format;

        const filter = getFilterValue(button);
        if (filter === false) {
            return;
        }

        const params = new URLSearchParams({ key, format });
        if (filter && filter.param) {
            params.append(filter.param, filter.value);
        }

        window.location.href = `${BASE_URL}/reporte/export?${params.toString()}`;
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.btnReport');
        if (!button) return;

        event.preventDefault();
        triggerDownload(button);
    });

    document.addEventListener('DOMContentLoaded', () => {
        buildReportItemCards();
        loadFilters();
    });
})();
