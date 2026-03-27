    <heather>
        <nav class="bg-gradient-to-r from-superarse-morado-oscuro to-superarse-morado-medio text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

        <!-- IZQUIERDA: Logo + Título -->
        <div class="flex items-center gap-3 min-w-0">
            <a href="<?= BASE_URL ?>/" class="shrink-0">
                <img src="<?= BASE_URL ?>/assets/img/LOGO SUPERARSE PNG-02.png"
                     alt="Logo"
                     class="h-8 sm:h-9 md:h-10 w-auto">
            </a>

            <h1 class="font-bold text-lg sm:text-xl md:text-2xl truncate">
                📚 Biblioteca Dra. Mery Navas
            </h1>
        </div>

        <!-- CENTRO: Menú (desktop) -->
        <div class="hidden md:flex items-center gap-6 font-semibold">
            <a href="<?= BASE_URL ?>/libros/catalogo" class="hover:underline">Libros</a>
            <a href="<?= BASE_URL ?>/tesis" class="hover:underline">Tesis</a>
            <a href="<?= BASE_URL ?>/publicaciones" class="hover:underline">Publicaciones</a>
           <a href="https://elibro.net/es/lc/superarse/login_usuario/" target="_blank" class="font-bold text-base hover:scale-105 transition"> <span class="text-gray-300">e</span><span class="text-[#C4161C]">Libro</span></a>

        </div>

        <!-- DERECHA: Usuario + Auth -->
        <div class="flex items-center gap-3 sm:gap-5">

            <!-- Usuario (oculto en móvil) -->
            <div class="text-right hidden sm:block leading-tight">
                <p class="font-semibold text-sm md:text-base">
                    <?php echo htmlspecialchars($_SESSION['nombres_completos'] ?? 'Invitado'); ?>
                </p>
                <p class="text-xs text-white/80">
                    👤 <?php echo $usuarioLogueado ? 'Estudiante' : 'Visitante'; ?>
                </p>
            </div>

            <!-- Botón Auth -->
            <?php if ($usuarioLogueado): ?>
                <a href="<?= BASE_URL ?>/logout"
                   class="bg-superarse-rosa hover:bg-red-600 px-3 sm:px-4 py-2 rounded-lg font-semibold text-sm transition whitespace-nowrap">
                    🚪 <span class="hidden sm:inline">Cerrar Sesión</span>
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login"
                   class="bg-white text-superarse-morado-oscuro font-semibold px-3 sm:px-4 py-2 rounded-lg text-sm hover:scale-105 transition whitespace-nowrap">
                    🔐 <span class="hidden sm:inline">Iniciar Sesión</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- MENÚ MÓVIL -->
    <div class="md:hidden bg-superarse-morado-medio px-4 py-2 flex justify-center gap-6 text-sm font-semibold">
        <a href="<?= BASE_URL ?>/libros/catalogo" class="hover:underline">Libros</a>
        <a href="<?= BASE_URL ?>/tesis" class="hover:underline">Tesis</a>
        <a href="<?= BASE_URL ?>/publicaciones" class="hover:underline">Publicaciones</a>
    </div>
</nav>
    </heather>