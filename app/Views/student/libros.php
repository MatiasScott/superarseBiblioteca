<?php
$usuarioLogueado = true;
$pageTitle = 'Libros';
$activeStudentModule = 'libros';
include __DIR__ . '/../layouts/student_header.php';
?>

<div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 mb-6 border-l-4 border-[#1b4785]">
    <h2 class="text-2xl sm:text-3xl font-bold text-[#1b4785] mb-2">📚 Catálogo de Libros</h2>
    <p class="text-gray-600 text-sm sm:text-base">
        Explora libros disponibles, revisa su stock y solicita préstamos sin salir del panel de estudiante.
    </p>
</div>

<div class="max-w-3xl mx-auto px-1 sm:px-4 mt-2 sm:mt-4 mb-6">
    <input
        id="buscador"
        type="text"
        placeholder="🔍 Buscar libro por título y autor..."
        class="w-full p-3 border rounded-lg shadow-sm focus:ring-2 focus:ring-superarse-morado-medio focus:outline-none"
        onkeyup="filtrarLibros()">
</div>

<div id="gridLibros"
     class="px-1 sm:px-4 py-4 sm:py-6 min-h-[60vh]
            grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($libros as $libro): ?>
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition cursor-pointer flex flex-col"
             onclick="abrirModal(<?= $libro['id'] ?>)">
            <img src="<?= htmlspecialchars($libro['portada']) ?>"
                 alt="<?= htmlspecialchars($libro['titulo']) ?>"
                 class="w-full h-52 sm:h-56 md:h-60 object-cover">

            <div class="p-4 flex flex-col flex-grow">
                <h3 class="font-bold text-base sm:text-lg text-superarse-morado-oscuro line-clamp-2">
                    <?= htmlspecialchars($libro['titulo']) ?>
                </h3>

                <p class="text-gray-600 text-xs sm:text-sm mt-1">
                    Autor: <?= htmlspecialchars($libro['autor']) ?>
                </p>

                <p class="text-gray-500 text-xs sm:text-sm mb-3">
                    Categoría: <?= htmlspecialchars($libro['categoria_nombre']) ?>
                </p>

                <div class="grid grid-cols-2 gap-2 text-xs sm:text-sm mt-auto">
                    <div class="bg-blue-500 text-white rounded-lg py-1 text-center">
                        Ubicación: <?= htmlspecialchars($libro['ubicacion']) ?>
                    </div>
                    <div class="bg-blue-500 text-white rounded-lg py-1 text-center">
                        Edición: <?= htmlspecialchars($libro['edicion']) ?>
                    </div>
                    <div class="bg-green-500 text-white rounded-lg py-1 text-center">
                        Stock: <?= htmlspecialchars($libro['stock']) ?>
                    </div>
                    <div class="bg-green-500 text-white rounded-lg py-1 text-center">
                        Código: <?= htmlspecialchars($libro['codigo']) ?>
                    </div>
                    <div class="col-span-2 bg-orange-500 text-white rounded-lg py-1 text-center mt-1">
                        👁️ Visitas:
                        <span id="visitasLibro-<?= $libro['id'] ?>"><?= htmlspecialchars($libro['visitas']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="noResultsMessage" class="px-4 py-12 text-center text-gray-500 hidden">
    <p class="text-lg">No se encontraron libros que coincidan con la búsqueda.</p>
</div>

<div id="modalLibro"
     class="fixed inset-0 bg-black/60 hidden flex items-start sm:items-center justify-center z-50 px-3 sm:px-4 py-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg md:max-w-3xl p-4 sm:p-6 relative max-h-[90vh] overflow-y-auto mt-4 sm:mt-0">
        <button onclick="cerrarModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-900 text-2xl font-bold">×</button>
        <div id="modalContent"></div>
    </div>
</div>

<div id="modalAlerta"
     class="fixed inset-0 bg-black/40 hidden flex items-start sm:items-center justify-center z-50 px-3 sm:px-4 py-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-4 sm:p-5 text-center relative mt-4 sm:mt-0">
        <button onclick="cerrarAlerta()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-900 text-2xl font-bold">×</button>
        <h2 id="alertaTitulo" class="text-lg sm:text-xl font-bold text-superarse-morado-oscuro mb-3">Notificación</h2>
        <p id="alertaMensaje" class="text-gray-700 text-sm sm:text-base mb-5">...</p>
        <button onclick="aceptarAlerta()" class="bg-superarse-morado-oscuro hover:bg-superarse-morado-medio text-white px-4 py-2 rounded-lg transition">Aceptar</button>
    </div>
</div>

<script>
window.APP = {
    BASE_URL: "<?= rtrim(BASE_URL, '/') ?>",
    libros: <?= json_encode($libros ?? []) ?>,
    usuarioLogueado: true
};
</script>
<script src="<?= BASE_URL ?>/js/estudiantes/libros.js?v=<?= time() ?>"></script>

<?php include __DIR__ . '/../layouts/student_footer.php'; ?>
