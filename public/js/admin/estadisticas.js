let chartPrestamos = null;
let chartItems = null;
let chartUsuarios = null;

async function cargarEstadisticas() {
    try {
        console.log('Cargando estadísticas...');
        const res = await fetch(`${BASE_URL}/estadisticas/api`);

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const data = await res.json();
        console.log('Datos recibidos:', data);

        if (!data.success) {
            document.getElementById('contadoresEstadisticas').innerHTML = `
                <p class="text-center text-red-500 col-span-6 py-6">
                    ❌ ${data.message || 'Error al cargar estadísticas'}
                </p>`;
            return;
        }

        const t = data.totales || {};

        document.getElementById('contadoresEstadisticas').innerHTML = `
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-lg p-6 border-l-4 border-blue-600">
                <div class="text-5xl mb-2">📚</div>
                <h3 class="text-gray-700 font-semibold mb-1">Total Libros</h3>
                <p class="text-3xl font-bold text-blue-700">${t.libros || 0}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-lg p-6 border-l-4 border-purple-600">
                <div class="text-5xl mb-2">🎓</div>
                <h3 class="text-gray-700 font-semibold mb-1">Total Tesis</h3>
                <p class="text-3xl font-bold text-purple-700">${t.tesis || 0}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-lg p-6 border-l-4 border-orange-600">
                <div class="text-5xl mb-2">📰</div>
                <h3 class="text-gray-700 font-semibold mb-1">Publicaciones</h3>
                <p class="text-3xl font-bold text-orange-700">${t.publicaciones || 0}</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-lg p-6 border-l-4 border-green-600">
                <div class="text-5xl mb-2">👥</div>
                <h3 class="text-gray-700 font-semibold mb-1">Usuarios Activos</h3>
                <p class="text-3xl font-bold text-green-700">${t.usuarios || 0}</p>
            </div>
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg shadow-lg p-6 border-l-4 border-indigo-600">
                <div class="text-5xl mb-2">📖</div>
                <h3 class="text-gray-700 font-semibold mb-1">Préstamos totales</h3>
                <p class="text-3xl font-bold text-indigo-700">${t.prestamosActivos || 0}</p>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-lg p-6 border-l-4 border-red-600">
                <div class="text-5xl mb-2">⏰</div>
                <h3 class="text-gray-700 font-semibold mb-1">Atrasados</h3>
                <p class="text-3xl font-bold text-red-700">${t.atrasados || 0}</p>
            </div>
        `;

        if (chartPrestamos) chartPrestamos.destroy();
        if (chartItems) chartItems.destroy();
        if (chartUsuarios) chartUsuarios.destroy();

        chartPrestamos = new Chart(document.getElementById('graficoPrestamos'), {
            type: 'line',
            data: {
                labels: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
                datasets: [
                    { label: 'Préstamos', data: data.prestamosMes || [], borderColor: '#1b4785', fill: true },
                    { label: 'Devoluciones', data: data.devolucionesMes || [], borderColor: '#479990', fill: true }
                ]
            }
        });

       if ((data.itemsMasPrestados || []).length) {
    chartItems = new Chart(document.getElementById('graficoItems'), {
        type: 'bar',
        data: {
            labels: data.itemsMasPrestados.map(i => i.titulo),
            datasets: [{
                label: 'Cantidad de préstamos',
                data: data.itemsMasPrestados.map(i => i.total),
                backgroundColor: '#34A6F4' 
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: '📚 Top 5 de libros más prestados',
                    font: {
                        size: 18
                    }
                },
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

if ((data.usuariosActivos || []).length) {
    chartUsuarios = new Chart(document.getElementById('graficoUsuarios'), {
        type: 'bar',
        data: {
            labels: data.usuariosActivos.map(u => `${u.nombre} ${u.apellido}`),
            datasets: [{
                label: 'Cantidad de préstamos',
                data: data.usuariosActivos.map(u => u.total),
                backgroundColor: '#FFB86A' 
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: '👤 Top 5 de usuarios más activos',
                    font: {
                        size: 18
                    }
                },
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}


    } catch (err) {
        console.error(err);
    }
}

function descargarExcelEstadisticas() {
    window.location.href = `${BASE_URL}/estadisticas/exportExcel`;
}

document.addEventListener('DOMContentLoaded', () => {
    const tab = document.getElementById('estadistica');
    if (tab && !tab.classList.contains('hidden')) {
        cargarEstadisticas();
    }
});
