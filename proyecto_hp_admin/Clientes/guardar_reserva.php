<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo = $_POST['tipo_habitacion'];
    $dni = trim($_POST['dni']);
    $ingreso = $_POST['fecha_ingreso'];
    $salida = $_POST['fecha_salida'];

    if (empty($tipo) || empty($dni) || empty($ingreso) || empty($salida)) {
        echo "<script>alert('❌ Todos los campos son obligatorios'); window.location='reservar.php';</script>";
        exit();
    }

    if (!isset($_SESSION['nombre_usuario'])) {
        echo "<script>alert('❌ Error: No se detectó usuario activo.'); window.location='../login.php';</script>";
        exit();
    }

    $nombreUsuario = $_SESSION['nombre_usuario'];

    $reserva = [
        'tipo_habitacion' => $tipo,
        'dni' => $dni,
        'fecha_ingreso' => $ingreso,
        'fecha_salida' => $salida,
        'hora_fecha_ingreso' => $ingreso,
        'hora_fecha_salida' => $salida,
        'nombre_usuario' => $nombreUsuario,
        'estado' => 'pendiente'
    ];

    $_SESSION['reservas'][] = $reserva;

    echo "<script>alert('✅ ¡Reserva guardada temporalmente!'); window.location='inicio.php';</script>";
    exit();
} else {
    header("Location: reservar.php");
    exit();
}
