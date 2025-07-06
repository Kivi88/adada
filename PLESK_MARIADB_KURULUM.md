# MariaDB ile Plesk Kurulum Rehberi

## 1. Dosya Hazırlığı (MariaDB için)

### 1.1 Database.php Değiştirme
Plesk'e yüklemeden önce:
1. `config/database.php` dosyasını silin
2. `config/database_mariadb.php` dosyasını `config/database.php` olarak yeniden adlandırın

### 1.2 Veritabanı Bilgilerini Güncelleme
`config/database.php` dosyasında şu satırları Plesk'teki bilgilerinizle değiştirin:
```php
$host = 'localhost'; // Genellikle localhost
$dbname = 'roblox_group_mgmt'; // Plesk'te oluşturacağınız DB adı
$username = 'your_db_user'; // Plesk'te oluşturacağınız kullanıcı
$password = 'your_db_password'; // Güçlü şifre
```

## 2. Plesk'te MariaDB Veritabanı Oluşturma

### 2.1 Veritabanı Oluşturma
1. Plesk panelinde **"Databases"** bölümüne gidin
2. **"Add Database"** butonuna tıklayın
3. Ayarlar:
   ```
   Database Type: MySQL/MariaDB
   Database Name: roblox_group_mgmt
   Database User: rgm_user
   Password: [güçlü şifre oluşturun]
   ```

### 2.2 Tabloları Oluşturma
1. **"phpMyAdmin"** veya **"Adminer"** açın
2. Oluşturduğunuz veritabanını seçin
3. **"SQL"** sekmesine gidin
4. `database/mariadb_schema.sql` dosyasının içeriğini kopyalayıp çalıştırın

## 3. PHP Ayarları (MariaDB için)

### 3.1 Gerekli Extensions
Plesk'te **"PHP Settings"** bölümünde aktif olması gerekenler:
```
✓ pdo_mysql
✓ mysql
✓ mysqli
✓ curl
✓ json
✓ session
✓ mbstring
✓ openssl
```

### 3.2 PHP.ini Ayarları
```ini
mysql.default_host = localhost
mysql.default_user = rgm_user
mysql.default_password = your_password
mysqli.default_host = localhost
mysqli.default_user = rgm_user
mysqli.default_pw = your_password
```

## 4. Veritabanı Bağlantısı Test Etme

### 4.1 Test Dosyası Oluşturma
`test_db.php` dosyası oluşturun:
```php
<?php
require_once 'config/database.php';

echo "Veritabanı bağlantısı başarılı!<br>";
echo "MySQL Versiyonu: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";

// Test sorgusu
$stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
$result = $stmt->fetch();
echo "Kullanıcı sayısı: " . $result['user_count'];
?>
```

### 4.2 Test Çalıştırma
`https://domain.com/test_db.php` adresine gidin

## 5. Giriş Bilgileri (MariaDB)

### 5.1 Demo Hesaplar
```
Admin: admin / admin123
Grup Sahibi: demo_owner / demo123
```

### 5.2 Demo Test Verileri
```
Grup ID: 123456 (Demo grup)
Kullanıcı: DemoKullanici (Demo oyuncu)
```

## 6. MariaDB Optimizasyon

### 6.1 my.cnf Ayarları (Eğer erişim varsa)
```ini
[mysqld]
innodb_buffer_pool_size = 128M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
```

### 6.2 Veritabanı Backup
```sql
-- Backup alma
mysqldump -u rgm_user -p roblox_group_mgmt > backup.sql

-- Geri yükleme
mysql -u rgm_user -p roblox_group_mgmt < backup.sql
```

## 7. Hata Giderme

### 7.1 Yaygın Hatalar

**"Connection refused":**
- Host bilgisini kontrol edin
- Port 3306'nın açık olduğundan emin olun

**"Access denied":**
- Kullanıcı adı/şifre kontrol edin
- Kullanıcının veritabanına erişim yetkisi var mı kontrol edin

**"Table doesn't exist":**
- `mariadb_schema.sql` dosyasını çalıştırdınız mı?
- Tablo isimleri doğru mu?

### 7.2 Debug Modu
Hata ayıklama için `config/database.php`'de:
```php
// Geliştirme ortamında hataları göster
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 8. Güvenlik (MariaDB)

### 8.1 Kullanıcı Yetkileri
```sql
-- Sadece gerekli yetkileri verin
GRANT SELECT, INSERT, UPDATE, DELETE ON roblox_group_mgmt.* TO 'rgm_user'@'localhost';
FLUSH PRIVILEGES;
```

### 8.2 SSL Bağlantısı
```php
// SSL ile bağlantı (opsiyonel)
$pdo = new PDO($dsn, $username, $password, [
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca.pem'
]);
```

## 9. Performans İzleme

### 9.1 Slow Query Log
```sql
-- Yavaş sorguları logla
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

### 9.2 İndeks Kontrolü
```sql
-- İndeksleri kontrol et
SHOW INDEX FROM users;
SHOW INDEX FROM group_members_cache;
```

Bu rehberi takip ederek MariaDB ile başarıyla kurulum yapabilirsiniz.