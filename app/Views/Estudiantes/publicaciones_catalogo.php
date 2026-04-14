<?php
$usuarioLogueado = isset($_SESSION['usuario_id']);
$pageTitle = 'Catálogo de Publicaciones';
$activeStudentModule = 'publicaciones';

if ($usuarioLogueado) {
    include __DIR__ . '/../layouts/student_header.php';
} else {
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Dra. Mery Navas</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logoSuperarse.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'superarse-morado-oscuro': '#1b4785ff',
                        'superarse-morado-medio': '#479990ff',
                        'superarse-rosa': '#164c7eff',
                        'superarse-amarillo': '#fbbf24',
                        'superarse-verde': '#22c55e'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php include(__DIR__ . '/../heatherGeneral.php'); ?>
    <?php
}
?>

<!-- BUSCADOR -->
<div class="max-w-3xl mx-auto px-4 mt-6">
    <input 
        id="buscador"
        type="text"
        placeholder="🔍 Buscar por título, autor o año..."
        class="w-full p-3 border rounded-lg shadow-sm focus:ring-2 focus:ring-superarse-morado-medio focus:outline-none"
        onkeyup="filtrarPublicaciones()"
    >
</div>

<!-- GRID DONDE SE MOSTRARÁN LAS PUBLICACIONES -->
<div id="gridPublicaciones"
    class="max-w-7xl mx-auto px-4 py-6 min-h-[60vh]
          grid grid-cols-1
          sm:grid-cols-2
          md:grid-cols-3
          lg:grid-cols-4
          gap-5">
</div>

<!-- Paginación -->
<div id="paginationPublicaciones" class="max-w-7xl mx-auto px-4"></div>
<!-- Mensaje cuando no hay resultados -->
<div id="noResultsMessagePublicaciones" class="max-w-7xl mx-auto px-4 py-12 text-center text-gray-500 hidden">
    <p class="text-lg">No se encontraron publicaciones que coincidan con la búsqueda.</p>
</div>
<div id="modalPublicacion"
     class="fixed inset-0 bg-black/60 hidden
          flex items-start sm:items-center justify-center z-50 px-3 sm:px-4 py-4 overflow-y-auto">

    <div class="bg-white rounded-2xl shadow-xl
                w-full max-w-lg md:max-w-3xl
                p-4 sm:p-6 relative
                max-h-[90vh] overflow-y-auto mt-4 sm:mt-0">

        <button onclick="cerrarModalPublicacion()"
                class="absolute top-3 right-3
                       text-gray-500 hover:text-gray-900
                       text-2xl font-bold">
            ×
        </button>

        <div id="modalContent"></div>
    </div>
</div>

<script>
    window.APP = window.APP || {};
    window.APP.publicaciones = <?= json_encode($publicaciones ?? []) ?>;
</script>

<script src="<?= BASE_URL ?>/js/estudiantes/publicaciones.js?v=20260414d"></script>

<?php
if ($usuarioLogueado) {
    include __DIR__ . '/../layouts/student_footer.php';
} else {
    include __DIR__ . '/../footer.php';
    ?>
</body>
</html>
<?php
}
?>
