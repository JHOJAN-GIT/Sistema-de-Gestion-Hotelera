<?php
require_once("conexion.php");

$conn = Cconexion::ConexionBD();

if ($conn) {
    echo "✅ ¡Conexión exitosa a MySQL!";
} else {
    echo "❌ Error al conectar.";
}
?>