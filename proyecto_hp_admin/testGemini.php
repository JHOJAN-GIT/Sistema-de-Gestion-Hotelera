<?php
require_once __DIR__ . '/config.php';

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=" . GEMINI_API_KEY;

$payload = json_encode([
    "contents" => [[
        "parts" => [["text" => "Hola, responde solo: funciona"]]
    ]]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<pre>";
echo "HTTP Code: $httpCode\n";
echo "cURL Error: $curlError\n";
echo "Response: $response\n";
echo "</pre>";
?>