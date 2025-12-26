/**
 * Can Kuruyemiş Drive Servisi
 * Bu servis, kullanıcıdan onay alarak Google Drive'da otomatik klasör oluşturur ve yükleme yapar.
 */

// NOT: Google Cloud Console'dan alacağın Client ID'yi buraya yazmalısın.
// Eğer henüz almadıysan, demo için OAuth penceresi açılacaktır.
const CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com'; 
const SCOPES = 'https://www.googleapis.com/auth/drive.file';
const FOLDER_NAME = 'Can Kuruyemis Arsivi';

let accessToken: string | null = null;

/**
 * Kullanıcıdan OAuth2 Access Token alır.
 */
const getAccessToken = (): Promise<string> => {
  return new Promise((resolve, reject) => {
    if (accessToken) {
      resolve(accessToken);
      return;
    }

    // @ts-ignore - google is loaded via script tag
    const client = google.accounts.oauth2.initTokenClient({
      client_id: CLIENT_ID,
      scope: SCOPES,
      callback: (response: any) => {
        if (response.error) {
          reject(response);
          return;
        }
        accessToken = response.access_token;
        resolve(response.access_token);
      },
    });
    client.requestAccessToken();
  });
};

/**
 * Drive'da klasör var mı bakar, yoksa oluşturur.
 */
const getOrCreateFolder = async (token: string): Promise<string> => {
  // 1. Önce klasörü ara
  const searchResponse = await fetch(
    `https://www.googleapis.com/drive/v3/files?q=name='${FOLDER_NAME}' and mimeType='application/vnd.google-apps.folder' and trashed=false`,
    {
      headers: { Authorization: `Bearer ${token}` }
    }
  );
  const searchData = await searchResponse.json();

  if (searchData.files && searchData.files.length > 0) {
    return searchData.files[0].id;
  }

  // 2. Yoksa oluştur
  const createResponse = await fetch('https://www.googleapis.com/drive/v3/files', {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      name: FOLDER_NAME,
      mimeType: 'application/vnd.google-apps.folder'
    })
  });
  const folderData = await createResponse.json();
  return folderData.id;
};

/**
 * Ana yükleme fonksiyonu
 */
export const uploadToGoogleDrive = async (base64Data: string, fileName: string): Promise<boolean> => {
  try {
    const token = await getAccessToken();
    const folderId = await getOrCreateFolder(token);

    // Base64'ten Blob'a çevir
    const base64Parts = base64Data.split(',');
    const byteString = atob(base64Parts[1]);
    const arrayBuffer = new ArrayBuffer(byteString.length);
    const uint8Array = new Uint8Array(arrayBuffer);
    for (let i = 0; i < byteString.length; i++) {
      uint8Array[i] = byteString.charCodeAt(i);
    }
    const blob = new Blob([uint8Array], { type: 'image/png' });

    // Metadata ve dosya yükleme (Multipart Upload)
    const metadata = {
      name: fileName,
      parents: [folderId],
      mimeType: 'image/png'
    };

    const formData = new FormData();
    formData.append('metadata', new Blob([JSON.stringify(metadata)], { type: 'application/json' }));
    formData.append('file', blob);

    const uploadResponse = await fetch(
      'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
      {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` },
        body: formData
      }
    );

    if (uploadResponse.ok) {
      alert(`✅ Başarılı! Görsel Google Drive'daki "${FOLDER_NAME}" klasörüne kaydedildi.`);
      return true;
    } else {
      const errData = await uploadResponse.json();
      console.error("Upload failed", errData);
      throw new Error("Yükleme başarısız");
    }
  } catch (error) {
    console.error("Drive upload error:", error);
    alert("Drive'a bağlanırken bir sorun oluştu. Client ID ayarlarınızı kontrol edin.");
    return false;
  }
};