<?php
// ============================================================
//  cerrarSesion.php — Cierre de sesión seguro
//  Ubicación: /Login/cerrarSesion.php
// ============================================================

// 1. Iniciar sesión para poder destruirla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Destruir todas las variables de sesión
$_SESSION = [];

// 3. Eliminar la cookie de sesión del navegador (si existe)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,           // Fecha en el pasado → el navegador la borra
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();

// 5. Evitar que el navegador cachee esta página o las anteriores
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 6. Redirigir al login con parámetro para mostrar mensaje si se desea
header("Location: login.php?sesion=cerrada");
exit();
?>