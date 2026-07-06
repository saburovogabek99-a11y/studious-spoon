<?php
/**
 * Telegram API Class
 * Telegram bilan ishlash
 */

class TelegramAPI {
    private $apiUrl;
    
    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }
    
    /**
     * Xabar yuborish
     */
    public function sendMessage($chatId, $text, $keyboard = null, $parseMode = 'HTML') {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];
        
        if ($keyboard) {
            $data['reply_markup'] = $keyboard;
        }
        
        return $this->makeRequest('sendMessage', $data);
    }
    
    /**
     * Inline keyboard bilan xabar yuborish
     */
    public function sendInlineKeyboard($chatId, $text, $buttons, $parseMode = 'HTML') {
        $keyboard = [
            'inline_keyboard' => array_chunk($buttons, 1)
        ];
        return $this->sendMessage($chatId, $text, $keyboard, $parseMode);
    }
    
    /**
     * Reply keyboard bilan xabar yuborish
     */
    public function sendReplyKeyboard($chatId, $text, $buttons, $parseMode = 'HTML') {
        $keyboard = [
            'keyboard' => array_chunk($buttons, 2),
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
        return $this->sendMessage($chatId, $text, $keyboard, $parseMode);
    }
    
    /**
     * Inline button yaratish
     */
    public static function inlineButton($text, $data = null, $url = null) {
        $button = ['text' => $text];
        
        if ($data) {
            $button['callback_data'] = $data;
        } elseif ($url) {
            $button['url'] = $url;
        }
        
        return $button;
    }
    
    /**
     * Reply button yaratish
     */
    public static function replyButton($text) {
        return ['text' => $text];
    }
    
    /**
     * Callback query'ga javob
     */
    public function answerCallbackQuery($callbackQueryId, $text = '', $showAlert = false) {
        $data = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert
        ];
        return $this->makeRequest('answerCallbackQuery', $data);
    }
    
    /**
     * API'ga so'rov yuborish
     */
    private function makeRequest($method, $data) {
        $url = $this->apiUrl . '/' . $method;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("Telegram API Error ($method): $response");
            return null;
        }
        
        return json_decode($response, true);
    }
}
?>