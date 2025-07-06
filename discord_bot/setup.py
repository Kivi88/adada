
#!/usr/bin/env python3
import os
import sqlite3

def setup_bot():
    """İlk kurulum için gerekli işlemleri yapar"""
    
    print("🤖 Roblox Grup Yönetim Discord Botu Kurulumu")
    print("=" * 50)
    
    # Token kontrolü
    token = os.getenv('DISCORD_BOT_TOKEN')
    if not token:
        print("❌ DISCORD_BOT_TOKEN çevre değişkeni bulunamadı!")
        print("💡 Şu komutu çalıştırın:")
        print("export DISCORD_BOT_TOKEN='YOUR_TOKEN_HERE'")
        return False
    
    # Veritabanı oluştur
    try:
        conn = sqlite3.connect('roblox_bot.db')
        cursor = conn.cursor()
        
        # Users tablosu
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS users (
                discord_id TEXT PRIMARY KEY,
                username TEXT,
                role TEXT DEFAULT 'user',
                group_id INTEGER,
                group_name TEXT,
                roblox_cookie TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ''')
        
        # Helpers tablosu
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS helpers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                discord_id TEXT,
                group_id INTEGER,
                username TEXT,
                permissions TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (discord_id) REFERENCES users(discord_id)
            )
        ''')
        
        conn.commit()
        conn.close()
        print("✅ Veritabanı başarıyla oluşturuldu!")
        
    except Exception as e:
        print(f"❌ Veritabanı hatası: {e}")
        return False
    
    print("\n📋 Kurulum Tamamlandı!")
    print("🚀 Botu başlatmak için: python bot.py")
    print("\n📝 İlk Kullanım:")
    print("1. Botu Discord sunucunuza davet edin")
    print("2. Kendinizi admin yapın: !admin_ekle @kendiniz")
    print("3. Grup sahiplerini kayıt edin: !kayıt <grup_id> <grup_adı>")
    
    return True

if __name__ == "__main__":
    setup_bot()
