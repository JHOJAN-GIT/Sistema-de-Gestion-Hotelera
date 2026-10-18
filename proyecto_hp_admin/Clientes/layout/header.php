<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-logout por inactividad (30 minutos) — lado servidor
$tiempoLimite = 30 * 60; // 1800 segundos

if (isset($_SESSION['ultimo_activity'])) {
    if ((time() - $_SESSION['ultimo_activity']) > $tiempoLimite) {
        session_unset();
        session_destroy();
        header("Location: ../Login/login.php?sesion=expirada");
        exit();
    }
}
$_SESSION['ultimo_activity'] = time();

if (!isset($_SESSION['id']) || strtolower($_SESSION['rol']) !== 'cliente') {
    header("Location: ../Login/login.php");
    exit();
}

$nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Hospedaje Ayacucho</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-highest": "#e2e2e2",
                        "secondary-fixed-dim": "#cdc897",
                        "on-error-container": "#93000a",
                        "on-surface": "#1a1c1c",
                        "on-primary-fixed": "#221b00",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "on-surface-variant": "#4d4632",
                        "surface-tint": "#705d00",
                        "primary-fixed": "#ffe170",
                        "inverse-on-surface": "#f0f1f1",
                        "on-secondary": "#ffffff",
                        "error-container": "#ffdad6",
                        "secondary-container": "#e7e1ae",
                        "on-tertiary-fixed": "#1b1b1b",
                        "surface-dim": "#dadada",
                        "primary": "#705d00",
                        "surface-container-low": "#f3f3f4",
                        "error": "#ba1a1a",
                        "surface-container-high": "#e8e8e8",
                        "on-secondary-fixed-variant": "#4b4822",
                        "on-tertiary-container": "#5e5e5e",
                        "tertiary": "#5e5e5e",
                        "tertiary-container": "#d9d9d9",
                        "primary-fixed-dim": "#e9c400",
                        "on-tertiary-fixed-variant": "#474747",
                        "primary-container": "#ffd600",
                        "surface": "#f9f9f9",
                        "on-error": "#ffffff",
                        "on-background": "#1a1c1c",
                        "on-secondary-container": "#67643b",
                        "on-primary-container": "#705d00",
                        "outline": "#7f775f",
                        "on-primary": "#ffffff",
                        "tertiary-fixed-dim": "#c6c6c6",
                        "surface-variant": "#e2e2e2",
                        "on-primary-fixed-variant": "#544600",
                        "inverse-surface": "#2f3131",
                        "tertiary-fixed": "#e2e2e2",
                        "secondary": "#636037",
                        "surface-container": "#eeeeee",
                        "secondary-fixed": "#eae4b1",
                        "background": "#f9f9f9",
                        "inverse-primary": "#e9c400",
                        "on-secondary-fixed": "#1e1c00",
                        "surface-bright": "#f9f9f9",
                        "outline-variant": "#d0c6ab"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { background-color: #f9f9f9; }
        .custom-card-shadow { box-shadow: 0px 4px 20px rgba(0,0,0,0.05); }
        .sidebar-transition { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .content-transition { transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-content { transition: opacity 0.2s ease-in-out; }

        /* Chat widget */
        #chat-widget {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            font-family: 'Inter', sans-serif;
        }
        #chat-box {
            width: 360px;
            max-height: 520px;
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid #e4e4e7;
            border-radius: 0.25rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
            margin-bottom: 12px;
        }
        #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 380px;
        }
        .msg-user {
            align-self: flex-end;
            background: #1a1a1a;
            color: #fff;
            padding: 8px 12px;
            border-radius: 0.25rem;
            font-size: 13px;
            max-width: 80%;
            line-height: 1.5;
        }
        .msg-bot {
            align-self: flex-start;
            background: #ffd600;
            color: #1a1a1a;
            padding: 8px 12px;
            border-radius: 0.25rem;
            font-size: 13px;
            max-width: 80%;
            line-height: 1.5;
        }
        .msg-loading {
            align-self: flex-start;
            background: #f4f4f5;
            color: #71717a;
            padding: 8px 12px;
            border-radius: 0.25rem;
            font-size: 13px;
            font-style: italic;
        }
    </style>
</head>
<body class="antialiased text-on-surface bg-background">

<header id="header-main" class="flex justify-between items-center h-16 px-8 w-full sticky top-0 z-50 bg-white border-b border-zinc-100 shadow-sm tracking-tight">
    
    <div class="flex items-center gap-2">
        <button id="toggle-menu" class="material-symbols-outlined text-zinc-700 hover:text-black p-2 rounded-sm hover:bg-zinc-100 transition-colors mr-2">
            menu
        </button>
        <span class="text-xl font-black tracking-widest text-zinc-900 uppercase">Hospedaje Ayacucho</span>
        <!-- Buscador abre el chat al hacer click -->
        <div class="hidden md:flex items-center bg-zinc-100 px-4 py-2 rounded-sm ml-2 cursor-pointer hover:bg-zinc-200 transition-colors"
             onclick="abrirChat()">
            <span class="material-symbols-outlined text-zinc-500 mr-2">smart_toy</span>
            <span class="text-sm text-zinc-500 w-64 select-none">Consulte las 24 horas con IA...</span>
        </div>
    </div>

    <div class="flex items-center gap-6">
        <a href="perfilCliente.php" class="block transition-transform active:scale-95 duration-150 group" title="Ver mi Perfil">
            <img alt="Perfil Usuario" class="w-9 h-9 rounded-full border-2 border-transparent group-hover:border-primary-container object-cover shadow-sm transition-all" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC16TfY01oOdIaH3i31RrxVSnrlD3jvPOi5PMb8k5tuUkT-B2BXN0y-PP_-s9Yh0s8YiblB10OiSK_3ekizOjT2DaYhtACs5u3qd5mawCr5_JbLABzvnBgLFs_30GBrK_B6fuwHAwMQACDn0QnsTpIt2_UN892V_vTvm_4X88XGs3Kc0cz2_uwF_NbGi9p0Yx0ct1KBhpNOo4bsQJYCNpLAAOdnQgxBckd76uJlqXOQCBwK2XSTkosIsD5JwXmwIvm_g98QWJ4Rlv4"/>
        </a>
    </div>

</header>

<!-- Timer de inactividad — fuera del <header> HTML -->
<script>
    // 28 min = aviso, 30 min = cierre de sesión
    let timerAviso  = setTimeout(mostrarAviso,  28 * 60 * 1000);
    let timerCierre = setTimeout(cerrarSesion,  30 * 60 * 1000);

    function mostrarAviso() {
        alert("⚠️ Tu sesión expirará en 2 minutos por inactividad. Mueve el mouse o haz clic para continuar.");
    }

    function cerrarSesion() {
        window.location.href = "../Login/login.php?sesion=expirada";
    }

    function resetTimer() {
        clearTimeout(timerAviso);
        clearTimeout(timerCierre);
        timerAviso  = setTimeout(mostrarAviso, 28 * 60 * 1000);
        timerCierre = setTimeout(cerrarSesion, 30 * 60 * 1000);
    }

    ["mousemove", "keydown", "click", "scroll", "touchstart"].forEach(function(evento) {
        document.addEventListener(evento, resetTimer);
    });
</script>

<!-- Chat Widget -->
<div id="chat-widget">
    <!-- Caja del chat (oculta por defecto) -->
    <div id="chat-box" style="display:none;">
        <!-- Header del chat -->
        <div class="flex items-center justify-between px-4 py-3 bg-zinc-900 text-white">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-400 text-base" style="font-variation-settings:'FILL' 1;">smart_toy</span>
                <div>
                    <p class="text-sm font-bold leading-tight">Asistente Hospedaje Ayacucho</p>
                    <p class="text-[10px] text-zinc-400">Disponible 24 horas</p>
                </div>
            </div>
            <button onclick="cerrarChat()" class="material-symbols-outlined text-zinc-400 hover:text-white transition-colors text-base">close</button>
        </div>

        <!-- Mensajes -->
        <div id="chat-messages">
            <div class="msg-bot">¡Hola! 👋 Soy el asistente virtual de Hospedaje Ayacucho. ¿En qué puedo ayudarte hoy?</div>
        </div>

        <!-- Input -->
        <div class="flex items-center gap-2 px-3 py-3 border-t border-zinc-100 bg-white">
            <input id="chat-input" type="text"
                   placeholder="Escribe tu consulta..."
                   class="flex-1 text-sm border border-zinc-200 rounded-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400"
                   onkeydown="if(event.key==='Enter') enviarMensaje()"/>
            <button onclick="enviarMensaje()"
                    class="bg-zinc-900 text-white px-3 py-2 rounded-sm hover:bg-zinc-700 transition-colors flex items-center justify-center">
                <span class="material-symbols-outlined text-sm text-yellow-400">send</span>
            </button>
        </div>
    </div>

    <!-- Botón flotante -->
    <div class="flex justify-end">
        <button id="chat-toggle-btn" onclick="toggleChat()"
                class="bg-zinc-900 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center hover:bg-zinc-700 transition-all active:scale-95">
            <span id="chat-icon" class="material-symbols-outlined text-yellow-400" style="font-variation-settings:'FILL' 1;">smart_toy</span>
        </button>
    </div>
</div>

<script>
    let chatAbierto = false;

    function abrirChat() {
        const box = document.getElementById('chat-box');
        box.style.display = 'flex';
        chatAbierto = true;
        document.getElementById('chat-icon').textContent = 'close';
        setTimeout(() => document.getElementById('chat-input').focus(), 100);
    }

    function cerrarChat() {
        document.getElementById('chat-box').style.display = 'none';
        chatAbierto = false;
        document.getElementById('chat-icon').textContent = 'smart_toy';
    }

    function toggleChat() {
        chatAbierto ? cerrarChat() : abrirChat();
    }

    async function enviarMensaje() {
        const input = document.getElementById('chat-input');
        const mensaje = input.value.trim();
        if (!mensaje) return;

        const mensajes = document.getElementById('chat-messages');

        const divUser = document.createElement('div');
        divUser.className = 'msg-user';
        divUser.textContent = mensaje;
        mensajes.appendChild(divUser);
        input.value = '';

        const divLoad = document.createElement('div');
        divLoad.className = 'msg-loading';
        divLoad.textContent = 'Escribiendo...';
        mensajes.appendChild(divLoad);
        mensajes.scrollTop = mensajes.scrollHeight;

        try {
            const res = await fetch('../Clientes/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensaje })
            });

            const data = await res.json();
            divLoad.remove();

            const divBot = document.createElement('div');
            divBot.className = 'msg-bot';
            divBot.textContent = data.respuesta || data.error || 'Error al obtener respuesta.';
            mensajes.appendChild(divBot);
        } catch (e) {
            divLoad.remove();
            const divErr = document.createElement('div');
            divErr.className = 'msg-bot';
            divErr.textContent = 'Error de conexión. Intenta nuevamente.';
            mensajes.appendChild(divErr);
        }

        mensajes.scrollTop = mensajes.scrollHeight;
    }
</script>

<!-- Wrapper principal del layout (lo cierra footer.php) -->
<div class="flex min-h-[calc(100vh-64px)] relative overflow-x-hidden">