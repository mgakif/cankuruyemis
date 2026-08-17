# Form Token Protection (Gizli Formlar)

Form'ları Google ve direkt erişimden koruma sistemi.

## Nasıl Çalışır?

Token korumalı formlar, sadece URL'de doğru `?form=TOKEN` parametresi olduğunda görüntülenebilir.

### Örnek Kullanım

```
Normal Form URL: https://siteniz.com/montaj-formu
Token Korumalı: https://siteniz.com/montaj-formu?form=whatsapptandanyonlendirilen
```

Eğer token yoksa veya yanlışsa → **404 Not Found**

## Kurulum

### 1. Migration'ı Çalıştır

```bash
docker compose exec bugass php artisan migrate
```

Bu, `forms` tablosuna şu alanları ekler:
- `requires_token` (boolean): Token koruması aktif mi?
- `access_token` (string): Erişim token'ı

### 2. Filament Admin Panelinden Form Ayarla

1. Admin panelinde **Forms** bölümüne git
2. Korumak istediğin formu aç
3. **"Erişim Kontrolü"** tab'ına geç
4. **"Token Gereksinimi"** toggle'ını aktif et
5. **"Erişim Token"** alanına benzersiz bir token gir (örn: `whatsapptandanyonlendirilen`)
6. Kaydet

### 3. Token'lı Linki Paylaş

Admin panelinde token girerken, otomatik olarak tam URL önizlemesi gösterilir:
```
https://siteniz.com/form-slug?form=TOKEN
```

Bu linki WhatsApp, email veya özel kanallardan paylaş.

## Özellikler

### 🔒 Güvenlik
- Token olmadan forma erişilemez
- 404 hatası döner (form varmış gibi görünmez)
- Brute-force'a karşı koruma (token tahmin etmesi çok zor)

### 🤖 SEO Koruması
- Token korumalı formlara otomatik `noindex, nofollow` meta tag eklenir
- Google ve diğer botlar formu indekslemez
- Sitemap'e eklenmez (zaten 404 döner token olmadan)

### 🔗 Esnek Token Sistemi
- Her form için farklı token kullanabilirsiniz
- Token'lar istediğiniz zaman değiştirilebilir
- Token olmayan formlar normal çalışmaya devam eder

### 📱 Kullanım Senaryoları

#### 1. WhatsApp'tan Yönlendirme
```
Merhaba! Montaj formunu doldurmak için şu linke tıklayın:
https://siteniz.com/montaj-formu?form=whatsapp2024
```

#### 2. Email Kampanyaları
```html
<a href="https://siteniz.com/ozel-teklif?form=email-kampanya-2024">
    Özel Teklif Formu
</a>
```

#### 3. QR Kod
QR kod oluştururken token'lı URL'yi kullan:
```
https://siteniz.com/servis-formu?form=qrkod123
```

#### 4. Kapalı Beta / Özel Erişim
```
Sadece davetli müşteriler için:
https://siteniz.com/vip-basvuru?form=davetli-musteri
```

## Token Önerileri

### ✅ İyi Token Örnekleri
```
whatsapptandanyonlendirilen
email-kampanya-2024
qr-kod-fuar-2024
vip-musteri-2024
servis-cagri-merkezi
```

### ❌ Kötü Token Örnekleri
```
123              (çok kısa, tahmin edilebilir)
form             (çok genel)
test             (çok yaygın)
a1b2c3           (basit pattern)
```

### 🎯 Token Oluşturma İpuçları

1. **En az 10-15 karakter** kullanın
2. **Anlamlı ama tahmin edilemez** olsun
3. **Özel karakterlerden kaçının** (URL'de sorun çıkarabilir)
4. **Küçük harf + tire** kullanımı ideal
5. **Her form için farklı** token kullanın

## Güvenlik Notları

### ⚠️ Dikkat Edilmesi Gerekenler

1. **Token'ları paylaşırken dikkatli olun**
   - Herkese açık yerlerde paylaşmayın
   - Sadece hedef kitleye gönderin

2. **Token'ları periyodik değiştirin**
   - Özellikle sızan token'ları hemen değiştirin
   - Kampanya bittiğinde token'ı değiştirin

3. **Loglarda token gösterebilir**
   - Access log'larında token görünür olabilir
   - Hassas veriler için ek önlem alın

4. **HTTPS kullanın**
   - Token'lar HTTP ile şifrelenmemiş gider
   - Mutlaka HTTPS kullanın

## Teknik Detaylar

### Kontrol Akışı

```php
1. Kullanıcı /form-slug?form=TOKEN 'a erişir
2. FormPageController::show() çağrılır
3. Form veritabanından çekilir
4. requires_token kontrolü yapılır
   ├─ FALSE ise → Normal göster
   └─ TRUE ise → Token kontrolü yap
       ├─ Token doğru ise → Göster
       └─ Token yanlış/yok ise → 404
```

### Meta Tags (Token Korumalı)

```html
<meta name="robots" content="noindex, nofollow">
```

Bu tag, Google'a "bu sayfayı indeksleme" der.

### Database Şeması

```sql
ALTER TABLE forms 
ADD COLUMN requires_token BOOLEAN DEFAULT FALSE,
ADD COLUMN access_token VARCHAR(255) NULL;
```

## Test Etme

### 1. Normal Erişim (404 Dönmeli)
```bash
curl -I https://siteniz.com/korunmus-form
# HTTP/1.1 404 Not Found
```

### 2. Token ile Erişim (200 Dönmeli)
```bash
curl -I https://siteniz.com/korunmus-form?form=DOGRU_TOKEN
# HTTP/1.1 200 OK
```

### 3. Yanlış Token (404 Dönmeli)
```bash
curl -I https://siteniz.com/korunmus-form?form=YANLIS_TOKEN
# HTTP/1.1 404 Not Found
```

## Sorun Giderme

### Form token ile de açılmıyor
1. Token'ı doğru yazdığınızdan emin olun (büyük/küçük harf duyarlı)
2. Cache'i temizleyin: `php artisan cache:clear`
3. Migration'ın çalıştığını kontrol edin
4. Form'da `requires_token` aktif mi kontrol edin

### Token olmadan da açılıyor
1. `requires_token` toggle'ının aktif olduğundan emin olun
2. `access_token` alanının dolu olduğundan emin olun
3. Formu kaydetmeyi unutmuş olabilirsiniz

### SEO botları formu görüyor
1. `noindex, nofollow` meta tag eklendiğinden emin olun
2. Sayfanın source kodunu kontrol edin
3. Google Search Console'da "Exclude" durumunda olmalı

## Gelişmiş Özellikler (İsteğe Bağlı)

### Token Süresi Eklemek İsterseniz

```php
// Migration
$table->timestamp('token_expires_at')->nullable();

// Controller'da kontrol
if ($form->token_expires_at && $form->token_expires_at->isPast()) {
    abort(404);
}
```

### IP Kısıtlaması Eklemek İsterseniz

```php
// Migration
$table->json('allowed_ips')->nullable();

// Controller'da kontrol
if ($form->allowed_ips && !in_array(request()->ip(), $form->allowed_ips)) {
    abort(403);
}
```

### Kullanım Sayısı Takibi

```php
// Migration
$table->integer('token_usage_count')->default(0);
$table->integer('token_usage_limit')->nullable();

// Controller'da
if ($form->token_usage_limit && $form->token_usage_count >= $form->token_usage_limit) {
    abort(410, 'Form erişim limiti doldu');
}
$form->increment('token_usage_count');
```

## API Entegrasyonu

Eğer form'u API olarak kullanmak isterseniz:

```javascript
// JavaScript örneği
const formData = new FormData();
formData.append('ad', 'Ahmet');
formData.append('email', 'ahmet@example.com');

fetch('/form/form-slug/submit?form=TOKEN', {
    method: 'POST',
    body: formData
});
```

Form submission endpoint'inde de aynı token kontrolü yapılmalı.
