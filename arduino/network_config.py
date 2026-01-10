# =============================================================================
# NETWORK CONFIGURATION FOR RASPBERRY PI PICO W
# =============================================================================
# 📡 Update file ini setiap kali IP server berubah
# Lalu upload ulang ke Pico W menggunakan Thonny IDE
# =============================================================================

# ===========================
# OPSI 1: WiFi CCTV_UISI (CURRENT)
# ===========================
# WiFi baru: CCTV_UISI
# Pico W dan Server harus connect ke WiFi ini

SSID_CCTV = "CCTV_UISI"
PASSWORD_CCTV = "08121191"
SERVER_URL_CCTV = "http://10.134.42.169:8000/api/monitoring/insert"  # Update jika IP berubah

# ===========================
# OPSI 2: WiFi Bocil (BACKUP)
# ===========================
# WiFi lama (backup jika CCTV_UISI tidak tersedia)

SSID_BOCIL = "Bocil"
PASSWORD_BOCIL = "kesayanganku"
SERVER_URL_BOCIL = "http://192.168.18.35:8000/api/monitoring/insert"

# ===========================
# OPSI 3: ETHERNET CONNECTION
# ===========================
# Jika server pakai Ethernet (kabel)

SSID_ETHERNET = "CCTV_UISI"  # Pico W tetap pakai WiFi
PASSWORD_ETHERNET = "08121191"
SERVER_URL_ETHERNET = "http://10.134.42.169:8000/api/monitoring/insert"

# ===========================
# OPSI 3: LOCALHOST (TESTING)
# ===========================
# Hanya untuk testing, TIDAK AKAN WORK untuk Pico W
# (Pico W tidak bisa akses localhost komputer lain)

SERVER_URL_LOCALHOST = "http://127.0.0.1:8000/api/monitoring/insert"

# ===========================
# ACTIVE CONFIGURATION
# ===========================
# Pilih salah satu (uncomment yang ingin digunakan):

# CURRENT: WiFi CCTV_UISI (Active) ✅
SSID = SSID_CCTV
PASSWORD = PASSWORD_CCTV
SERVER_URL = SERVER_URL_CCTV

# Backup WiFi Bocil (uncomment jika CCTV_UISI tidak tersedia):
# SSID = SSID_BOCIL
# PASSWORD = PASSWORD_BOCIL
# SERVER_URL = SERVER_URL_BOCIL

# ===========================
# DEVICE INFO
# ===========================
DEVICE_ID = "PICO_CABAI_01"

# ===========================
# CARA CEK IP SERVER
# ===========================
# Windows PowerShell:
#   ipconfig | Select-String "IPv4|Wireless|Ethernet"
#
# CMD:
#   ipconfig
#
# Linux/Mac:
#   ifconfig
#   ip addr show

# ===========================
# TROUBLESHOOTING
# ===========================
# 1. Pico W tidak bisa connect ke server?
#    → Pastikan Pico W dan Server dalam satu jaringan
#    → Cek firewall Windows (port 8000 harus open)
#    → Ping test: ping 10.134.42.169
#
# 2. Server IP berubah?
#    → Jalankan ipconfig
#    → Update SERVER_URL di file ini
#    → Upload ulang ke Pico W
#
# 3. WiFi "Bocil" tidak tersedia?
#    → Ubah SSID dan PASSWORD sesuai WiFi yang ada
#    → Pastikan server juga terhubung ke WiFi yang sama

print("✅ Network config loaded!")
print(f"📡 SSID: {SSID}")
print(f"🌐 Server: {SERVER_URL}")
print(f"🆔 Device: {DEVICE_ID}")

