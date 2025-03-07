#!/bin/bash

# Konfigurasi database
DB_SERVER=localhost
DB_USERNAME=root
DB_PASSWORD=""
DB_NAME=school_pweb_lelang

# Lokasi aplikasi
BASE_URL="localhost:8001" # This matches the $url variable in your PHP file

echo "Membuat database..."
mysql -u $DB_USERNAME -p$DB_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME"
mysql -u $DB_USERNAME -p$DB_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"

echo "Mengimpor struktur database dan data awal..."

SQL_FILE="lelang_php.sql"
if [[ -f "$SQL_FILE" ]]; then
  mysql -u $DB_USERNAME -p$DB_PASSWORD $DB_NAME <"$SQL_FILE"
  echo "Database structure and initial data imported successfully."
else
  echo "Error: SQL file '$SQL_FILE' not found!"
  exit 1
fi
