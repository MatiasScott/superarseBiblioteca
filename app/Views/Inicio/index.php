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
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-r from-superarse-morado-oscuro via-superarse-morado-medio to-superarse-rosa min-h-screen flex flex-col">

    <!-- HEADER -->
    <header class="bg-transparent text-white w-full py-6 shadow-lg relative">
       <div class="relative max-w-7xl mx-auto px-4 py-8 flex flex-col items-center text-center">

    <!-- BOTÓN LOGIN (arriba derecha) -->
    <div class="absolute top-4 right-1 sm:top-6 sm:right-1">
        <a href="login"
           class="px-4 sm:px-5 py-2 bg-white/20 backdrop-blur-lg border border-white/40 rounded-xl text-white font-semibold text-sm sm:text-base hover:scale-105 transition shadow-lg whitespace-nowrap">
            🔐 Iniciar Sesión
        </a>
    </div>

    <!-- LOGO -->
    <img src="<?= BASE_URL ?>/assets/img/LOGO SUPERARSE PNG-02.png"
         alt="Logo Superarse"
         class="h-14 sm:h-16 md:h-20 w-auto mb-3 sm:mb-4">

    <!-- TÍTULO -->
    <h1 class="font-bold tracking-wide text-2xl sm:text-3xl md:text-4xl">
        📚 Biblioteca Dra. Mery Navas
    </h1>

    <!-- SUBTÍTULO -->
    <p class="text-xs sm:text-sm text-gray-200 mt-1">
        Libros • Tesis • Publicaciones
    </p>

</div>

    </header>

    <!-- CONTENIDO CENTRAL -->
    <main class="flex-grow flex flex-col items-center justify-start mt-10 px-4">

        <!-- Buscador Global -->
        <div class="w-full max-w-2xl">
            <input 
                id="buscadorGlobal"
                type="text" 
                placeholder="🔎 Buscar libros, tesis o publicaciones por el título ..."
                class="w-full px-5 py-4 rounded-xl shadow-lg border-2 border-white/40 bg-white/80 backdrop-blur-md focus:outline-none focus:border-superarse-morado-medio transition">
        </div>

        <!-- CONTENEDOR DE RESULTADOS -->
        <div id="contenedorResultados" class="w-full max-w-4xl mt-10 hidden">
            <h2 class="text-white text-2xl font-bold mb-4">Resultados de la búsqueda:</h2>

            <div id="listaResultados" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Aquí se insertan los resultados dinámicos -->
            </div>
        </div>

        <!-- Categorías -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-12 w-full max-w-4xl">

            <!-- Libros -->
            <a href="<?= BASE_URL ?>/libros/catalogo"
               class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-xl border border-white/30 hover:scale-105 transition-all">
                <h2 class="text-xl font-bold text-superarse-morado-oscuro mb-2">📘 Libros</h2>
                <p class="text-gray-600 text-sm">Colección de libros académicos y de consulta general.</p>
            </a>

            <!-- Tesis -->
            <a href="<?= BASE_URL ?>/tesis"
               class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-xl border border-white/30 hover:scale-105 transition-all">
                <h2 class="text-xl font-bold text-superarse-morado-medio mb-2">📗 Tesis</h2>
                <p class="text-gray-600 text-sm">Tesis de Técnicos y Tecnólogos del Instituto Superarse</p>
            </a>

            <!-- Publicaciones Docentes -->
            <a href="<?= BASE_URL ?>/publicaciones"
               class="bg-white/80 backdrop-blur-lg rounded-2xl p-6 shadow-xl border border-white/30 hover:scale-105 transition-all">
                <h2 class="text-xl font-bold text-superarse-rosa mb-2">📙 Publicaciones</h2>
                <p class="text-gray-600 text-sm">Artículos, papers y obras publicadas por docentes.</p>
            </a>

        </div>

    </main>

    <!-- FOOTER -->
        <footer class="text-white text-center py-4 mt-10">
        <p class="text-sm">&copy; All Rights Reserved. Designed by Instituto Superarse</p>
    </footer>

    <!-- SCRIPT DEL BUSCADOR GLOBAL -->
<script>
    window.APP = {
        BASE_URL: "<?= BASE_URL ?>"
    };

    window.INICIO_DATA = {
        libros: <?= json_encode($libros, JSON_UNESCAPED_UNICODE) ?>,
        tesis: <?= json_encode($tesis, JSON_UNESCAPED_UNICODE) ?>,
        publicaciones: <?= json_encode($publicaciones, JSON_UNESCAPED_UNICODE) ?>
    };
</script>

<script src="<?= BASE_URL ?>/js/inicio/index.js?v=1"></script>



</body>

</html>
