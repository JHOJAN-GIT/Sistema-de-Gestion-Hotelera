<?php
require_once 'layout/seguridad.php';
session_start();
session_unset();
session_destroy();
header("Location: ../Login/login.php");
exit();
?>
