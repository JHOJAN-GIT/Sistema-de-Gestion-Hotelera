<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP 1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado
session_start();
require_once("../Conexion/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $conn = Cconexion::ConexionBD();

    if ($conn) {
        // La consulta busca la combinación exacta de correo y contraseña
        $sql = "SELECT * FROM Usuarios WHERE Correo = ? AND Contrasena = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$correo, $contrasena]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $_SESSION['id'] = $usuario['IdUsuario'];
            $_SESSION['nombre'] = $usuario['Nombre'];
            $_SESSION['correo'] = $usuario['Correo'];
            $_SESSION['rol'] = $usuario['Rol'];

            // Redirección según el rol encontrado
            if ($usuario['Rol'] === 'admin') {
                header("Location: /proyecto_hp_admin/Administrador/inicioAdmin.php");
                exit();
            } elseif ($usuario['Rol'] === 'cliente') {
                header("Location: /proyecto_hp_admin/Clientes/inicioClientes.php");
                exit();
            } else {
                $error = "Acceso denegado: Rol no reconocido.";
            }
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } else {
        $error = "Error de conexión con el servidor.";
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Iniciar Sesión | Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <!-- Tailwind Configuration -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "error-container": "#ffdad6",
                        "surface-container-highest": "#e2e2e2",
                        "on-secondary-fixed-variant": "#4b4822",
                        "surface-container-high": "#e8e8e8",
                        "on-surface-variant": "#4d4632",
                        "background": "#f9f9f9",
                        "primary-container": "#ffd600",
                        "on-secondary-fixed": "#1e1c00",
                        "on-secondary": "#ffffff",
                        "tertiary-fixed": "#e2e2e2",
                        "secondary-fixed-dim": "#cdc897",
                        "tertiary-fixed-dim": "#c6c6c6",
                        "primary-fixed": "#ffe170",
                        "on-primary-fixed-variant": "#544600",
                        "secondary-fixed": "#eae4b1",
                        "surface-container-low": "#f3f3f4",
                        "secondary": "#636037",
                        "on-primary": "#ffffff",
                        "surface-tint": "#705d00",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "inverse-primary": "#e9c400",
                        "outline-variant": "#d0c6ab",
                        "secondary-container": "#e7e1ae",
                        "primary": "#705d00",
                        "outline": "#7f775f",
                        "on-tertiary-fixed": "#1b1b1b",
                        "surface": "#f9f9f9",
                        "tertiary-container": "#d9d9d9",
                        "on-secondary-container": "#67643b",
                        "surface-bright": "#f9f9f9",
                        "tertiary": "#5e5e5e",
                        "on-primary-container": "#705d00",
                        "inverse-surface": "#2f3131",
                        "on-tertiary-container": "#5e5e5e",
                        "surface-dim": "#dadada",
                        "surface-container": "#eeeeee",
                        "inverse-on-surface": "#f0f1f1",
                        "primary-fixed-dim": "#e9c400",
                        "on-background": "#1a1c1c",
                        "error": "#ba1a1a",
                        "on-surface": "#1a1c1c",
                        "surface-variant": "#e2e2e2",
                        "on-tertiary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "on-primary-fixed": "#221b00",
                        "on-tertiary-fixed-variant": "#474747"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "stack-sm": "8px",
                        "container-max": "1440px",
                        "stack-lg": "32px",
                        "margin-page": "40px",
                        "unit": "8px",
                        "gutter": "24px",
                        "card-padding": "24px",
                        "stack-md": "16px"
                    },
                    "fontFamily": {
                        "inter": ["Inter", "sans-serif"]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .input-premium:focus {
            box-shadow: 0 0 0 2px #ffd600;
            border-color: #1a1c1c !important;
        }
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script type="text/javascript">
        window.history.forward();
        function noBack() {
            window.history.forward();
        }
    </script>
</head>
<body class="bg-background text-on-surface font-inter antialiased overflow-hidden" onload="noBack();" onpageshow="if (event.persisted) noBack();">

<main class="min-h-screen w-full flex flex-col md:flex-row">
    <!-- ¡AQUÍ VA EL LOGO NUEVO! -->
        <!-- Logo Flotante en la Esquina Superior Izquierda -->
        <div class="absolute top-4 left-4 z-50">
            <img src="../Imagenes/logo.png" alt="Logo Hospedaje Ayacucho" class="h-20 w-auto object-contain filter drop-shadow-md">
        </div>

    <!-- Left Side: Visual/Branding -->
    <section class="hidden md:flex md:w-1/2 lg:w-3/5 bg-on-background relative items-center justify-center overflow-hidden">
        <!-- Decorative Pattern Overlay -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#ffd600 1px, transparent 1px); background-size: 24px 24px;"></div>
        
        <div class="relative z-10 p-10 text-white max-w-xl">
            <span class="inline-block px-3 py-1 bg-primary-container text-on-primary-container font-bold text-xs uppercase tracking-wider mb-4 rounded-lg">
                Premium Management
            </span>
            <h1 class="text-5xl font-black tracking-tighter mb-4 leading-tight">
                Hospedaje <br><span class="text-primary-container">Ayacucho</span>
            </h1>
            <p class="text-lg text-surface-variant max-w-md leading-relaxed">
                Gestión hotelera de alta precisión. Optimice sus reservas, administre sus huéspedes y controle sus operaciones desde un solo lugar.
            </p>
            <div class="mt-12 grid grid-cols-2 gap-6">
                <div class="flex flex-col gap-1">
                    <span class="text-3xl font-bold text-primary-container">99.9%</span>
                    <span class="text-xs font-bold uppercase text-surface-variant">Disponibilidad</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-3xl font-bold text-primary-container">24/7</span>
                    <span class="text-xs font-bold uppercase text-surface-variant">Soporte Técnico</span>
                </div>
            </div>
        </div>

        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img alt="Hotel Management Interface Background" class="w-full h-full object-cover opacity-30 mix-blend-luminosity" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBclf4vFWM4u2ao1lIq70mLdptocCsaV1TFsH0a9k8BI6HPzub3ryWhGEfp_fvi9YpHbsWauBuydIjHb_OPQcSGUc38RLoeTxT_Te-Jg0skbCQe5iHffRiUHfx3w09TmvqtJ2yVHEUSs_6LhZ37t9oBDvyAbj9jAuU9qcB8ii8pTMVM6t0OPd88nB0lo9NXFmc2yg0deXEmyev7RTRha2l35dWfVwGq2-NxXqOQkLHUQ4-RXa9m-oPposCv5XSygKkevPxx56uQF54">
        </div>
    </section>

    <!-- Right Side: Login Form -->
    <section class="w-full md:w-1/2 lg:w-2/5 bg-surface flex items-center justify-center p-8 relative">
        <!-- Mobile Logo -->
        <div class="absolute top-8 left-8 md:hidden">
            <h2 class="text-2xl font-black text-primary tracking-tighter">HA</h2>
        </div>

        <div class="w-full max-w-sm fade-in-up">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-on-surface mb-2 tracking-tight">Bienvenido a Hospedaje Ayacucho</h2>
                <p class="text-sm text-on-surface-variant">Iniciar Sesión para continuar a la consola administrativa.</p>
            </div>

            <!-- Contenedor de Errores PHP Dinámico -->
            <?php if (isset($error)): ?>
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-error text-error text-sm font-medium rounded-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">error</span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="flex flex-col gap-4">
                <!-- Email Field -->
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-bold text-on-surface" for="correo">Correo Electrónico</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">
                            mail
                        </span>
                        <input class="w-full pl-12 pr-4 py-3.5 bg-surface-container-lowest border border-outline-variant rounded-xl focus:outline-none input-premium transition-all duration-200 text-on-surface" id="correo" name="correo" placeholder="nombre@ejemplo.com" required type="email">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <label class="text-sm font-bold text-on-surface" for="contrasena">Contraseña</label>
                        <a class="text-xs font-bold text-primary hover:underline transition-all" href="recuperarContraseña.php">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">
                            lock
                        </span>
                        <input class="w-full pl-12 pr-12 py-3.5 bg-surface-container-lowest border border-outline-variant rounded-xl focus:outline-none input-premium transition-all duration-200 text-on-surface" id="contrasena" name="contrasena" placeholder="••••••••" required type="password">
                        <button class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface" onclick="togglePassword()" type="button">
                            <span class="material-symbols-outlined text-[20px]" id="password-toggle">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Stay logged in -->
                <div class="flex items-center gap-2 py-1">
                    <input class="w-4 h-4 rounded border-outline text-primary focus:ring-primary" id="remember" type="checkbox">
                    <label class="text-xs text-on-surface-variant cursor-pointer select-none" for="remember">Recordar mi sesión</label>
                </div>

                <!-- Submit Button -->
                <button class="mt-2 w-full bg-primary-container text-on-primary-container py-4 rounded-xl font-bold hover:brightness-95 active:scale-[0.98] transition-all shadow-sm flex items-center justify-center gap-2" type="submit">
                    Ingresar
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </form>

            <!-- Registration Link -->
            <div class="mt-8 text-center">
                <p class="text-xs text-on-surface-variant">
                    ¿No tienes una cuenta? 
                    <a class="text-primary font-bold hover:underline ml-1" href="../Login/registrarCuenta.php">Regístrate aquí</a>
                </p>
            </div>

            <!-- Footer Meta -->
            <div class="mt-12 pt-6 border-t border-outline-variant">
                <div class="flex items-center gap-4 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[20px] shrink-0">security</span>
                    <p class="text-[11px] leading-snug">Acceso seguro mediante encriptación SSL de 256 bits. Sus datos están protegidos bajo protocolos de seguridad industrial.</p>
                </div>
            </div>
        </div>

        <!-- Decorative Micro-interaction Element -->
        <div class="absolute bottom-10 right-10 opacity-10 hidden lg:block">
            <span class="material-symbols-outlined text-[120px]" style="font-variation-settings: 'FILL' 1;">hotel_class</span>
        </div>
    </section>
</main>

<!-- UI Micro-interactions Scripts -->
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('contrasena');
        const icon = document.getElementById('password-toggle');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            icon.textContent = 'visibility';
        }
    }

    // Interceptar atrás en el navegador
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
    
</script>
<script>localStorage.setItem('sidebarOpen', '0');</script>
</body>
</html>