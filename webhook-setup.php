<?php
/**
 * Webhook sozlash skripti
 */

require_once __DIR__ . '/src/TelegramAPI.php';

$token = getenv('TELEGRAM_BOT_TOKEN');

if (empty($token)) {
    echo "XATOLIK: TELEGRAM_BOT_TOKEN topilmadi!\n";
    exit(1);
}

echo "Webhook URL'ni kiriting: ";
$webhookUrl = trim(fgets(STDIN));

if (empty($webhookUrl)) {
    echo "XATOLIK: URL bo'sh!\n";
    exit(1);
}

$apiUrl = 'https://api.telegram.org/bot' . $token;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl . '/setWebhook',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['url' => $webhookUrl]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($result['ok']) {
    echo "MUVAFFAQIYAT! Webhook sozlandi!\n";
    echo "URL: $webhookUrl\n";
} else {
    echo "XATOLIK: " . ($result['description'] ?? 'Noma\'lum') . "\n";
    exit(1);
}

?>
