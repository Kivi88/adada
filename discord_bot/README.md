
# Roblox Grup Yönetim Discord Botu

Bu Discord botu, Roblox Grup Yönetim web sisteminizin tüm özelliklerini Discord üzerinden kullanmanıza olanak sağlar.

## Özellikler

### 🔍 Arama Komutları
- `!grup <id>` - Roblox grup bilgilerini getir
- `!oyuncu <username>` - Roblox oyuncu bilgilerini ara

### 👤 Kullanıcı Komutları
- `!panel` - Kişisel kullanıcı paneli
- `!yardım` - Komut listesi

### 👑 Grup Sahibi Komutları
- `!yardımcı_ekle <@user> <izinler>` - Yardımcı ekle
- `!yardımcılar` - Yardımcıları listele

### ⚙️ Admin Komutları
- `!kayıt <grup_id> <grup_adı>` - Grup sahibi kayıt et
- `!admin_ekle <@user>` - Yeni admin ekle
- `!istatistik` - Sistem istatistikleri

## Kurulum

### 1. Discord Bot Oluşturma
1. [Discord Developer Portal](https://discord.com/developers/applications)'a gidin
2. "New Application" → Bot adını girin
3. "Bot" sekmesine gidin → "Add Bot"
4. Token'ı kopyalayın

### 2. Bot İzinleri
Bot davet ederken şu izinleri verin:
- Send Messages
- Embed Links
- Read Message History
- Use Slash Commands

### 3. Kurulum
```bash
# Bağımlılıkları yükle
pip install -r requirements.txt

# Bot token'ını ayarla
export DISCORD_BOT_TOKEN="your_bot_token_here"

# Botu çalıştır
python bot.py
```

### 4. İlk Kurulum
1. Botu sunucunuza davet edin
2. Kendinizi admin yapın: `!admin_ekle @kendiniz` (İlk kullanımda)
3. Grup sahiplerini kayıt edin: `!kayıt <grup_id> <grup_adı>`

## Kullanım Örnekleri

```
!grup 123456789
!oyuncu RobloxUser123
!yardımcı_ekle @helper manage_ranks kick_members
!panel
!istatistik
```

## İzin Türleri

Yardımcı eklerken kullanabileceğiniz izinler:
- `manage_ranks` - Rütbe yönetimi
- `edit_group_name` - Grup adı düzenleme
- `kick_members` - Üye atma
- `invite_members` - Üye davet etme
- `ban_members` - Üye banlama

## Veritabanı

Bot SQLite veritabanı kullanır ve otomatik olarak `roblox_bot.db` dosyası oluşturur.

## Güvenlik

- Bot token'ınızı kimseyle paylaşmayın
- Sadece güvenilir kişilere admin yetkisi verin
- Yardımcı izinlerini dikkatli seçin

## Destek

Herhangi bir sorun yaşarsanız bot geliştiricisi ile iletişime geçin.
