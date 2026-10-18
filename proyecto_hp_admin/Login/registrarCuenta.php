<?php
session_start();
// Lógica para reiniciar el formulario
if (isset($_POST['cancelar_registro'])) {
    session_unset();         // Borra todas las variables de sesión actuales
    $_SESSION['paso'] = 1;    // Forzamos que inicie en el paso 1
    header("Location: registrarCuenta.php");
    exit();
}

// Si no hay paso definido, por defecto es 1
if (!isset($_SESSION['paso'])) {
    $_SESSION['paso'] = 1;
}
include_once("../Conexion/conexion.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

$exito = '';
$error = '';
$mostrarVerificacion = false; // Inicializar

$rolSeleccionado = $_POST['rol']?? $_SESSION['rol_temp'] ?? '';

$paso = $_SESSION['paso'] ?? 1;

// ================= ENVIAR CODIGO =================
if (isset($_POST['enviar_codigo'])) {
    $correoPost = $_POST['correo'];
    $rolPost = $_POST['rol'];
    // Dentro de if (isset($_POST['enviar_codigo']))
    $_SESSION['nombre_temp'] = trim(preg_replace('/\s+/', ' ', $_POST['nombre']));

    // --- VALIDACIÓN MODIFICADA: Verificar si el correo ya existe CON EL MISMO ROL ---
    $conn = Cconexion::ConexionBD();
    $checkEmailRol = $conn->prepare("SELECT COUNT(*) FROM Usuarios WHERE correo = ? AND rol = ?");
    $checkEmailRol->execute([$correoPost, $rolPost]);
    $existeRegistro = $checkEmailRol->fetchColumn();

    if ($existeRegistro > 0) {
        // Si ya existe ese correo con ese rol, lanzamos el error
        $error = "Este correo ya está registrado como " . strtoupper($rolPost) . ". Intenta con otro rol o inicia sesión.";
    } else {
        // --- Si no existe esa combinación, procedemos a enviar el código ---
        $_SESSION['nombre_temp'] = $_POST['nombre'];
        $_SESSION['correo_temp'] = $correoPost;
        $_SESSION['rol_temp'] = $rolPost;
        $rolSeleccionado = $rolPost;
        
        if ($rolSeleccionado === 'admin' || $rolSeleccionado === 'cliente') {
            $mail = new PHPMailer(true);
            
            // Lógica de generación de códigos
            if ($rolSeleccionado === 'admin') {
                $codigo_usuario = rand(100000, 999999);
                $codigo_admin = rand(100000, 999999);
                $_SESSION['codigo_usuario'] = $codigo_usuario;
                $_SESSION['codigo_admin'] = $codigo_admin;
            } else {
                $codigo_usuario = rand(100000, 999999);
                $_SESSION['codigo_usuario'] = $codigo_usuario;
            }

            try {
                // ... Configuración de PHPMailer (Host, Username, Password, etc.) ...
                
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'jhojanantezana96@gmail.com';
                $mail->Password = 'mffv gefb imeo wrxp';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('jhojanantezana96@gmail.com', 'Hospedaje Ayacucho');
                $mail->isHTML(true);

                $mail->addAddress($correoPost);
                $mail->Subject = 'Verificación de correo';
                $mail->Body = "Tu código de verificación es: <b>$codigo_usuario</b>";
                $mail->send();

                if ($rolSeleccionado === 'admin') {
                    $mail->clearAddresses();
                    $mail->addAddress('jhojanantezana96@gmail.com');
                    $mail->Subject = 'Autorización de nuevo administrador';
                    $mail->Body = "Código de autorización: <b>$codigo_admin</b>";
                    $mail->send();
                }

                $exito = "Si el correo es válido, se enviará un código de verificación";
                $_SESSION['paso'] = 2;
                $paso = 2;

            } catch (Exception $e) {
                $error = "No se pudo enviar el correo en este momento.";
            }
        }
    }
}

// ================= REGISTRAR =================
if (isset($_POST['registrar'])) {
    $nombre = $_SESSION['nombre_temp'];
    $correo = $_SESSION['correo_temp'];
    $rol = $_SESSION['rol_temp'];
    $contrasena = $_POST['contrasena'];
    $confirmar = $_POST['confirmar_contrasena'];

    // 1. Validar Fortaleza y Coincidencia (Seguridad del Servidor)
    if ($contrasena !== $confirmar) {
        $error = "Las contraseñas ingresadas no coinciden.";
    } 
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/', $contrasena)) {
        $error = "La contraseña no cumple los requisitos mínimos.";
    }

    // 2. Si las claves están bien, validar códigos
if (empty($error)) {
    $input_codigo_usuario = $_POST['codigo_usuario'] ?? '';
    $input_codigo_admin = $_POST['codigo_admin'] ?? '';

    if ($rol === 'admin') {
        // CASO 1: Ambos códigos están mal introducidos
        if ($input_codigo_usuario != ($_SESSION['codigo_usuario'] ?? '') && $input_codigo_admin != ($_SESSION['codigo_admin'] ?? '')) {
            $error = "Códigos de verificación incorrectos.";
        }
        // CASO 2: Solo el código enviado al correo está mal
        elseif ($input_codigo_usuario != ($_SESSION['codigo_usuario'] ?? '')) {
            $error = "Código de verificación de usuario incorrecto.";
        } 
        // CASO 3: Solo el código de autorización de administrador está mal
        elseif ($input_codigo_admin != ($_SESSION['codigo_admin'] ?? '')) {
            $error = "Código de autorización de administrador incorrecto.";
        }
    } else {
        // Para rol cliente solo se evalúa el código de usuario
        if ($input_codigo_usuario != ($_SESSION['codigo_usuario'] ?? '')) {
            $error = "Código de verificación incorrecto.";
        }
    }
}

    // 3. Si todo lo anterior es correcto, validar duplicidad en BD e Insertar
    if (empty($error)) {
        try {
            $conn = Cconexion::ConexionBD();
            $checkPass = $conn->prepare("SELECT contrasena FROM Usuarios WHERE correo = ?");
            $checkPass->execute([$correo]);
            $passwordsExistentes = $checkPass->fetchAll(PDO::FETCH_COLUMN);

            $esMismaPassword = false;
            foreach ($passwordsExistentes as $p) {
                if ($contrasena === $p) { $esMismaPassword = true; break; }
            }

            if ($esMismaPassword) {
                $error = "La contraseña no puede ser igual a la de su otra cuenta.";
            } else {
                $stmt = $conn->prepare("INSERT INTO Usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $correo, $contrasena, $rol]);
                session_unset();
                session_destroy();
                echo "<script>alert('Cuenta creada exitosamente.'); window.location.href='login.php';</script>";
                exit();
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    $paso = 2; // Asegura que si algo falló, sigamos en el formulario
}
?>

<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Registro de Cuenta - Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "error-container": "#ffdad6",
                    "background": "#f9f9f9",
                    "primary-container": "#ffd600",
                    "on-secondary": "#ffffff",
                    "primary": "#705d00",
                    "surface": "#f9f9f9",
                    "on-primary-container": "#705d00",
                    "on-background": "#1a1c1c",
                    "error": "#ba1a1a",
                    "on-surface": "#1a1c1c",
                    "on-error-container": "#93000a"
            },
            "fontFamily": {
                    "body-md": ["Inter"]
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
            animation: fadeInUp 0.5s ease-out forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md antialiased overflow-x-hidden">

<main class="min-h-screen w-full flex flex-col md:flex-row">
    <section class="hidden md:flex md:w-1/2 lg:w-3/5 bg-on-background relative items-center justify-center overflow-hidden">
        <div class="absolute top-10 left-10 z-20 flex items-center gap-3">
            <img src="../Imagenes/logo.png" alt="Logo Hospedaje" class="h-10 w-auto object-contain">
            <span class="text-white font-bold text-lg tracking-wider">HOSPEDAJE AYACUCHO</span>
        </div>
        
        <div class="relative z-10 text-center px-12 max-w-lg">
            <span class="material-symbols-outlined text-primary-container text-[80px] mb-6 block">person_add</span>
            <h1 class="text-white text-4xl font-extrabold tracking-tight mb-4 leading-tight">Únete a Nuestra Plataforma</h1>
            <p class="text-gray-400 text-lg">Crea una cuenta para gestionar tus accesos y disfrutar de una experiencia completamente optimizada.</p>
        </div>
        
        <div class="absolute bottom-10 right-10 opacity-10 hidden lg:block">
            <span class="material-symbols-outlined text-[120px] text-white" style="font-variation-settings: 'FILL' 1;">hotel_class</span>
        </div>
    </section>

    <section class="flex-1 flex flex-col justify-center items-center px-6 py-12 md:px-12 lg:px-24 bg-surface relative">
        <div class="w-full max-w-md space-y-8 fade-in-up">
            
            <div class="md:hidden flex flex-col items-center mb-4">
                <img src="../Imagenes/logo.png" alt="Logo Hospedaje" class="h-16 w-auto mb-2 object-contain">
                <h2 class="text-2xl font-bold text-on-background">Hospedaje Ayacucho</h2>
            </div>

            <div class="space-y-2">
                <h2 class="text-2xl md:text-3xl font-black text-on-background tracking-tight">¡Crea tu Cuenta!</h2>
                <p class="text-sm text-gray-500">Regístrate de manera segura rellenando los siguientes datos.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="flex items-center gap-3 p-4 bg-error-container text-on-error-container rounded-xl text-sm border border-red-200 transition-all">
                    <span class="material-symbols-outlined shrink-0 text-xl">error</span>
                    <p class="font-medium"><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($exito)): ?>
                <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm border border-emerald-200 transition-all">
                    <span class="material-symbols-outlined shrink-0 text-xl text-emerald-600">check_circle</span>
                    <p class="font-medium"><?php echo $exito; ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-8 space-y-5">
                
                <div class="space-y-4">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">badge</span>
                        <select name="rol" id="rol" required <?php echo ($paso == 2) ? 'style="pointer-events: none; background: #f3f4f6; color: #6b7280;"' : ''; ?> class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm font-medium text-on-surface focus:outline-none focus:ring-0 transition-colors">
                            <option value="">Selecciona rol</option>
                            <option value="admin" <?php if($rolSeleccionado=="admin") echo "selected"; ?>>Administrador</option>
                            <option value="cliente" <?php if($rolSeleccionado=="cliente") echo "selected"; ?>>Cliente</option>
                        </select>
                    </div>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">person</span>
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" required 
                               value="<?php echo $_POST['nombre'] ?? $_SESSION['nombre_temp'] ?? ''; ?>"
                               <?php echo ($paso == 2) ? 'readonly class="w-full pl-12 pr-4 py-3.5 bg-gray-100 text-gray-500 border border-gray-200 rounded-xl text-sm focus:outline-none"' : 'class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors"'; ?>
                               oninput="formatearNombre(this)">
                    </div>

                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">mail</span>
                        <input type="email" id="correo" name="correo" placeholder="Correo electrónico" required 
                               value="<?php echo $_POST['correo'] ?? $_SESSION['correo_temp'] ?? ''; ?>"
                               <?php echo ($paso == 2) ? 'readonly class="w-full pl-12 pr-4 py-3.5 bg-gray-100 text-gray-500 border border-gray-200 rounded-xl text-sm focus:outline-none"' : 'class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors"'; ?>>
                    </div>
                </div>

                <?php if ($paso == 1): ?>
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center gap-2 p-3 bg-amber-50 text-amber-800 rounded-xl text-xs border border-amber-200">
                            <span class="material-symbols-outlined text-base text-amber-600">info</span>
                            <p>Si el correo es válido, se enviará un código de verificación.</p>
                        </div>
                        <button id="btnCodigo" type="submit" name="enviar_codigo" onclick="return validarFormulario()" class="w-full flex justify-center items-center gap-2 bg-primary-container text-on-primary-container font-bold py-3.5 px-4 rounded-xl hover:bg-yellow-400 active:scale-[0.99] transition-all shadow-md shadow-yellow-400/20 text-sm tracking-wide">
                            <span>Continuar</span>
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($paso == 2): ?>
                    <div class="space-y-4 border-t border-gray-200 pt-5 fade-in-up">
                        
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">pin</span>
                            <input type="text" name="codigo_usuario" placeholder="Código enviado a tu correo" required class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors">
                        </div>

                        <?php if ($rolSeleccionado === 'admin'): ?>
                            <div class="relative fade-in-up">
                                <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">admin_panel_settings</span>
                                <input type="text" name="codigo_admin" placeholder="Código de autorización (admin)" required class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors">
                            </div>
                        <?php endif; ?>

                        <div class="space-y-1">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">lock</span>
                                <input type="password" id="contrasena" name="contrasena" placeholder="Nueva Contraseña" required onkeyup="validarFortaleza()" class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors">
                            </div>
                            <div id="mensaje-fortaleza" class="text-xs font-bold pl-2 min-h-[16px]"></div>
                        </div>

                        <div class="space-y-1">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">lock_status</span>
                                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="Confirmar Contraseña" required onkeyup="comprobarCoincidencia()" class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors">
                            </div>
                            <div id="mensaje-coincidencia" class="text-xs font-bold pl-2 min-h-[16px]"></div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <button type="submit" name="registrar" id="btnRegistrar" class="w-full flex justify-center items-center gap-2 bg-primary-container text-on-primary-container font-bold py-3.5 px-4 rounded-xl hover:bg-yellow-400 disabled:opacity-50 disabled:pointer-events-none active:scale-[0.99] transition-all shadow-md shadow-yellow-400/20 text-sm tracking-wide">
                                <span>Crear Cuenta</span>
                                <span class="material-symbols-outlined text-lg">how_to_reg</span>
                            </button>

                            <button type="submit" name="cancelar_registro" formnovalidate class="w-full flex justify-center items-center gap-2 bg-red-50 text-red-700 font-bold py-3 px-4 rounded-xl hover:bg-red-100 active:scale-[0.99] transition-all text-xs border border-red-200">
                                <span class="material-symbols-outlined text-base">cancel</span>
                                <span>Cancelar y Volver al Inicio</span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            </form>

            <div class="pt-4 text-center border-t border-gray-200">
                <a href="login.php" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-yellow-600 hover:underline transition-all">
                    <span class="material-symbols-outlined text-lg">keyboard_return</span>
                    <span>¿Ya tienes cuenta? Inicia Sesión</span>
                </a>
            </div>

        </div>
    </section>
</main>

<script>
const rol = document.getElementById("rol");
const nombre = document.getElementById("nombre");
const correo = document.getElementById("correo");

function validarRol() {
    if (rol.value === "") {
        alert("Primero debes seleccionar un rol");
        return false;
    }
    return true;
}

if(nombre && correo && rol) {
    nombre.addEventListener("focus", function () {
        if (!validarRol()) this.blur();
    });

    correo.addEventListener("focus", function () {
        if (!validarRol()) this.blur();
    });

    rol.addEventListener("change", function () {
        if (this.value !== "") {
            nombre.disabled = false;
            correo.disabled = false;
        }
    });

    nombre.addEventListener("click", function () {
        if (rol.value === "") alert("Primero debes seleccionar un rol");
    });

    correo.addEventListener("click", function () {
        if (rol.value === "") alert("Primero debes seleccionar un rol");
    });
}

function validarFormulario() {
    if (rol.value === "") {
        alert("Selecciona un rol");
        return false;
    }
    if (nombre.value.trim() === "" || correo.value.trim() === "") {
        alert("Completa nombre y correo");
        return false;
    }
    return true;
}

function validarFortaleza() {
    const pass = document.getElementById('contrasena').value;
    const mensaje = document.getElementById('mensaje-fortaleza');
    const regexFuerte = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/;

    if (pass.length === 0) {
        mensaje.innerHTML = "";
    } else if (regexFuerte.test(pass)) {
        mensaje.innerHTML = "Fortaleza: Fuerte (Segura) ✅";
        mensaje.className = "text-xs font-bold pl-2 text-emerald-600";
    } else {
        mensaje.innerHTML = "Debe tener: 8+ caracteres, Mayúscula, Número y Símbolo (!@#$).";
        mensaje.className = "text-xs font-semibold pl-2 text-amber-600";
    }
    comprobarCoincidencia();
}

function comprobarCoincidencia() {
    const pass1 = document.getElementById('contrasena').value;
    const pass2 = document.getElementById('confirmar_contrasena').value;
    const mensaje = document.getElementById('mensaje-coincidencia');
    const btn = document.getElementById('btnRegistrar');

    if (!btn) return;

    if (pass2.length === 0) {
        mensaje.innerHTML = "";
    } else if (pass1 === pass2) {
        mensaje.innerHTML = "Las contraseñas coinciden ✅";
        mensaje.className = "text-xs font-bold pl-2 text-emerald-600";
        btn.disabled = false;
    } else {
        mensaje.innerHTML = "Las contraseñas no coinciden ❌";
        mensaje.className = "text-xs font-bold pl-2 text-red-600";
        btn.disabled = true;
    }
}

// Formatear Nombre
function formatearNombre(input) {
    let valor = input.value;
    let palabras = valor.toLowerCase().split(' ');
    
    for (let i = 0; i < palabras.length; i++) {
        palabras[i] = palabras[i].charAt(0).toUpperCase() + palabras[i].slice(1);
    }

    if (palabras.length > 5) {
        palabras = palabras.slice(0, 5);
    }
    input.value = palabras.join(' ');
}

// Microinteracción del Botón de Carga al enviar el formulario válido
document.querySelector('form').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]:not([formnovalidate])');
    if (btn && !btn.disabled) {
        btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-on-primary-container" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Procesando solicitud...</span>`;
        btn.style.opacity = "0.85";
    }
});
</script>
</body>
</html>