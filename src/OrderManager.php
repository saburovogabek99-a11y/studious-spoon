<?php
/**
 * Order Manager Class
 * Buyurtmalarni boshqarish va to'lov miqdorini hisoblash
 */

class OrderManager {
    private $storage;
    
    public function __construct(JsonStorage $storage) {
        $this->storage = $storage;
    }
    
    /**
     * Yangi buyurtma yaratish
     */
    public function createOrder($userId, $productId, $quantity) {
        $products = $this->storage->getProducts();
        
        if (!isset($products[$productId])) {
            throw new Exception('Mahsulot topilmadi');
        }
        
        $product = $products[$productId];
        
        // Noyob to'lov miqdorini hisoblash
        $amount = $this->generateUniqueAmount();
        
        $order = [
            'user_id' => $userId,
            'product_id' => $productId,
            'product_name' => $product['name'],
            'product_type' => $product['type'],
            'quantity' => $quantity,
            'amount' => $amount,
            'status' => 'pending'
        ];
        
        return $this->storage->addOrder($order);
    }
    
    /**
     * Noyob to'lov miqdorini hisoblash
     * Agar miqdor bilan aktiv buyurtma bor bo'lsa, +1 qo'shish
     */
    private function generateUniqueAmount() {
        $baseAmount = rand(10000, 99999); // 10,000 - 99,999 so'm
        $amount = $baseAmount;
        $maxAttempts = 100;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            // Tekshirish: bu miqdor bilan aktiv buyurtma bor mi?
            if (!$this->storage->getOrderByAmount($amount)) {
                return $amount;
            }
            $amount += 1; // +1 so'm qo'shish
            $attempt++;
        }
        
        // Agar 100 ta urinishdan keyin ham topib bo'lmasak
        throw new Exception('Noyob to\'lov miqdorini topib bo\'lmadi');
    }
    
    /**
     * Buyurtmani yangilash
     */
    public function updateOrder($orderId, $data) {
        return $this->storage->updateOrder($orderId, $data);
    }
    
    /**
     * Buyurtmani olish
     */
    public function getOrder($orderId) {
        return $this->storage->getOrder($orderId);
    }
    
    /**
     * Aktiv buyurtmalarni tekshirish va eskirganlarini bekor qilish
     */
    public function checkExpiredOrders() {
        $orders = $this->storage->read('orders.json');
        $updated = false;
        
        foreach ($orders as $orderId => $order) {
            if ($order['status'] === 'pending') {
                if (strtotime($order['expires_at']) < time()) {
                    $order['status'] = 'expired';
                    $this->storage->updateOrder($orderId, ['status' => 'expired']);
                    $updated = true;
                }
            }
        }
        
        return $updated;
    }
}
?>