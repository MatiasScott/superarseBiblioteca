<?php
$pageTitle = 'Reportes';
$activeModule = 'reportes';
include __DIR__ . '/../layouts/admin_header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 lg:p-8 mb-8">
    <h3 class="text-xl sm:text-2xl font-bold text-[#1b4785] mb-2">🧾 Módulo de Reportes</h3>
    <p class="text-sm text-gray-600 mb-6">
        Descarga cada reporte en formato Excel o PDF.
    </p>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="border rounded-xl p-4">
            <h4 class="font-bold text-[#1b4785] mb-3">🎓 Reportes de Tesis</h4>

            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <span class="text-sm font-semibold w-56">Reporte de tesis</span>
                    <div class="flex gap-2">
                        <button class="btnReport" data-key="tesis_todas" data-format="xlsx">Excel</button>
                        <button class="btnReport" data-key="tesis_todas" data-format="pdf">PDF</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <span class="text-sm font-semibold w-56">Tesis por carrera</span>
                    <select id="tesisCarrera" class="input text-sm w-full sm:w-56"></select>
                    <div class="flex gap-2">
                        <button class="btnReport with-filter" data-key="tesis_por_carrera" data-filter="tesisCarrera" data-param="categoria_id" data-format="xlsx">Excel</button>
                        <button class="btnReport with-filter" data-key="tesis_por_carrera" data-filter="tesisCarrera" data-param="categoria_id" data-format="pdf">PDF</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <span class="text-sm font-semibold w-56">Tesis por año</span>
                    <select id="tesisAnio" class="input text-sm w-full sm:w-40"></select>
                    <div class="flex gap-2">
                        <button class="btnReport with-filter" data-key="tesis_por_anio" data-filter="tesisAnio" data-param="anio" data-format="xlsx">Excel</button>
                        <button class="btnReport with-filter" data-key="tesis_por_anio" data-filter="tesisAnio" data-param="anio" data-format="pdf">PDF</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <span class="text-sm font-semibold w-56">Tesis más vistas (asc)</span>
                    <div class="flex gap-2">
                        <button class="btnReport" data-key="tesis_mas_vistas" data-format="xlsx">Excel</button>
                        <button class="btnReport" data-key="tesis_mas_vistas" data-format="pdf">PDF</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="border rounded-xl p-4">
            <h4 class="font-bold text-[#1b4785] mb-3">📚 Reportes de Libros</h4>

            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <span class="text-sm font-semibold w-56">Todos los libros</span>
                    <div class="flex gap-2">
                        <button class="btnReport" data-key="libros_todos" data-format="xlsx">Excel</button>
                        <button class="btnReport" data-key="libros_todos" data-format="pdf">PDF</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <span class="text-sm font-semibold w-56">Libros por categoría</span>
                    <select id="librosCategoria" class="input text-sm w-full sm:w-56"></select>
                    <div class="flex gap-2">
                        <button class="btnReport with-filter" data-key="libros_por_categoria" data-filter="librosCategoria" data-param="categoria_id" data-format="xlsx">Excel</button>
                        <button class="btnReport with-filter" data-key="libros_por_categoria" data-filter="librosCategoria" data-param="categoria_id" data-format="pdf">PDF</button>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <span class="text-sm font-semibold w-56">Libros vigentes (5 años)</span>
                    <div class="flex gap-2">
                        <button class="btnReport" data-key="libros_vigentes_5_anios" data-format="xlsx">Excel</button>
                        <button class="btnReport" data-key="libros_vigentes_5_anios" data-format="pdf">PDF</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="border rounded-xl p-4">
            <h4 class="font-bold text-[#1b4785] mb-3">📖 Reportes de Préstamos</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="reportItem" data-key="prestamos_totales" data-label="Préstamos totales"></div>
                <div class="reportItem" data-key="prestamos_activos" data-label="Préstamos activos"></div>
                <div class="reportItem" data-key="prestamos_pendientes" data-label="Préstamos pendientes"></div>
                <div class="reportItem" data-key="prestamos_rechazados" data-label="Préstamos rechazados"></div>
                <div class="reportItem" data-key="prestamos_devueltos" data-label="Libros devueltos"></div>
            </div>
        </section>

        <section class="border rounded-xl p-4">
            <h4 class="font-bold text-[#1b4785] mb-3">📂 Reportes de Publicaciones</h4>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <span class="text-sm font-semibold w-56">Reporte de publicaciones</span>
                <div class="flex gap-2">
                    <button class="btnReport" data-key="publicaciones_todas" data-format="xlsx">Excel</button>
                    <button class="btnReport" data-key="publicaciones_todas" data-format="pdf">PDF</button>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
.btnReport {
    background: #1b4785;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 0.5rem;
    padding: 0.45rem 0.75rem;
    transition: background-color .2s ease;
}
.btnReport:hover { background: #479990; }
</style>

<script src="<?= BASE_URL ?>/js/admin/reportes.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
