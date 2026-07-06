<?php
/**
 * Konfiguratsiya fayli
 */

return [
    'bot' => [
        'token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
        'api_url' => 'https://api.telegram.org/bot',
    ],
    
    'pixy' => [
        'api_url' => 'https://api.pixy.uz',
        'api_key' => getenv('PIXY_API_KEY') ?: '',
    ],
    
    'admin' => [
        'user_ids' => explode(',', getenv('ADMIN_USER_ID') ?: ''),
        'sms_group_id' => (int)getenv('SMS_GROUP_ID') ?: -1001234567890,
    ],
    
    'payment' => [
        'min_amount' => 1000,
        'max_amount' => 5000000,
        'order_timeout' => 600,
    ],
    
    'language' => 'uz',
];
?>