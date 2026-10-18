<?php
class Cconexion {
    public static function ConexionBD() {
        try {
            $host     = "sql308.infinityfree.com";
            $puerto   = 3306;
            $usuario  = "if0_42043291";
            $password = "O5INyN35VxPpEvB"; // ← tu contraseña de InfinityFree
            $base     = "if0_42043291_hospedaje";

            $conn = new PDO(
                "mysql:host=$host;port=$puerto;dbname=$base;charset=utf8mb4",
                $usuario,
                $password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }
}
?>