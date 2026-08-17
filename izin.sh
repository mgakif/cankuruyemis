#!/bin/bash

DIR="$1"
USER_INPUT="$2"

# Klasör kontrolü
if [ -z "$DIR" ]; then
  echo "Kullanım: ./izin.sh klasor_adi [username]"
  exit 1
fi

if [ ! -d "$DIR" ]; then
  echo "Hata: '$DIR' diye bir klasör yok"
  exit 1
fi

# Kullanıcı belirleme
if [ -z "$USER_INPUT" ]; then
  USER=$(whoami)
else
  if ! id "$USER_INPUT" &>/dev/null; then
    echo "Hata: '$USER_INPUT' diye bir kullanıcı yok"
    exit 1
  fi
  USER="$USER_INPUT"
fi

echo "İzinler ayarlanıyor:"
echo "  Klasör : $DIR"
echo "  Kullanıcı : $USER"

# Sahipliği ayarla
sudo chown -R "$USER":"$USER" "$DIR"


# Dizinler ve dosyalar
find "$DIR" -type d -exec chmod 755 {} \;
find "$DIR" -type f -exec chmod 644 {} \;

echo "Tamam. IDE artık yazabilir, Docker da köşede düşünsün."
