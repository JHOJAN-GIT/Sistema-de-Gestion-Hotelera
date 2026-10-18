<?php
// Admin/layout/seguridad.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Anti-caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Validación de sesión y rol
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

?>