# 🤖 Ona Bot - Telegram Stars & Premium

**Uzbek tilidagi Telegram Bot** - Stars va Premium sotish

## ✨ Xususiyatlar

✅ Uzbek tili  
✅ Avtomatik noyob to'lov miqdori  
✅ SMS to'lov qabuli  
✅ api.pixy.uz integratsiyasi  
✅ JSON ma'lumot saqlash  
✅ 10 daqiqa timeout  

## 📁 Tuzilma

```
ona-bot/
├── index.php
├── config.php
├── webhook-setup.php
├── .env
├── .gitignore
├── src/
│   ├── JsonStorage.php
│   ├── TelegramAPI.php
│   ├── PixyAPI.php
│   ├── OrderManager.php
│   ├── PaymentHandler.php
│   └── MessageHandler.php
└── data/ (JSON fayllar)
```

## 🚀 Ishga Tushirish

### 1. .env'ni sozlash

```bash
TELEGRAM_BOT_TOKEN=YOUR_TOKEN
PIXY_API_KEY=YOUR_KEY
```

### 2. Webhook'ni sozlash

```bash
php webhook-setup.php
```

### 3. Bot komandalar

```
/start - Boshlash
/menu - Menyu
```

## 💳 To'lov Jarayoni

1. Foydalanuvchi mahsulot tanlaydi
2. Bot noyob to'lov miqdori hisoblaydi
3. Foydalanuvchi kartaga pul o'tkazadi
4. SMS'dan to'lov aniqlash
5. api.pixy.uz orqali yetkazish

## 📚 Qo'shimcha

- [Telegram Bot API](https://core.telegram.org/bots/api)
- [JSON Docs](https://www.php.net/manual/en/book.json.php)

---

**Status:** ✅ Ishlamoqda
