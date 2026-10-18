<?php
// Admin/verificar_codigo.php
session_start();
header('Content-Type: application/json');

// Leer el JSON enviado
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$codigoIngresado = $data['codigo'] ?? '';

// Validar que existe un código en sesión
if (!isset($_SESSION['codigo_reporte']) || !isset($_SESSION['codigo_timestamp'])) {
    echo json_encode([
        'ok' => false,
        'error' => 'No se ha generado ningún código. Solicita uno nuevo.'
    ]);
    exit;
}

// Verificar que el código no haya expirado (30 segundos)
$tiempoTranscurrido = time() - $_SESSION['codigo_timestamp'];
if ($tiempoTranscurrido > 30) {
    // Limpiar sesión
    unset($_SESSION['codigo_reporte']);
    unset($_SESSION['codigo_timestamp']);
    
    echo json_encode([
        'ok' => false,
        'error' => 'El código ha expirado. Solicita uno nuevo.'
    ]);
    exit;
}

// Verificar que el código sea correcto
if ($codigoIngresado === $_SESSION['codigo_reporte']) {
    // Código correcto - limpiar sesión
    unset($_SESSION['codigo_reporte']);
    unset($_SESSION['codigo_timestamp']);
    
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Código verificado correctamente'
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'error' => 'Código incorrecto. Verifica e intenta nuevamente.'
    ]);
}
?>
