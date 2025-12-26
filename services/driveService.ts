/**
 * Can Kuruyemiş Drive Servisi
 * Bu servis, kullanıcıdan onay alarak Google Drive'da otomatik klasör oluşturur ve yükleme yapar.
 */

declare const google: any;

/** 
 * ŞAFAK ESNAFIMIN DİKKATİNE:
 * Aldığın 'redirect_uri_mismatch' hatasını çözmek için:
 * 1. console.cloud.google.com adresine git.
 * 2. APIs & Services > Credentials > OAuth 2.0 Client ID (Web Application) içine gir.
 * 3. 'Authorized JavaScript Origins' kısmına AŞAĞIDAKİ ADRESİ EKLE:
 *    (Tarayıcı adres çubuğundaki protokol dahil kök dizin)
 */
const CLIENT_ID = '1055129081162-chh4eumm371balnqajrc1lbu5uk26chp.apps.googleusercontent.com'; 
const SCOPES = 'https://www.googleapis.com/auth/drive.file';
const FOLDER_NAME = 'Can Kuruyemis Arsivi';

let accessToken: string | null = null;

const getAccessToken = (): Promise<string> => {
  return new Promise((resolve, reject) => {
    // Mevcut origin'i konsola bas ki kullanıcı kopyalayabilsin
    console.log("🛠️ Google Cloud Console'a eklemen gereken ORIGIN:", window.location.origin);

    if (accessToken) {
      resolve(accessToken);
      return;
    }

    try {
      if (typeof google === 'undefined') {
          throw new Error("Google Kütüphanesi (GSI) henüz yüklenmedi. Lütfen sayfayı yenileyin.");
      }

      const client = google.accounts.oauth2.initTokenClient({
        client_id: CLIENT_ID,
        scope: SCOPES,
        callback: (response: any) => {
          if (response.error) {
            console.error("❌ Google Yetkilendirme Hatası Detayı:", response);
            let msg = `Yetkilendirme hatası: ${response.error_description || response.error}`;
            if (response.error === 'redirect_uri_mismatch' || response.error === 'invalid_request') {
                msg += "\n\nİPUCU: Google Cloud Console'da 'Authorized JavaScript Origins' kısmına şu adresi eklemelisiniz: " + window.location.origin;
            }
            reject(new Error(msg));
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
  console.log("🚀 Drive yükleme denemesi başlatıldı...");
  
  try {
    const token = await getAccessToken();
    const folderId = await getOrCreateFolder(token);

    const base64Parts = base64Data.split(',');
    if (base64Parts.length < 2) throw new Error("Görsel verisi bozuk.");
    
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
      throw new Error(errData.error?.message || "Yükleme sırasında teknik bir hata oluştu.");
    }
  } catch (error: any) {
    console.error("❌ Drive Yükleme Hatası:", error);
    alert(`❌ Hata: ${error.message}`);
    return false;
  }
};