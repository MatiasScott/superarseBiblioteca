<?php
/**
 * app/Views/layouts/admin_header.php
 *
 * Layout superior del panel de administración.
 * Variables esperadas antes del include:
 *   $pageTitle   (string) — título de la página
 *   $activeModule (string) — clave del módulo activo en el sidebar
 */

$sidebarModules = [
    'dashboard'    => ['icon' => '🎛️',  'label' => 'Panel de Control',  'url' => BASE_URL . '/dashboard'],
    'prestamos'    => ['icon' => '📖',  'label' => 'Préstamos',          'url' => BASE_URL . '/admin/prestamos'],
    'libros'       => ['icon' => '📚',  'label' => 'Libros',             'url' => BASE_URL . '/admin/libros'],
    'publicaciones'=> ['icon' => '📂',  'label' => 'Publicaciones',      'url' => BASE_URL . '/admin/publicaciones'],
    'tesis'        => ['icon' => '🎓',  'label' => 'Tesis',              'url' => BASE_URL . '/admin/tesis'],
    'usuarios'     => ['icon' => '👥',  'label' => 'Usuarios',           'url' => BASE_URL . '/admin/usuarios'],
    'categorias'   => ['icon' => '🏷️', 'label' => 'Categorías',         'url' => BASE_URL . '/admin/categorias'],
    'estadisticas' => ['icon' => '📊',  'label' => 'Estadísticas',       'url' => BASE_URL . '/admin/estadisticas'],
    'mas-vistos'   => ['icon' => '👁️', 'label' => 'Más Vistos',         'url' => BASE_URL . '/admin/mas-vistos'],
    'reportes'     => ['icon' => '🧾',  'label' => 'Reportes',           'url' => BASE_URL . '/admin/reportes'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> – Biblioteca Dra. Mery Navas</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/img/logoSuperarse.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'superarse-morado-oscuro': '#1b4785ff',
                        'superarse-morado-medio':  '#479990ff',
                        'superarse-rosa':          '#164c7eff',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* SweetAlert2 encima de los modales */
        .swal2-container { z-index: 20000 !important; }
        .swal2-popup      { z-index: 20001 !important; }

        /* Inputs de formulario reutilizables */
        .input {
            width: 100%;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        /* Sidebar slide-in en móvil */
        @media (max-width: 1023px) {
            #adminSidebar {
                transform: translateX(-100%);
                transition: transform .3s ease-in-out;
            }
            #adminSidebar.open {
                transform: translateX(0);
            }

            .admin-panel-popover {
                left: 50%;
                right: auto;
                transform: translateX(-50%);
                width: calc(100vw - 1.5rem);
                max-width: 24rem;
            }
        }
    </style>

    <!-- Variables globales JS disponibles en todos los módulos -->
    <script>
        const BASE_URL     = "<?= rtrim(BASE_URL, '/') ?>";
        const DEFAULT_COVER = `${BASE_URL}/assets/img/default-cover.png`;
    </script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col lg:flex-row">

    <!-- =========================================================
         SIDEBAR
    ========================================================== -->
    <aside id="adminSidebar"
            class="fixed lg:static top-0 left-0 z-40 h-screen w-[min(18rem,85vw)] lg:w-64
                  bg-gradient-to-b from-[#1b4785] to-[#2a6596]
                  text-white flex flex-col shadow-xl shrink-0
                lg:translate-x-0">

        <!-- Branding -->
        <div class="flex items-center gap-3 px-4 py-5 border-b border-white/20 shrink-0">
            <img src="<?= BASE_URL ?>/assets/img/LOGO SUPERARSE PNG-02.png"
                 class="h-10 w-auto" alt="Logo Superarse">
            <span class="font-bold text-sm leading-tight">
                Biblioteca<br>Dra. Mery Navas
            </span>
        </div>

        <!-- Navegación -->
        <nav class="flex-1 overflow-y-auto py-4 px-2">
            <?php foreach ($sidebarModules as $key => $mod): ?>
                <?php $isActive = ($activeModule ?? '') === $key; ?>
                <a href="<?= $mod['url'] ?>"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg mb-1 text-sm
                          transition-colors duration-150
                          <?= $isActive
                              ? 'bg-white/20 font-semibold text-white'
                              : 'text-white/80 hover:bg-white/10 hover:text-white' ?>">
                    <span class="text-lg leading-none w-6 text-center"><?= $mod['icon'] ?></span>
                    <span><?= htmlspecialchars($mod['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Usuario + Logout -->
        <div class="px-4 py-4 border-t border-white/20 shrink-0">
            <p class="text-sm font-semibold truncate">
                <?= htmlspecialchars($_SESSION['nombres_completos'] ?? 'Administrador') ?>
            </p>
            <p class="text-xs text-white/70 mb-3">💼 Administrador</p>
            <a href="<?= BASE_URL ?>/logout"
               class="block text-center bg-red-600/80 hover:bg-red-600
                      text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                🚪 Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Overlay móvil -->
    <div id="sidebarOverlay"
            class="hidden fixed inset-0 bg-black/40 z-30 lg:hidden"
         onclick="closeSidebar()"></div>

    <!-- =========================================================
         WRAPPER PRINCIPAL
    ========================================================== -->
    <div class="flex flex-col flex-1 min-h-screen min-w-0 overflow-x-hidden overflow-y-visible">

        <!-- Barra superior -->
        <header class="bg-gradient-to-r from-[#1b4785] to-[#479990] text-white
                       shadow-md px-4 py-3
                   flex items-center justify-between gap-3 shrink-0 sticky top-0 z-20 overflow-visible">

            <div class="flex items-center gap-3">
                <!-- Hamburger en móvil -->
                <button onclick="toggleSidebar()"
                    class="lg:hidden text-white text-xl focus:outline-none"
                        aria-label="Abrir menú">☰</button>

                <h1 class="font-bold text-base sm:text-lg">
                    <?= htmlspecialchars($pageTitle ?? 'Administración') ?>
                </h1>
            </div>

            <!-- Campana de notificaciones -->
            <div class="relative">
                <button id="btnCampana"
                        class="relative text-2xl focus:outline-none hover:scale-110 transition"
                        aria-label="Notificaciones">
                    🔔
                    <span id="badgePendientes"
                          class="hidden absolute -top-2 -right-2 bg-red-600 text-white
                                 text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
                </button>

                <div id="panelSolicitudes"
                     class="admin-panel-popover hidden absolute right-0 mt-3 w-[90vw] sm:w-96
                           bg-white text-gray-800 rounded-xl shadow-lg border border-gray-200 z-[120]">
                    <div class="px-4 py-3 font-semibold bg-gray-100 rounded-t-xl">
                        Solicitudes pendientes
                    </div>
                    <ul id="listaSolicitudes" class="divide-y max-h-72 overflow-y-auto">
                        <li class="px-4 py-4 text-sm text-center text-gray-500">Cargando...</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/admin/prestamos"
                       class="block text-center py-2 bg-gray-50 text-sm font-semibold
                              text-[#479990] hover:bg-gray-100 rounded-b-xl">
                        Ver todas las solicitudes →
                    </a>
                </div>
            </div>
        </header>

        <!-- Contenido de la página -->
        <main class="flex-1 overflow-y-auto p-3 sm:p-6">
            <div class="w-full max-w-[1400px] mx-auto">
