/**
 * Can Kuruyemiş Drive Servisi
 * Bu servis, kullanıcıdan onay alarak Google Drive'da otomatik klasör oluşturur ve yükleme yapar.
 */

declare const google: any;

/** 
 * ŞAFAK ESNAFIMIN DİKKATİNE:
 * Ekran görüntüsündeki ayarların DOĞRU. 
 * Eğer hala hata alıyorsan:
 * 1. Google'ın bu ayarları kaydetmesi bazen 30 dakika sürebilir.
 * 2. Kodun içindeki CLIENT_ID ile Google Console'daki 'Client ID'nin birebir aynı olduğundan emin ol.
 */
const CLIENT_ID = '1055129081162-chh4eumm371balnqajrc1lbu5uk26chp.apps.googleusercontent.com'; 
const SCOPES = 'https://www.googleapis.com/auth/drive.file';
const FOLDER_NAME = 'Can Kuruyemis Arsivi';

let accessToken: string | null = null;

const getAccessToken = (): Promise<string> => {
  return new Promise((resolve, reject) => {
    // Mevcut origin'i logluyoruz (Slash hatası ihtimaline karşı)
    const currentOrigin = window.location.origin;
    console.log("🛠️ Google Cloud Console'da olması gereken adres:", currentOrigin);

    if (accessToken) {
      resolve(accessToken);
      return;
    }

    try {
      if (typeof google === 'undefined') {
          throw new Error("Google kütüphanesi yüklenemedi. Sayfayı yenileyip tekrar deneyin.");
      }

      const client = google.accounts.oauth2.initTokenClient({
        client_id: CLIENT_ID,
        scope: SCOPES,
        ux_mode: 'popup', // Açıkça popup modunu belirtiyoruz
        callback: (response: any) => {
          if (response.error) {
            console.error("❌ Google Yetki Hatası:", response);
            
            let userFriendlyMsg = `Yetkilendirme Hatası: ${response.error}`;
            
            if (response.error === 'redirect_uri_mismatch' || response.error === 'invalid_request') {
              userFriendlyMsg = `Hata 400: Adres Uyuşmazlığı!\n\nŞafak Esnafım, Google Cloud panelinde 'Authorized JavaScript Origins' kısmındaki adresin başında veya sonunda boşluk olmadığından emin ol.\n\nEğer yeni eklediysen 5-10 dakika bekleyip tekrar dene.`;
            }
            
            reject(new Error(userFriendlyMsg));
            return;
          }
          accessToken = response.access_token;
          resolve(response.access_token);
        },
      });
      client.requestAccessToken();
    } catch (err) {
      reject(err);
    }
  });
};

const getOrCreateFolder = async (token: string): Promise<string> => {
  try {
    const searchUrl = `https://www.googleapis.com/drive/v3/files?q=name='${FOLDER_NAME}' and mimeType='application/vnd.google-apps.folder' and trashed=false`;
    const searchResponse = await fetch(searchUrl, {
        headers: { Authorization: `Bearer ${token}` }
    });
    
    if (!searchResponse.ok) throw new Error("Klasör aranırken hata oluştu.");

    const searchData = await searchResponse.json();
    if (searchData.files && searchData.files.length > 0) return searchData.files[0].id;

    // Klasör yoksa oluştur
    const createResponse = await fetch('https://www.googleapis.com/drive/v3/files', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ name: FOLDER_NAME, mimeType: 'application/vnd.google-apps.folder' })
    });
    
    const folderData = await createResponse.json();
    return folderData.id;
  } catch (e) {
    console.error("Klasör Hatası:", e);
    throw new Error("Drive'da klasör hazırlanamadı.");
  }
};

export const uploadToGoogleDrive = async (base64Data: string, fileName: string): Promise<boolean> => {
  console.log("🚀 Drive yükleme işlemi başlatılıyor...");
  
  try {
    const token = await getAccessToken();
    const folderId = await getOrCreateFolder(token);

    const base64Parts = base64Data.split(',');
    const byteString = atob(base64Parts[1]);
    const arrayBuffer = new ArrayBuffer(byteString.length);
    const uint8Array = new Uint8Array(arrayBuffer);
    for (let i = 0; i < byteString.length; i++) {
      uint8Array[i] = byteString.charCodeAt(i);
    }
    const blob = new Blob([uint8Array], { type: 'image/png' });

    const metadata = { name: fileName, parents: [folderId], mimeType: 'image/png' };
    const formData = new FormData();
    formData.append('metadata', new Blob([JSON.stringify(metadata)], { type: 'application/json' }));
    formData.append('file', blob);

    const uploadResponse = await fetch(
      'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
      { method: 'POST', headers: { Authorization: `Bearer ${token}` }, body: formData }
    );

    if (uploadResponse.ok) {
      alert(`✅ Harika! Görsel Google Drive'da "${FOLDER_NAME}" klasörüne başarıyla yüklendi.`);
      return true;
    } else {
      const errData = await uploadResponse.json();
      throw new Error(errData.error?.message || "Yükleme tamamlanamadı.");
    }
  } catch (error: any) {
    console.error("❌ İşlem Hatası:", error);
    alert(`💡 Şafak Esnafım Dikkat:\n${error.message}`);
    return false;
  }
};