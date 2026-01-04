from time import sleep
from soil_sensor import SoilMoisture
from servo_motor import ServoMotor
from lcd_display import LCDDisplay

# =====================
# KONFIGURASI
# =====================
THRESHOLD = 40  # persen kelembaban tanah

# =====================
# INISIALISASI OBJEK
# =====================
soil = SoilMoisture(pin_adc=26)
servo = ServoMotor(pin_pwm=9)
lcd = LCDDisplay()

lcd.show("Smart Irrigation", "Initializing...")
sleep(2)

# =====================
# LOOP UTAMA
# =====================
while True:
    moisture = soil.read_percent()

    if moisture < THRESHOLD:
        servo.open()
        status = "Kering"
    else:
        servo.close()
        status = "Lembab"

    lcd.show(
        "Soil:{:.1f}%".format(moisture),
        "Status:" + status
    )

    sleep(2)
