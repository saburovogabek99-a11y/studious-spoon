<?php
/**
 * Telegram Bot - Uzbek Stars & Premium Sotish
 * Platform: Replit
 * Language: Uzbek (O'zbek)
 * Data Storage: JSON
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Telegram API token
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: '7729485916:AAHcNqqJNAKQu3ZzF7T8nN2pXzT9qZ8nK9W');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN);

// Pixy.uz API
define('PIXY_API_URL', 'https://api.pixy.uz');
define('PIXY_API_KEY', getenv('PIXY_API_KEY') ?: 'YOUR_PIXY_KEY');

// Ma'lumotlar fayllar
define('DATA_DIR', __DIR__ . '/data');
define('ORDERS_FILE', DATA_DIR . '/orders.json');
define('USERS_FILE', DATA_DIR . '/users.json');
define('PAYMENTS_FILE', DATA_DIR . '/payments.json');
define('PRODUCTS_FILE', DATA_DIR . '/products.json');
define('SMS_LOG_FILE', DATA_DIR . '/sms_log.json');

// Asosiy klasslar
require_once __DIR__ . '/src/JsonStorage.php';
require_once __DIR__ . '/src/TelegramAPI.php';
require_once __DIR__ . '/src/PixyAPI.php';
require_once __DIR__ . '/src/OrderManager.php';
require_once __DIR__ . '/src/PaymentHandler.php';
require_once __DIR__ . '/src/MessageHandler.php';

// Ma'lumotlar papkasini yaratish
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Webhook ma'lumotlarini olish
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

try {
    // JSON Storage
    $storage = new JsonStorage();
    $storage->init();
    
    // Telegram API
    $telegram = new TelegramAPI(TELEGRAM_API_URL);
    
    // Order Manager
    $orderManager = new OrderManager($storage);
    
    // Message Handler
    $messageHandler = new MessageHandler($telegram, $storage, $orderManager);
    
    // Update'ni qayta ishlash
    if (isset($update['message'])) {
        $messageHandler->handleMessage($update['message']);
    } elseif (isset($update['callback_query'])) {
        $messageHandler->handleCallbackQuery($update['callback_query']);
    }
    
    http_response_code(200);
    echo json_encode(['ok' => true]);
    
} catch (Exception $e) {
    error_log('Bot Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>