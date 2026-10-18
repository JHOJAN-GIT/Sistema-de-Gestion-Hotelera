<?php
session_start();
if (!isset($_SESSION['id']) || strtolower($_SESSION['rol']) !== 'cliente') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$pregunta = isset($input['mensaje']) ? trim($input['mensaje']) : '';

if (empty($pregunta)) {
    echo json_encode(['error' => 'Mensaje vacío']);
    exit();
}

$systemPrompt = "Eres un asistente virtual del Hospedaje Ayacucho. Responde preguntas relacionadas con los servicios, habitaciones, precios, políticas y términos del hospedaje. Si te preguntan algo fuera de este tema puedes responder, pero breve y menciona amablemente que tu funcion es ayudar con información del hospedaje. Responde siempre en español, de forma clara, amable y concisa.

INFORMACIÓN DEL HOSPEDAJE:

HABITACIONES Y TARIFAS:
- Habitaciones 301-305 (Momentáneas): S/ 30.00, mínimo 3 horas, máximo 12 horas continuas. Para estancias cortas.
- Habitaciones 401-405 (Individual/Estándar): S/ 100.00 por día. Cama plaza y media, para una persona.
- Habitaciones 501-505 (Doble): S/ 180.00 por día. Camas de dos plazas, para parejas o dos personas.
- Habitaciones 601-605 (Suite Matrimonial Premium): S/ 220.00 por día. Cama matrimonial, terraza, piscina, acabados modernos.

HORARIOS Y POLÍTICA DE DÍA:
- Habitaciones momentáneas (300): el tiempo corre desde el registro exacto en recepción.
- Habitaciones diarias (400, 500, 600): si ingresa ANTES de las 08:00 a.m., la salida es a las 12:00 p.m. del MISMO día. Si ingresa desde las 08:00 a.m. en adelante, la salida es a las 12:00 p.m. del día SIGUIENTE.
- Pasado el horario de salida se aplican cargos adicionales.

RESERVAS:
- Sujetas a disponibilidad.
- Pueden requerir adelantos o confirmaciones previas.
- Cancelaciones deben hacerse con anticipación razonable.
- Adelantos pueden retenerse según tiempo de aviso.

NORMAS GENERALES:
- Se requiere documento de identidad vigente al registro.
- No se permiten escándalos, conductas violentas ni actos ilícitos.
- Visitantes adicionales deben registrarse en recepción.
- No se excede la capacidad máxima de cada habitación.
- El hospedaje tiene videovigilancia en áreas comunes.
- No se responsabiliza por pérdida de objetos de valor.

PAGOS:
- Efectivo, transferencia o billeteras digitales.
- Servicios adicionales se cancelan antes del check-out.

SUITE PREMIUM (600):
- Acceso a terraza y piscina.
- Menores bajo supervisión de adulto responsable en todo momento.

Responde solo con la información anterior. Si no sabes algo específico, indica que consulte directamente en recepción.";
$url = "https://openrouter.ai/api/v1/chat/completions";

$payload = json_encode([
    "model" => "openai/gpt-oss-120b:free",
    "messages" => [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $pregunta]
    ],
    "max_tokens" => 500,
    "temperature" => 0.3
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . OPENROUTER_API_KEY,
    'HTTP-Referer: http://localhost',
    'X-Title: Hospedaje Ayacucho'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response || $httpCode !== 200) {
    echo json_encode([
        'error' => 'Error al conectar.',
        'debug_http' => $httpCode,
        'debug_curl' => curl_error($ch),
        'debug_response' => json_decode($response, true)
    ]);
    exit();
}

$data = json_decode($response, true);
$respuesta = $data['choices'][0]['message']['content'] ?? 'No pude procesar tu consulta.';

echo json_encode(['respuesta' => $respuesta]);
?>
