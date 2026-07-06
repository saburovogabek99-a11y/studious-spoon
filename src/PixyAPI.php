<?php
/**
 * Pixy.uz API Class
 * Telegram Stars va Premium yetkazish
 */

class PixyAPI {
    private $apiUrl;
    private $apiKey;
    
    public function __construct($apiUrl, $apiKey) {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }
    
    /**
     * Telegram Stars yuborish
     */
    public function sendStars($telegramUserId, $starAmount) {
        $data = [
            'action' => 'send_stars',
            'user_id' => $telegramUserId,
            'amount' => $starAmount,
            'api_key' => $this->apiKey
        ];
        
        return $this->makeRequest($data);
    }
    
    /**
     * Telegram Premium yuborish
     */
    public function sendPremium($telegramUserId, $months) {
        $data = [
            'action' => 'send_premium',
            'user_id' => $telegramUserId,
            'months' => $months,
            'api_key' => $this->apiKey
        ];
        
        return $this->makeRequest($data);
    }
    
    /**
     * API'ga so'rov yuborish
     */
    private function makeRequest($data) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
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
            error_log("Pixy API Error: $response");
            return ['success' => false, 'error' => $response];
        }
        
        return json_decode($response, true);
    }
}
?>