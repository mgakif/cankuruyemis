# Form Submissions Management System

Form 10 gönderilerini görüntüleme, dışa aktarma ve istatistik sistemi.

## Kurulum

1. **Migration'ı çalıştırın:**
```bash
docker compose exec bugass php artisan migrate
```

## Kullanım

### Sayfalar ve Rotalar

#### 1. Form Kayıtlarını Görüntüle
- **URL:** `/form-submissions/form-10`
- **Route:** `form-submissions.form10`
- **Açıklama:** Tüm Form 10 kayıtlarını listeler, sayfalandırma ile
- **Özellikler:**
  - Tablo görünümü
  - Modal ile detay görüntüleme
  - Cevaplandı/Bekliyor durumu
  - Sayfalandırma (20 kayıt/sayfa)

#### 2. CSV Dışa Aktarma
- **URL:** `/form-submissions/form-10/download-csv`
- **Route:** `form-submissions.form10.download-csv`
- **Açıklama:** Tüm kayıtları CSV formatında indirir
- **Özellikler:**
  - UTF-8 BOM (Türkçe karakter desteği)
  - Excel uyumlu
  - Tarih damgalı dosya adı

#### 3. JSON Dışa Aktarma
- **URL:** `/form-submissions/form-10/export-json`
- **Route:** `form-submissions.form10.export-json`
- **Açıklama:** API için JSON formatında veri sağlar
- **Response:**
```json
{
  "success": true,
  "count": 10,
  "data": [...]
}
```

#### 4. İstatistikler
- **URL:** `/form-submissions/form-10/stats`
- **Route:** `form-submissions.form10.stats`
- **Açıklama:** Form kayıtları için detaylı istatistikler
- **Özellikler:**
  - Toplam kayıt sayısı
  - Cevaplanan/bekleyen sayıları
  - Son 7 günlük kayıtlar
  - Ürün dağılımı grafiği (Pie chart)
  - İl dağılımı grafiği (Bar chart)
  - Detaylı tablolar

#### 5. Dışa Aktarma Sayfası
- **URL:** `/form-submissions/form-10/export`
- **Route:** `form-submissions.form10.export`
- **Açıklama:** CSV ve JSON dışa aktarma seçeneklerini gösteren sayfa

## CSV Format

CSV dosyası aşağıdaki sütunları içerir:

- ID
- Form ID
- Ad Soyad (DB)
- Email (DB)
- Cevaplandı
- Oluşturulma Tarihi
- Güncellenme Tarihi
- Ad
- Soyad
- Email
- Telefon
- İl
- İlçe
- Adres
- Gaz Kontrol
- Arızalı Ürün
- Garanti
- Kabul
- Mesaj Notu

## Güvenlik

**ÖNEMLİ:** Bu rotalar şu anda açık ve herkese erişilebilir durumda!

Production'da mutlaka authentication ekleyin:

```php
Route::middleware(['auth'])->prefix('form-submissions')->group(function () {
    // ... routes ...
});
```

veya admin paneli üzerinden erişim sağlayın.

## Diğer Formlar için Genişletme

Başka form ID'leri için benzer sayfalar oluşturmak isterseniz:

1. Controller'da yeni metodlar ekleyin (örn: `showForm11`, `downloadForm11CSV`)
2. Routes ekleyin
3. View dosyalarını kopyalayıp form_id'yi değiştirin

Veya daha dinamik bir yapı için form_id'yi parametre olarak alacak şekilde refactor edin:

```php
Route::get('/{formId}', [FormSubmissionController::class, 'show'])
    ->where('formId', '[0-9]+');
```

## Teknolojiler

- **Backend:** Laravel 12
- **Frontend:** Bootstrap 5.3
- **Charts:** Chart.js
- **Format:** CSV (UTF-8 BOM), JSON

## Özelleştirme

### Sayfa Başına Kayıt Sayısı
`FormSubmissionController@showForm10` içinde `paginate(20)` değerini değiştirin.

### CSV Sütunları
`FormSubmissionController@downloadForm10CSV` içinde `$headers` ve `$row` array'lerini düzenleyin.

### Grafik Renkleri
`stats-form10.blade.php` içinde Chart.js `backgroundColor` değerlerini değiştirin.

## Sorun Giderme

### "Form ID 10 için kayıt bulunamadı" hatası
- Form ID'si 10 olan kayıt var mı kontrol edin
- Database'de `form_submissions` tablosunu kontrol edin

### Türkçe karakterler bozuk görünüyor
- CSV'yi Excel'de açarken "Veri" > "Metinden" seçeneğini kullanın
- UTF-8 kodlamasını seçin

### Grafikler görünmüyor
- İnternet bağlantınızı kontrol edin (Chart.js CDN'den yükleniyor)
- Browser console'da hata var mı kontrol edin
