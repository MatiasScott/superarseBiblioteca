<?php
/**
 * app/Views/layouts/student_header.php
 * Variables esperadas: $pageTitle, $activeStudentModule
 */

$studentModules = [
    'dashboard' => ['icon' => '🏠', 'label' => 'Inicio',       'url' => BASE_URL . '/student/dashboard'],
    'prestamos' => ['icon' => '📖', 'label' => 'Mis Préstamos', 'url' => BASE_URL . '/student/prestamos'],
    'perfil'    => ['icon' => '👤', 'label' => 'Mi Perfil',     'url' => BASE_URL . '/student/perfil'],
    'libros'    => ['icon' => '📚', 'label' => 'Libros',        'url' => BASE_URL . '/student/libros'],
    'tesis'     => ['icon' => '🎓', 'label' => 'Tesis',         'url' => BASE_URL . '/student/tesis'],
    'publicaciones' => ['icon' => '📂', 'label' => 'Publicaciones', 'url' => BASE_URL . '/student/publicaciones'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Estudiante') ?> - Biblioteca Dra. Mery Navas</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logoSuperarse.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    <style>
        @media (max-width: 1023px) {
            #studentSidebar {
                transform: translateX(-100%);
                transition: transform .3s ease-in-out;
            }
            #studentSidebar.open {
                transform: translateX(0);
            }
        }
    </style>

    <script>
        const BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
    </script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col lg:flex-row">
    <aside id="studentSidebar"
        class="fixed lg:sticky lg:top-0 lg:self-start top-0 left-0 z-40 h-screen w-[min(18rem,85vw)] lg:w-64
                  bg-gradient-to-b from-[#1b4785] to-[#479990]
            text-white flex flex-col shadow-xl shrink-0 lg:translate-x-0">

        <div class="flex items-center gap-3 px-4 py-5 border-b border-white/20 shrink-0">
            <img src="<?= BASE_URL ?>/assets/img/LOGO SUPERARSE PNG-02.png" class="h-10 w-auto" alt="Logo">
            <span class="font-bold text-sm leading-tight">Biblioteca<br>Dra. Mery Navas</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-2">
            <?php foreach ($studentModules as $key => $mod): ?>
                <?php $isActive = ($activeStudentModule ?? '') === $key; ?>
                <a href="<?= $mod['url'] ?>"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
                          transition-colors duration-150
                          <?= $isActive ? 'bg-white/20 font-semibold text-white' : 'text-white/85 hover:bg-white/10 hover:text-white' ?>">
                    <span class="text-lg w-6 text-center"><?= $mod['icon'] ?></span>
                    <span><?= htmlspecialchars($mod['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="px-4 py-4 border-t border-white/20 shrink-0">
            <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Estudiante') ?></p>
            <p class="text-xs text-white/70 mb-3">👤 Solicitante</p>
            <a href="<?= BASE_URL ?>/logout"
               class="block text-center bg-red-600/80 hover:bg-red-600
                      text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                🚪 Cerrar Sesión
            </a>
        </div>
    </aside>

    <div id="studentSidebarOverlay" class="hidden fixed inset-0 bg-black/40 z-30 lg:hidden" onclick="closeStudentSidebar()"></div>

    <div class="flex flex-col flex-1 min-h-screen min-w-0 overflow-hidden">
        <header class="bg-gradient-to-r from-[#1b4785] to-[#479990] text-white shadow-md px-4 py-3 flex items-center justify-between gap-3 shrink-0 sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button onclick="toggleStudentSidebar()" class="lg:hidden text-white text-xl focus:outline-none" aria-label="Abrir menú">☰</button>
                <h1 class="font-bold text-base sm:text-lg"><?= htmlspecialchars($pageTitle ?? 'Estudiante') ?></h1>
            </div>
            <a href="https://elibro.net/es/lc/superarse/login_usuario/" target="_blank" class="text-xs sm:text-sm font-semibold hover:underline shrink-0">
                eLibro
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-3 sm:p-6">
            <div class="w-full max-w-[1320px] mx-auto">
