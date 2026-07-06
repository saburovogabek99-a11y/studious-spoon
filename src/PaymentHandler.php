<?php
/**
 * Payment Handler Class
 * To'lovlarni qayta ishlash va SMS'dan to'lov aniqlash
 */

class PaymentHandler {
    private $storage;
    
    public function __construct(JsonStorage $storage) {
        $this->storage = $storage;
    }
    
    /**
     * SMS orqali to'lovni aniqlash va tasdiqlash
     */
    public function confirmPaymentBySMS($amount, $cardLast4, $transactionId) {
        // SMS log'ga qo'shish
        $smsLog = [
            'amount' => $amount,
            'card_last4' => $cardLast4,
            'transaction_id' => $transactionId,
            'status' => 'processed'
        ];
        $this->storage->addSmsLog($smsLog);
        
        // Bu miqdor bilan buyurtmani topish
        $order = $this->storage->getOrderByAmount($amount);
        
        if (!$order) {
            return ['success' => false, 'error' => 'Buyurtma topilmadi'];
        }
        
        // Duplikat tekshirish - bu order uchun to'lov allaqachon tasdiqlangan mi?
        $payments = $this->storage->read('payments.json');
        foreach ($payments as $payment) {
            if ($payment['order_id'] === $order['id'] && $payment['status'] === 'confirmed') {
                return ['success' => false, 'error' => 'Bu buyurtma uchun to\'lov allaqachon tasdiqlandi'];
            }
        }
        
        // To'lovni yaratish va buyurtmani tasdiqlash
        $payment = [
            'order_id' => $order['id'],
            'user_id' => $order['user_id'],
            'amount' => $amount,
            'card_last4' => $cardLast4,
            'transaction_id' => $transactionId,
            'status' => 'confirmed'
        ];
        
        $this->storage->addPayment($payment);
        $this->storage->updateOrder($order['id'], [
            'status' => 'confirmed',
            'confirmed_at' => date('Y-m-d H:i:s'),
            'payment_id' => $payment['id']
        ]);
        
        return [
            'success' => true,
            'order_id' => $order['id'],
            'payment_id' => $payment['id']
        ];
    }
    
    /**
     * To'lov statistikasini olish
     */
    public function getPaymentStats() {
        $payments = $this->storage->read('payments.json');
        
        $confirmedPayments = 0;
        $totalAmount = 0;
        $todayAmount = 0;
        $today = date('Y-m-d');
        
        foreach ($payments as $payment) {
            if ($payment['status'] === 'confirmed') {
                $confirmedPayments++;
                $totalAmount += $payment['amount'];
                
                if (strpos($payment['created_at'], $today) === 0) {
                    $todayAmount += $payment['amount'];
                }
            }
        }
        
        return [
            'confirmed_payments' => $confirmedPayments,
            'total_amount' => $totalAmount,
            'today_amount' => $todayAmount
        ];
    }
}
?>