# reCAPTCHA Sorun Giderme Kılavuzu

## Sorun: "Lütfen robot olmadığınızı doğrulayın" hatası

### Yapılan İyileştirmeler

1. **JavaScript tarafında**:
   - `grecaptcha.getResponse()` ile response doğru alınıyor
   - FormData'ya manuel olarak ekleniyor/güncelleniyor
   - Debug console.log'ları eklendi

2. **Backend tarafında**:
   - ReCaptcha validation rule iyileştirildi
   - Hata durumları loglanıyor
   - Boş değer kontrolü eklendi

3. **Form reset**:
   - Başarılı submit'ten sonra `grecaptcha.reset()` çağrılıyor

### Adım 1: .env Dosyasını Kontrol Et

```bash
# .env dosyasında şu satırların olduğundan emin ol:
RECAPTCHA_SITE_KEY=your_site_key_here
RECAPTCHA_SECRET_KEY=your_secret_key_here
```

**Site Key ve Secret Key'i nereden alacaksın?**

1. https://www.google.com/recaptcha/admin adresine git
2. Google hesabınla giriş yap
3. Yeni site kaydı oluştur veya mevcut siteyi seç
4. **Site Key** (public key): Frontend'de kullanılır
5. **Secret Key**: Backend'de kullanılır

### Adım 2: Config Cache'i Temizle

```bash
docker compose exec bugass php artisan config:clear
docker compose exec bugass php artisan cache:clear
```

### Adım 3: Test Et

1. Formu aç (tarayıcıda)
2. F12 ile Console'u aç
3. reCAPTCHA checkbox'ını işaretle
4. Console'da şunları gör:

```
reCAPTCHA Response: 03AGdBq...uzun_bir_string
FormData has g-recaptcha-response: true
FormData contents:
ad: Test
email: test@example.com
g-recaptcha-response: 03AGdBq...uzun_bir_string
```

### Adım 4: Backend Log'larını Kontrol Et

```bash
docker compose exec bugass tail -f storage/logs/laravel.log
```

Form submit ettiğinde şunları görmelisin:

**Başarılı ise:**
```
[2024-02-06 22:00:00] local.INFO: reCAPTCHA Verification Result {"success":true,"error_codes":[]}
```

**Başarısız ise:**
```
[2024-02-06 22:00:00] local.WARNING: reCAPTCHA verification failed {"error_codes":["invalid-input-secret","timeout-or-duplicate"]}
```

### Adım 5: Olası Hatalar ve Çözümleri

#### 1. "reCAPTCHA Response: ''" (Boş string)

**Sorun**: reCAPTCHA checkbox'ı işaretlenmemiş

**Çözüm**: 
- Checkbox'ı işaretle
- Google reCAPTCHA script'inin yüklendiğinden emin ol
- Console'da `typeof grecaptcha` yaz, `object` dönmeli

#### 2. "grecaptcha is not defined"

**Sorun**: Google reCAPTCHA script'i yüklenmemiş

**Çözüm**:
- Sayfanın kaynak kodunu görüntüle (Ctrl+U)
- Şu script'i ara: `https://www.google.com/recaptcha/api.js`
- Yoksa form-widget.blade.php'deki condition'ı kontrol et

**Kontrol komutu**:
```bash
# Form'da recaptcha aktif mi?
docker compose exec bugass php artisan tinker
>>> \App\Models\Form::where('slug', 'form-slug')->first()->recaptcha
=> true

# Config'de site key var mı?
>>> config('services.recaptcha.site_key')
=> "6Le..."
```

#### 3. "invalid-input-secret"

**Sorun**: Secret key yanlış veya eksik

**Çözüm**:
```bash
# .env dosyasını kontrol et
docker compose exec bugass cat .env | grep RECAPTCHA

# Çıktı şöyle olmalı:
RECAPTCHA_SITE_KEY=6Le...
RECAPTCHA_SECRET_KEY=6Le...

# Sonra cache'i temizle
docker compose exec bugass php artisan config:clear
```

#### 4. "timeout-or-duplicate"

**Sorun**: reCAPTCHA response'u zaten kullanılmış veya zaman aşımına uğramış

**Çözüm**:
- reCAPTCHA checkbox'ını tekrar işaretle
- 2 dakikadan fazla beklediysen yenile

#### 5. "missing-input-response"

**Sorun**: Backend'e g-recaptcha-response gönderilmemiş

**Çözüm**:
- Console'da `FormData contents` çıktısını kontrol et
- `g-recaptcha-response` var mı?
- Yoksa JavaScript hatası var demektir

#### 6. Site key localhost'ta çalışmıyor

**Sorun**: reCAPTCHA site key domain'e özel

**Çözüm**:
1. https://www.google.com/recaptcha/admin git
2. Site ayarlarını aç
3. "Domains" kısmına `localhost` ekle
4. Veya yeni bir test site key oluştur

### Adım 6: Test Komutları

#### reCAPTCHA Config'i Test Et

```bash
docker compose exec bugass php artisan tinker
```

```php
// Site key var mı?
config('services.recaptcha.site_key')

// Secret key var mı?
config('services.recaptcha.secret_key')

// Form'da recaptcha aktif mi?
\App\Models\Form::where('slug', 'YOUR_FORM_SLUG')->first()->recaptcha
```

#### Manuel reCAPTCHA Test

```bash
docker compose exec bugass php artisan tinker
```

```php
use Illuminate\Support\Facades\Http;

$response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
    'secret' => config('services.recaptcha.secret_key'),
    'response' => 'TEST_RESPONSE_FROM_FRONTEND', // Console'dan kopyala
    'remoteip' => '127.0.0.1',
]);

$response->json();
// ['success' => true] görmeli
```

### Adım 7: Production'da Test

Production'da farklı domain kullanıyorsanız:

1. Google reCAPTCHA Admin'e git
2. Site ayarlarında domain ekle: `yourdomain.com`
3. Cache'i temizle (production'da)
4. Test et

### Debug Mode'u Kapat

Test tamamlandıktan sonra debug console.log'larını kapat:

```javascript
// form-widget.blade.php içindeki bu satırları yorum yap veya sil:
console.log('reCAPTCHA Response:', recaptchaResponse);
console.log('FormData has g-recaptcha-response:', formData.has('g-recaptcha-response'));
console.log('FormData contents:');
for (let pair of formData.entries()) {
    console.log(pair[0] + ': ' + pair[1]);
}
```

### Alternatif: reCAPTCHA v3'e Geçiş (Opsiyonel)

Eğer v2 checkbox'ı kullanmak istemiyorsanız, reCAPTCHA v3'e geçebilirsiniz:

**Avantajları**:
- Kullanıcı etkileşimi gerektirmez
- Arka planda score hesaplar
- Daha iyi UX

**Dezavantajları**:
- Score threshold ayarlamanız gerekir
- Bazı botlar geçebilir

### Sık Sorulan Sorular

**S: reCAPTCHA'yı devre dışı bırakabilir miyim?**

C: Evet, Filament admin panelinde Form > "Genel Bilgiler" tab'ında "Google reCAPTCHA Aktif" toggle'ını kapat.

**S: Test ortamında reCAPTCHA'yı bypass edebilir miyim?**

C: Evet, `.env` dosyasında:
```
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=
```
Boş bırakırsan reCAPTCHA kontrolü yapılmaz.

**S: Birden fazla form varsa sorun çıkar mı?**

C: Hayır, her form kendi reCAPTCHA widget'ına sahip. `grecaptcha.getResponse()` aktif widget'tan response alır.

**S: AJAX submit ile reCAPTCHA çalışır mı?**

C: Evet, bizim implementasyonumuz AJAX uyumlu. FormData'ya manuel ekliyoruz.

### İletişim

Sorun devam ederse:
1. Browser console'daki hataları kaydet
2. Laravel log'larını kaydet
3. Network tab'da form submit request'ini incele (Payload kısmında `g-recaptcha-response` var mı?)
