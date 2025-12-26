/**
 * Can Kuruyemiş Drive Servisi
 * Bu servis, kullanıcıdan onay alarak Google Drive'da otomatik klasör oluşturur ve yükleme yapar.
 */

declare const google: any;

/** 
 * ŞAFAK ESNAFIMIN DİKKATİNE:
 * 1. Google Cloud Console'da (console.cloud.google.com) bir proje oluşturun.
 * 2. 'APIs & Services' > 'Credentials' kısmından 'OAuth 2.0 Client ID' (Web Application) oluşturun.
 * 3. 'Authorized JavaScript Origins' kısmına ŞU ANKİ TARAYICI ADRESİNİ (örn: https://...web-platform.io) ekleyin.
 * 4. OAuth Onay Ekranı (Consent Screen) kısmında projenizi 'Testing' modunda bırakıyorsanız kendi mailinizi 'Test Users' olarak ekleyin.
 */
const CLIENT_ID = '1055129081162-chh4eumm371balnqajrc1lbu5uk26chp.apps.googleusercontent.com'; 
const SCOPES = 'https://www.googleapis.com/auth/drive.file';
const FOLDER_NAME = 'Can Kuruyemis Arsivi';

let accessToken: string | null = null;

const getAccessToken = (): Promise<string> => {
  return new Promise((resolve, reject) => {
    // Placeholder kontrolü
    if (CLIENT_ID.startsWith('YOUR_')) {
      reject(new Error("Lütfen driveService.ts içindeki CLIENT_ID değişkenini Google Cloud Console'dan aldığınız ID ile değiştirin."));
      return;
    }

    if (accessToken) {
      resolve(accessToken);
      return;
    }

    try {
      if (typeof google === 'undefined') {
          throw new Error("Google Identity Services kütüphanesi yüklenemedi. Sayfayı yenileyip tekrar deneyin.");
      }

      const client = google.accounts.oauth2.initTokenClient({
        client_id: CLIENT_ID,
        scope: SCOPES,
        callback: (response: any) => {
          if (response.error) {
            console.error("Google Auth Error:", response);
            // Hata 400 detaylandırması
            let errorMsg = `Yetkilendirme hatası: ${response.error_description || response.error}`;
            if (response.error === 'invalid_client') errorMsg = "Hata 400: Client ID geçersiz veya hatalı kopyalanmış.";
            reject(new Error(errorMsg));
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
  const searchUrl = `https://www.googleapis.com/drive/v3/files?q=name='${FOLDER_NAME}' and mimeType='application/vnd.google-apps.folder' and trashed=false`;
  const searchResponse = await fetch(searchUrl, {
      headers: { Authorization: `Bearer ${token}` }
  });
  
  if (!searchResponse.ok) {
      const err = await searchResponse.json();
      throw new Error(`Klasör aranırken hata: ${err.error?.message}`);
  }

  const searchData = await searchResponse.json();
  if (searchData.files && searchData.files.length > 0) return searchData.files[0].id;

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
};

export const uploadToGoogleDrive = async (base64Data: string, fileName: string): Promise<boolean> => {
  console.log("🛠️ Drive Debug: Yükleme denemesi...", fileName);
  
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
      alert(`✅ Başarılı! Görsel Drive'daki "${FOLDER_NAME}" klasörüne atıldı.`);
      return true;
    } else {
      const errData = await uploadResponse.json();
      throw new Error(errData.error?.message || "Yükleme başarısız.");
    }
  } catch (error: any) {
    console.error("❌ Drive Hatası Detayı:", error);
    
    // Şafak Esnafım için debug yardımı:
    console.log("💡 İPUCU: Hata 400 alıyorsan, Google Cloud Console'da 'Authorized JavaScript Origins' kısmına şu adresi eklemelisin:");
    console.log(window.location.origin);
    
    alert(`❌ Hata: ${error.message}\n\nKonsoldaki (F12) talimatları takip edin.`);
    return false;
  }
};