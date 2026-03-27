let charts = {};
let subTabActivo = 'libros';

/* ================= CAMBIO DE SUBTAB ================= */
function showSubTab(tipo) {
    subTabActivo = tipo;

    document.querySelectorAll('.sub-tab')
        .forEach(t => t.classList.add('hidden'));

    const contenedor = document.getElementById(tipo + 'ChartContainer');
    if (!contenedor) return;

    contenedor.classList.remove('hidden');

    filtrarRango();

    setTimeout(() => {
        if (charts[tipo]) {
            charts[tipo].resize();
        }
    }, 100);
}

/* ================= FILTRAR POR RANGO ================= */
function filtrarRango() {
    const inicio = document.getElementById('fechaInicio')?.value;
    const fin = document.getElementById('fechaFin')?.value;

    if (!inicio || !fin) return;

    cargarGrafico(subTabActivo, inicio, fin);
}

/* ================= CARGAR GRÁFICO ================= */
async function cargarGrafico(tipo, fechaInicio, fechaFin) {

    const canvasMap = {
        libros: 'chartLibros',
        tesis: 'chartTesis',
        publicaciones: 'chartPublicaciones'
    };

    try {
        if (charts[tipo]) {
            charts[tipo].destroy();
            charts[tipo] = null;
        }

        const canvas = document.getElementById(canvasMap[tipo]);
        if (!canvas) return;

        const url = `${BASE_URL}/${tipo}/masVistosJson`
                  + `?fechaInicio=${fechaInicio}&fechaFin=${fechaFin}`;

        const res = await fetch(url);
        const data = await res.json();

        let items = [];
        if (tipo === 'libros') items = data.libros || [];
        if (tipo === 'tesis') items = data.tesis || [];
        if (tipo === 'publicaciones') items = data.publicaciones || [];

        if (!data.success || !items.length) return;

        charts[tipo] = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: items.map(i =>
                    i.titulo.length > 30
                        ? i.titulo.substring(0, 30) + '...'
                        : i.titulo
                ),
                datasets: [{
                    label: 'Visitas',
                    data: items.map(i => i.visitas),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 12 }
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 10 },
                            maxRotation: 25,
                            minRotation: 25
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: `Top 10 ${tipo.toUpperCase()} más vistos`,
                        font: { size: 16 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const item = items[context.dataIndex];
                                return `${item.titulo} — ${item.visitas} visitas`;
                            }
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error(`Error cargando gráfico de ${tipo}:`, error);
    }
}

/* ================= DESCARGAR EXCEL ================= */
function descargarExcel(tipo) {
    const inicio = document.getElementById('fechaInicio')?.value;
    const fin = document.getElementById('fechaFin')?.value;

    if (!inicio || !fin) {
        alert('Seleccione un rango de fechas');
        return;
    }

    window.location.href =
        `${BASE_URL}/reporte/${tipo}MasVistos`
        + `?fechaInicio=${inicio}&fechaFin=${fin}`;
}

/* ================= CARGA INICIAL ================= */
document.addEventListener('DOMContentLoaded', () => {
    showSubTab('libros');
});

