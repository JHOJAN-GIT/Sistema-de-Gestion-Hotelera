<?php
// Admin/enviar_codigo.php
session_start();
header('Content-Type: application/json');

require_once("../PHPMailer/PHPMailer.php");
require_once("../PHPMailer/SMTP.php");
require_once("../PHPMailer/Exception.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Generar código aleatorio de 6 dígitos
$codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Guardar en sesión con timestamp
$_SESSION['codigo_reporte'] = $codigo;
$_SESSION['codigo_timestamp'] = time();

// Enviar correo con PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jhojanantezana96@gmail.com';
    $mail->Password = 'mffv gefb imeo wrxp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Remitente y destinatario
    $mail->setFrom('jhojanantezana96@gmail.com', 'Hospedaje Ayacucho');
    $mail->addAddress('jhojanantezana96@gmail.com', 'Administrador');

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Código de Verificación - Reporte Diario';
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #705d00 0%, #ffd600 100%); padding: 30px; text-align: center; }
            .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; }
            .content { padding: 40px 30px; text-align: center; }
            .code-box { background-color: #f9f9f9; border: 2px dashed #705d00; border-radius: 8px; padding: 30px; margin: 30px 0; }
            .code { font-size: 48px; font-weight: bold; color: #705d00; letter-spacing: 8px; font-family: monospace; }
            .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; text-align: left; }
            .warning-icon { color: #ffc107; font-weight: bold; }
            .footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔐 Código de Verificación</h1>
            </div>
            <div class="content">
                <p style="font-size: 16px; color: #333; margin-bottom: 10px;">
                    Se ha solicitado acceso al <strong>Reporte Diario</strong> del sistema.
                </p>
                <p style="font-size: 14px; color: #666; margin-bottom: 30px;">
                    Utiliza el siguiente código para verificar tu identidad:
                </p>
                
                <div class="code-box">
                    <div class="code">' . $codigo . '</div>
                </div>

                <div class="warning">
                    <p style="margin: 0; color: #856404;">
                        <span class="warning-icon">⚠️</span> 
                        <strong>Importante:</strong> Este código es válido por <strong>30 segundos</strong> únicamente.
                    </p>
                </div>

                <p style="font-size: 13px; color: #999; margin-top: 30px;">
                    Si no solicitaste este código, ignora este mensaje.
                </p>
            </div>
            <div class="footer">
                <p style="margin: 0;">© ' . date('Y') . ' Hospedaje Ayacucho - Sistema de Gestión Hotelera</p>
                <p style="margin: 5px 0 0 0;">Este es un correo automático, por favor no responder.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    $mail->AltBody = "Tu código de verificación es: $codigo\n\nEste código es válido por 30 segundos.\n\nSi no solicitaste este código, ignora este mensaje.\n\n© " . date('Y') . " Hospedaje Ayacucho";

    // Enviar correo
    $mail->send();

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Código enviado correctamente a tu correo'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => 'Error al enviar el correo: ' . $mail->ErrorInfo
    ]);
}
?>
