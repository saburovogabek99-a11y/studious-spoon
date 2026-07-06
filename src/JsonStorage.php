<?php
/**
 * JSON Storage Class
 * Ma'lumotlarni JSON fayllarida saqlash
 */

class JsonStorage {
    private $dataDir;
    
    public function __construct($dataDir = __DIR__ . '/../data') {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }
    
    /**
     * Dastlabki JSON fayllarini yaratish
     */
    public function init() {
        $this->ensureFile('users.json', []);
        $this->ensureFile('orders.json', []);
        $this->ensureFile('payments.json', []);
        $this->ensureFile('products.json', $this->getDefaultProducts());
        $this->ensureFile('sms_log.json', []);
        $this->ensureFile('settings.json', $this->getDefaultSettings());
    }
    
    /**
     * Faylni tekshirish va yaratish
     */
    private function ensureFile($filename, $defaultData = []) {
        $filepath = $this->dataDir . '/' . $filename;
        if (!file_exists($filepath)) {
            file_put_contents($filepath, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            chmod($filepath, 0644);
        }
    }
    
    /**
     * JSON fayldagi ma'lumotlarni o'qish
     */
    public function read($filename) {
        $filepath = $this->dataDir . '/' . $filename;
        if (!file_exists($filepath)) {
            throw new Exception("Fayl topilmadi: $filename");
        }
        $content = file_get_contents($filepath);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * JSON fayliga ma'lumotlarni yozish
     */
    public function write($filename, $data) {
        $filepath = $this->dataDir . '/' . $filename;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($filepath, $json) === false) {
            throw new Exception("Faylga yozib bo'lmadi: $filename");
        }
        chmod($filepath, 0644);
        return true;
    }
    
    /**
     * Foydalanuvchini qo'shish yoki yangilash
     */
    public function addOrUpdateUser($userId, $userData) {
        $users = $this->read('users.json');
        $users[$userId] = array_merge($users[$userId] ?? [], $userData);
        $users[$userId]['updated_at'] = date('Y-m-d H:i:s');
        $this->write('users.json', $users);
        return $users[$userId];
    }
    
    /**
     * Foydalanuvchini olish
     */
    public function getUser($userId) {
        $users = $this->read('users.json');
        return $users[$userId] ?? null;
    }
    
    /**
     * Buyurtmani qo'shish
     */
    public function addOrder($order) {
        $orders = $this->read('orders.json');
        $orderId = uniqid('order_', true);
        $order['id'] = $orderId;
        $order['created_at'] = date('Y-m-d H:i:s');
        $order['expires_at'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $order['status'] = 'pending';
        $orders[$orderId] = $order;
        $this->write('orders.json', $orders);
        return $order;
    }
    
    /**
     * Buyurtmani yangilash
     */
    public function updateOrder($orderId, $data) {
        $orders = $this->read('orders.json');
        if (!isset($orders[$orderId])) {
            throw new Exception("Buyurtma topilmadi: $orderId");
        }
        $orders[$orderId] = array_merge($orders[$orderId], $data);
        $orders[$orderId]['updated_at'] = date('Y-m-d H:i:s');
        $this->write('orders.json', $orders);
        return $orders[$orderId];
    }
    
    /**
     * Buyurtmani olish
     */
    public function getOrder($orderId) {
        $orders = $this->read('orders.json');
        return $orders[$orderId] ?? null;
    }
    
    /**
     * Foydalanuvchining barcha buyurtmalarini olish
     */
    public function getUserOrders($userId) {
        $orders = $this->read('orders.json');
        return array_filter($orders, function($order) use ($userId) {
            return $order['user_id'] === $userId;
        });
    }
    
    /**
     * Standart mahsulotlar
     */
    private function getDefaultProducts() {
        return [
            1 => [
                'id' => 1,
                'name' => '⭐ Telegram Stars',
                'type' => 'stars',
                'prices' => [
                    'price_1' => ['amount' => 1, 'uzs' => 1000],
                    'price_100' => ['amount' => 100, 'uzs' => 50000],
                    'price_500' => ['amount' => 500, 'uzs' => 200000],
                    'price_1000' => ['amount' => 1000, 'uzs' => 350000],
                ],
                'description' => 'Telegram Stars - Creator uchun pul topish'
            ],
            2 => [
                'id' => 2,
                'name' => '👑 Telegram Premium',
                'type' => 'premium',
                'prices' => [
                    'price_1m' => ['months' => 1, 'uzs' => 40000],
                    'price_6m' => ['months' => 6, 'uzs' => 200000],
                    'price_12m' => ['months' => 12, 'uzs' => 350000],
                ],
                'description' => 'Telegram Premium - Barcha premium xususiyatlar'
            ]
        ];
    }
    
    /**
     * Standart sozlamalar
     */
    private function getDefaultSettings() {
        return [
            'bot_name' => 'Ona Bot',
            'admin_ids' => [],
            'sms_group_id' => -1001234567890,
            'language' => 'uz',
            'min_amount' => 1000,
            'max_amount' => 5000000
        ];
    }
}
?>