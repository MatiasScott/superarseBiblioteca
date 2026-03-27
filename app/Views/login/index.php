<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biblioteca Dra. Mery Navas</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
    display: none;
}

input[type="password"]::-webkit-credentials-auto-fill-button {
    visibility: hidden;
}
</style>

</head>

<body
    class="bg-gradient-to-r from-superarse-morado-oscuro via-superarse-morado-medio to-superarse-rosa min-h-screen flex flex-col">

    <header class="bg-transparent text-white w-full py-4 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 text-center">

        <a href="<?= BASE_URL ?>/"> <!-- 👈 Ruta de regreso al home -->
            <img src="<?= BASE_URL ?>/assets/img/LOGO SUPERARSE PNG-02.png"
                 onerror="this.onerror=null; this.src='/assets/logos/LOGO SUPERARSE PNG-02.png';"
                 alt="Logo de Superarse"
                 class="logo h-20 w-auto mx-auto mb-4"
                 style="cursor: pointer;">
        </a>

    </div>
</header>


    <main class="flex-grow flex items-center justify-center p-4 pt-10 pb-10">
        <div class="w-full max-w-md">
            <div class="bg-white/95 backdrop-blur-md p-8 rounded-2xl shadow-2xl border border-white/20">
                <!-- Logo y Título -->
                <div class="text-center mb-9">
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-superarse-morado-oscuro to-superarse-morado-medio bg-clip-text text-transparent mb-2">
                        📚 Biblioteca Dra. Mery Navas
                    </h1>
                    <p class="text-gray-500 text-sm font-medium">Accede a tu cuenta</p>
                </div>

                <!-- Mensaje de error -->
                <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700 text-sm font-medium">
                        <?php
                        $errorMessages = [
                            'campos_vacios' => '⚠️ Por favor completa todos los campos',
                            'credenciales_invalidas' => '❌ Cédula o contraseña incorrecta',
                            'usuario_inactivo' => '🚫 Tu cuenta está inactiva',
                            'metodo_invalido' => '⚠️ Método de solicitud inválido'
                        ];
                        echo $errorMessages[$_GET['error']] ?? '❌ Error desconocido';
                        ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Mensaje de éxito -->
                <?php if (isset($_GET['success']) && $_GET['success'] === 'logout'): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-700 text-sm font-medium">✅ Sesión cerrada correctamente</p>
                </div>
                <?php endif; ?>

                <form id="login-Form" action="<?= BASE_URL ?>/login/check" method="POST"
                    class="space-y-5">
                    <!-- Campo Cédula -->
                    <div>
                        <label for="cedula" class="block text-gray-700 text-sm font-semibold mb-2 flex items-center">
                            <span class="text-lg mr-2">🆔</span> Cédula de Identidad
                        </label>
                        <input type="text" id="cedula" name="cedula" required
                            maxlength="10" minlength="10" pattern="\d{10}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-superarse-morado-medio focus:ring-2 focus:ring-superarse-morado-medio/20 transition-all duration-200 placeholder-gray-400"
                            placeholder="Ej: 0912345678"
                            title="La cédula debe tener exactamente 10 números">
                    </div>

                    <!-- Campo Contraseña -->
   <div>
    <label for="contrasena"
        class="block text-gray-700 text-sm font-semibold mb-2 flex items-center gap-2">
        <i class="fa-solid fa-lock text-gray-500"></i>
        Contraseña
    </label>

    <div class="relative">
        <input
            type="password"
            id="contrasena"
            name="contrasena"
            required
            placeholder="Ingresa tu contraseña"
            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg
                   focus:outline-none focus:border-superarse-morado-medio
                   focus:ring-2 focus:ring-superarse-morado-medio/20
                   transition-all duration-200 placeholder-gray-400"
        >

        <!-- Ojito -->
        <button
            type="button"
            onclick="togglePassword()"
            class="absolute inset-y-0 right-3 flex items-center
                   text-gray-500 hover:text-superarse-morado-oscuro transition"
        >
            <i id="iconEye" class="fa-solid fa-eye"></i>
        </button>
    </div>
</div>

                    <!-- Botón de Ingreso -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-superarse-morado-oscuro to-superarse-morado-medio hover:shadow-lg text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2 mt-6">
                        <span> Iniciar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-superarse-rosa text-white">
                    <h5 class="modal-title" id="errorModalLabel">Error de Acceso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center text-gray-700" id="errorMessage">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn bg-superarse-morado-medio text-white"
                        data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

        <footer class="text-white text-center py-4 mt-10">
        <p class="text-sm">&copy; All Rights Reserved. Designed by Instituto Superarse</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.getElementById('login-Form').addEventListener('submit', function(e) {
        const cedula = document.getElementById('cedula').value;
        if (cedula.length !== 10) {
            e.preventDefault();
            alert("La cédula debe contener exactamente 10 números.");
        }
    });
    </script>
    <script>
function togglePassword() {
    const input = document.getElementById("contrasena");
    const icon = document.getElementById("iconEye");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

   

</body>

</html>
