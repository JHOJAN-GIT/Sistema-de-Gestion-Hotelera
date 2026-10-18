<?php
session_start();
include_once("../Conexion/conexion.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

$error = "";
$exito = "";
$paso = $_SESSION['paso_recu'] ?? 1;

// ================= CANCELAR Y VOLVER AL LOGIN =================
if (isset($_POST['cancelar_regresar_login'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// ================= PASO 1: ENVIAR CÓDIGO =================
if (isset($_POST['enviar_codigo_recu'])) {
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $conn = Cconexion::ConexionBD();
    
    // Verificamos si el correo existe en cualquier rol
    $stmt = $conn->prepare("SELECT rol, Nombre FROM Usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($usuarios) {
        $usuarioCorrecto = null;
        foreach ($usuarios as $u) {
            if ($u['rol'] === $rol) { $usuarioCorrecto = $u; break; }
        }

        if ($usuarioCorrecto) {
            $codigo = rand(100000, 999999);
            $_SESSION['recu_codigo'] = $codigo;
            $_SESSION['recu_correo'] = $correo;
            $_SESSION['recu_rol'] = $rol;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'jhojanantezana96@gmail.com'; 
                $mail->Password = 'mffv gefb imeo wrxp';        
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('jhojanantezana96@gmail.com', 'Hospedaje Ayacucho');
                $mail->addAddress($correo);
                $mail->isHTML(true);
                $mail->Subject = 'Recuperacion de Acceso';
                $mail->Body = "Hola <b>{$usuarioCorrecto['Nombre']}</b>, tu código es: <h2>$codigo</h2>";
                $mail->send();
                $_SESSION['paso_recu'] = 2;
                $paso = 2;
                $exito = "Código enviado con éxito.";
            } catch (Exception $e) { $error = "Error al enviar: " . $mail->ErrorInfo; }
        } else {
            $error = "El correo está registrado, pero no con el rol " . strtoupper($rol) . ". Seleccione el rol correcto.";
        }
    } else {
        $error = "No existe una cuenta asociada a este correo. Revise la dirección ingresada.";
    }
}

// ================= PASO 2: VERIFICAR CÓDIGO =================
if (isset($_POST['verificar_codigo_recu'])) {
    $codigo_input = $_POST['codigo_verificar'];
    
    if ($codigo_input == $_SESSION['recu_codigo']) {
        $_SESSION['paso_recu'] = 3;
        $paso = 3;
    } else {
        $error = "El código ingresado es incorrecto.";
        $paso = 2;
    }
}

// ================= PASO 3: CAMBIAR CONTRASEÑA =================
if (isset($_POST['cambiar_password'])) {
    $nueva_pass = $_POST['nueva_contrasena'];
    $confirmar_pass = $_POST['confirmar_contrasena'];
    $correo = $_SESSION['recu_correo'];
    $rol = $_SESSION['recu_rol'];

    // 1. Validar fortaleza (Mismo Regex del registro)
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/', $nueva_pass)) {
        $error = "La contraseña no cumple los requisitos mínimos.";
        $paso = 3;
    } 
    elseif ($nueva_pass !== $confirmar_pass) {
        $error = "Las contraseñas no coinciden.";
        $paso = 3;
    } else {
        try {
            $conn = Cconexion::ConexionBD();
            
            // 2. VALIDACIÓN NUEVA: Verificar que no sea igual a la contraseña actual del ROL ACTUAL
            $checkCurrent = $conn->prepare("SELECT contrasena FROM Usuarios WHERE correo = ? AND rol = ?");
            $checkCurrent->execute([$correo, $rol]);
            $passActual = $checkCurrent->fetchColumn();

            if ($passActual && $nueva_pass === $passActual) {
                $error = "La nueva contraseña no puede ser igual a su contraseña anterior.";
                $paso = 3;
            } else {
                // Validar que no sea la misma clave del OTRO rol (para evitar conflictos en Login)
                $checkOther = $conn->prepare("SELECT contrasena FROM Usuarios WHERE correo = ? AND rol != ?");
                $checkOther->execute([$correo, $rol]);
                $passOtroRol = $checkOther->fetchColumn();

                if ($passOtroRol && $nueva_pass === $passOtroRol) {
                    $error = "Por seguridad, no puede usar la misma contraseña que tiene en su otro rol.";
                    $paso = 3;
                } else {
                    // 3. ACTUALIZAR EN BD
                    $update = $conn->prepare("UPDATE Usuarios SET contrasena = ? WHERE correo = ? AND rol = ?");
                    $update->execute([$nueva_pass, $correo, $rol]);

                    session_unset();
                    session_destroy();
                    echo "<script>alert('Contraseña actualizada correctamente.'); window.location.href='login.php';</script>";
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
            $paso = 3;
        }
    }
}
?>

<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Recuperar Acceso - Hospedaje Ayacucho</title>
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
            animation: fadeInUp 0.6s ease-out forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md antialiased overflow-x-hidden">

<main class="min-h-screen w-full flex flex-col md:flex-row">
    <section class="hidden md:flex md:w-1/2 lg:w-3/5 bg-on-background relative items-center justify-center overflow-hidden">
        <div class="absolute top-10 left-10 z-20 flex items-center gap-3">
            <img src="../Imagenes/logo.png" alt="Logo Hospedaje Ayacucho" class="h-20 w-auto object-contain filter drop-shadow-md">
            <span class="text-white font-bold text-lg tracking-wider">HOSPEDAJE AYACUCHO</span>
        </div>
        
        <div class="relative z-10 text-center px-12 max-w-lg">
            <span class="material-symbols-outlined text-primary-container text-[80px] mb-6 block">lock_reset</span>
            <h1 class="text-white text-4xl font-extrabold tracking-tight mb-4 leading-tight">¿Olvidaste tus credenciales?</h1>
            <p class="text-gray-400 text-lg">No te preocupes. Sigue nuestro sistema de verificación en tres pasos para restablecer tu contraseña de manera rápida y segura.</p>
        </div>
        
        <div class="absolute bottom-10 right-10 opacity-10 hidden lg:block">
            <span class="material-symbols-outlined text-[120px] text-white" style="font-variation-settings: 'FILL' 1;">hotel_class</span>
        </div>
    </section>

    <section class="flex-1 flex flex-col justify-center items-center px-6 py-12 md:px-12 lg:px-24 bg-surface relative">
        <div class="w-full max-w-md space-y-8 fade-in-up">
            
            <div class="md:hidden flex flex-col items-center mb-4">
                <img src="../Imagenes/logo.png" alt="Logo Hospedaje Ayacucho" class="h-16 w-auto mb-2">
                <h2 class="text-2xl font-bold text-on-background">Hospedaje Ayacucho</h2>
            </div>

            <div class="space-y-2">
                <h2 class="text-2xl md:text-3xl font-black text-on-background tracking-tight">Recuperación de Acceso</h2>
                <p class="text-sm text-gray-500">Completa la validación requerida por el área de seguridad.</p>
            </div>

            <?php if ($error): ?>
                <div class="flex items-center gap-3 p-4 bg-error-container text-on-error-container rounded-xl text-sm border border-red-200 transition-all duration-300">
                    <span class="material-symbols-outlined shrink-0 text-xl">error</span>
                    <p class="font-medium"><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($exito): ?>
                <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm border border-emerald-200 transition-all duration-300">
                    <span class="material-symbols-outlined shrink-0 text-xl text-emerald-600">check_circle</span>
                    <p class="font-medium"><?php echo $exito; ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="mt-8 space-y-6">
                
                <?php if ($paso == 1): ?>
                    <div class="space-y-4">
                        <div class="border-l-4 border-primary pl-3 py-1">
                            <h3 class="text-sm font-bold text-primary uppercase tracking-wider">Paso 1: Identificación</h3>
                            <p class="text-xs text-gray-400">Ingresa tu cuenta registrada en la plataforma.</p>
                        </div>
                        
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">badge</span>
                            <select name="rol" required class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm font-medium text-on-surface focus:outline-none focus:ring-0 transition-colors">
                                <option value="" disabled selected>Selecciona tu Rol Corporativo</option>
                                <option value="admin">Administrador</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </div>

                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">mail</span>
                            <input type="email" name="correo" placeholder="Correo electrónico registrado" required class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-0 transition-colors">
                        </div>
                        
                        <button type="submit" name="enviar_codigo_recu" class="w-full flex justify-center items-center gap-2 bg-primary-container text-on-primary-container font-bold py-3.5 px-4 rounded-xl hover:bg-yellow-400 active:scale-[0.99] transition-all duration-200 shadow-md shadow-yellow-400/20 text-sm tracking-wide">
                            <span>Enviar Código de Seguridad</span>
                            <span class="material-symbols-outlined text-lg">send</span>
                        </button>
                    </div>
                <?php endif; ?>


                <?php if ($paso == 2): ?>
                    <div class="space-y-4">
                        <div class="border-l-4 border-primary pl-3 py-1">
                            <h3 class="text-sm font-bold text-primary uppercase tracking-wider">Paso 2: Verificación</h3>
                            <p class="text-xs text-gray-400">Introduce los 6 dígitos verificadores enviados por correo.</p>
                        </div>

                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">pin</span>
                            <input type="text" name="codigo_verificar" placeholder="Código de 6 dígitos" required maxlength="6" class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm font-mono text-center text-lg tracking-[0.4em] placeholder:font-sans placeholder:text-sm placeholder:tracking-normal focus:outline-none focus:ring-0 transition-colors">
                        </div>

                        <div class="space-y-2 pt-2">
                            <button type="submit" name="verificar_codigo_recu" class="w-full flex justify-center items-center gap-2 bg-primary-container text-on-primary-container font-bold py-3.5 px-4 rounded-xl hover:bg-yellow-400 active:scale-[0.99] transition-all duration-200 shadow-md shadow-yellow-400/20 text-sm tracking-wide">
                                <span>Verificar Identidad</span>
                                <span class="material-symbols-outlined text-lg">verified</span>
                            </button>                      
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($paso == 3): ?>
                    <div class="space-y-4">
                        <div class="border-l-4 border-primary pl-3 py-1">
                            <h3 class="text-sm font-bold text-primary uppercase tracking-wider">Paso 3: Seguridad de Acceso</h3>
                            <p class="text-xs text-gray-400">Establece tu nueva clave de autenticación única.</p>
                        </div>

                        <div class="space-y-1">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">lock</span>
                                <input type="password" id="contrasena" name="nueva_contrasena" placeholder="Nueva Contraseña" required onkeyup="validarFortaleza()" class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors">
                            </div>
                            <div id="mensaje-fortaleza" class="text-xs font-bold pl-2 min-h-[16px]"></div>
                        </div>

                        <div class="space-y-1">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-400 text-xl pointer-events-none">lock_status</span>
                                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="Confirmar Nueva Contraseña" required onkeyup="comprobarCoincidencia()" class="input-premium w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-0 transition-colors">
                            </div>
                            <div id="mensaje-coincidencia" class="text-xs font-bold pl-2 min-h-[16px]"></div>
                        </div>

                        <button type="submit" name="cambiar_password" id="btnRegistrar" class="w-full flex justify-center items-center gap-2 bg-primary-container text-on-primary-container font-bold py-3.5 px-4 rounded-xl hover:bg-yellow-400 disabled:opacity-50 disabled:pointer-events-none active:scale-[0.99] transition-all duration-200 shadow-md shadow-yellow-400/20 text-sm tracking-wide">
                            <span>Actualizar y Restablecer</span>
                            <span class="material-symbols-outlined text-lg">published_with_changes</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="pt-4 text-center border-t border-gray-200">
                    <button type="submit" name="cancelar_regresar_login" formnovalidate class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-yellow-600 hover:underline transition-all bg-transparent border-none cursor-pointer">
                        <span class="material-symbols-outlined text-lg">keyboard_return</span>
                        <span>Cancelar y Regresar al Login</span>
                    </button>
                </div>

            </form>

        </div>
    </section>
</main>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]:not([formnovalidate])');
        if (btn) {
            btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-on-primary-container" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Procesando solicitud...</span>`;
            btn.style.opacity = "0.85";
        }
    });

    function validarFortaleza() {
        const pass = document.getElementById('contrasena').value;
        const mensaje = document.getElementById('mensaje-fortaleza');
        const regexFuerte = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/;
        
        if (pass.length === 0) { 
            mensaje.innerHTML = ""; 
        } else if (regexFuerte.test(pass)) { 
            mensaje.innerHTML = "Segura ✅"; 
            mensaje.className = "text-xs font-bold pl-2 text-emerald-600"; 
        } else { 
            mensaje.innerHTML = "Mínimo 8 caracteres, 1 Mayúscula, 1 Número y 1 Símbolo."; 
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

    window.onload = function () {
        window.history.forward();
    };
    window.onpageshow = function (evt) {
        if (evt.persisted) window.history.forward();
    };
</script>

</body>
</html>