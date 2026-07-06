<?php
/**
 * Message Handler Class
 * Telegram xabarlarini qayta ishlash
 */

class MessageHandler {
    private $telegram;
    private $storage;
    private $orderManager;
    private $paymentHandler;
    
    public function __construct(TelegramAPI $telegram, JsonStorage $storage, OrderManager $orderManager) {
        $this->telegram = $telegram;
        $this->storage = $storage;
        $this->orderManager = $orderManager;
        $this->paymentHandler = new PaymentHandler($storage);
    }
    
    /**
     * Xabarni qayta ishlash
     */
    public function handleMessage($message) {
        $chatId = $message['chat']['id'];
        $userId = $message['from']['id'];
        $text = $message['text'] ?? '';
        
        // Foydalanuvchini ro'yxatdan o'tkazish
        $this->storage->addOrUpdateUser($userId, [
            'first_name' => $message['from']['first_name'] ?? '',
            'last_name' => $message['from']['last_name'] ?? '',
            'username' => $message['from']['username'] ?? '',
            'chat_id' => $chatId
        ]);
        
        // Komandalarni qayta ishlash
        if (strpos($text, '/') === 0) {
            $this->handleCommand($chatId, $userId, $text);
        }
    }
    
    /**
     * Callback query'ni qayta ishlash
     */
    public function handleCallbackQuery($callbackQuery) {
        $chatId = $callbackQuery['message']['chat']['id'];
        $userId = $callbackQuery['from']['id'];
        $data = $callbackQuery['data'] ?? '';
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $callbackId = $callbackQuery['id'] ?? null;
        
        if ($callbackId) {
            $this->telegram->answerCallbackQuery($callbackId);
        }
        
        if (strpos($data, 'product_') === 0) {
            $productId = str_replace('product_', '', $data);
            $this->showProductDetails($chatId, $messageId, $userId, (int)$productId);
        } elseif (strpos($data, 'price_') === 0) {
            $parts = explode('_', $data, 3);
            if (count($parts) === 3) {
                $productId = $parts[1];
                $priceKey = $parts[2];
                $this->handlePriceSelection($chatId, $messageId, $userId, (int)$productId, $priceKey);
            }
        } elseif ($data === 'back_menu') {
            $this->showMainMenu($chatId, $userId);
        }
    }
    
    /**
     * Komandalarni qayta ishlash
     */
    private function handleCommand($chatId, $userId, $command) {
        switch ($command) {
            case '/start':
                $this->handleStart($chatId, $userId);
                break;
            case '/menu':
                $this->showMainMenu($chatId, $userId);
                break;
            default:
                $this->telegram->sendMessage($chatId, "❌ Noma'lum komanda");
        }
    }
    
    /**
     * /start komandasi
     */
    private function handleStart($chatId, $userId) {
        $user = $this->storage->getUser($userId);
        $firstName = $user['first_name'] ?? 'Mehmonzoda';
        
        $text = "👋 Xush kelibsiz, $firstName!\n\n";
        $text .= "🚀 <b>Ona Bot</b> - Telegram Stars va Premium sotish\n\n";
        $text .= "📱 Bu bot orqali quyidagilarni qila olasiz:\n";
        $text .= "• ⭐ Telegram Stars sotib olish\n";
        $text .= "• 👑 Telegram Premium sotib olish\n";
        $text .= "• 💳 Qulay to'lov usuli\n\n";
        $text .= "Boshlash uchun <b>Asosiy Menyu</b> tugmasini bosing 👇";
        
        $buttons = [
            TelegramAPI::inlineButton('🏠 Asosiy Menyu', 'back_menu')
        ];
        
        $this->telegram->sendInlineKeyboard($chatId, $text, $buttons);
    }
    
    /**
     * Asosiy menyu
     */
    private function showMainMenu($chatId, $userId) {
        $text = "🎯 <b>Asosiy Menyu</b>\n\n";
        $text .= "Qaysi mahsulotni tanlaysiz?";
        
        $buttons = [
            TelegramAPI::inlineButton('⭐ Telegram Stars', 'product_1'),
            TelegramAPI::inlineButton('👑 Telegram Premium', 'product_2')
        ];
        
        $this->telegram->sendInlineKeyboard($chatId, $text, $buttons);
    }
    
    /**
     * Mahsulot tafsilotlarini ko'rsatish
     */
    private function showProductDetails($chatId, $messageId, $userId, $productId) {
        $products = $this->storage->getProducts();
        
        if (!isset($products[$productId])) {
            return;
        }
        
        $product = $products[$productId];
        
        $text = "<b>" . $product['name'] . "</b>\n\n";
        $text .= $product['description'] . "\n\n";
        $text .= "💰 <b>Narxlar:</b>\n";
        
        $buttons = [];
        
        foreach ($product['prices'] as $priceKey => $price) {
            if ($product['type'] === 'stars') {
                $text .= "• {$price['amount']} ⭐ = {$price['uzs']} so'm\n";
                $buttonText = $price['amount'] . " ⭐ - " . $price['uzs'] . " so'm";
            } else {
                $text .= "• {$price['months']} oy = {$price['uzs']} so'm\n";
                $buttonText = $price['months'] . " oy - " . $price['uzs'] . " so'm";
            }
            
            $buttons[] = TelegramAPI::inlineButton($buttonText, "price_" . $productId . "_" . $priceKey);
        }
        
        $buttons[] = TelegramAPI::inlineButton('🔙 Orqaga', 'back_menu');
        
        $keyboard = ['inline_keyboard' => array_chunk($buttons, 1)];
        
        if ($messageId) {
            $this->telegram->editMessageText($chatId, $messageId, $text, $keyboard);
        } else {
            $this->telegram->sendMessage($chatId, $text, $keyboard);
        }
    }
    
    /**
     * Narx tanlashni qayta ishlash
     */
    private function handlePriceSelection($chatId, $messageId, $userId, $productId, $priceKey) {
        $products = $this->storage->getProducts();
        
        if (!isset($products[$productId])) {
            return;
        }
        
        $product = $products[$productId];
        
        if (!isset($product['prices'][$priceKey])) {
            return;
        }
        
        $price = $product['prices'][$priceKey];
        
        try {
            if ($product['type'] === 'stars') {
                $quantity = $price['amount'];
            } else {
                $quantity = $price['months'];
            }
            
            $order = $this->orderManager->createOrder($userId, $productId, $quantity);
            
            $text = "✅ <b>Buyurtma yaratildi!</b>\n\n";
            $text .= "📦 <b>Mahsulot:</b> {$product['name']}\n";
            $text .= "📊 <b>Miqdor:</b> {$quantity}\n";
            $text .= "💳 <b>To'lov miqdori:</b> <code>{$order['amount']}</code> so'm\n";
            $text .= "⏰ <b>Vaqti:</b> 10 daqiqa\n\n";
            $text .= "🏦 Quyidagi kartaga pul o'tkazing:\n";
            $text .= "<code>9860 1234 5678 9100</code>\n\n";
            $text .= "💰 <b>Miqdor:</b> <code>{$order['amount']} so'm</code>\n\n";
            $text .= "✅ To'lovni tekshirish uchun quyidagi tugmani bosing:";
            
            $buttons = [
                TelegramAPI::inlineButton('🔄 To\'lovni tekshirish', 'back_menu'),
                TelegramAPI::inlineButton('❌ Bekor qilish', 'back_menu')
            ];
            
            $keyboard = ['inline_keyboard' => array_chunk($buttons, 1)];
            
            if ($messageId) {
                $this->telegram->editMessageText($chatId, $messageId, $text, $keyboard);
            } else {
                $this->telegram->sendMessage($chatId, $text, $keyboard);
            }
            
        } catch (Exception $e) {
            $this->telegram->sendMessage($chatId, "❌ Xatolik: " . $e->getMessage());
        }
    }
}
?>