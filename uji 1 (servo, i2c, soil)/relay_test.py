from machine import Pin
from time import sleep

# Pin relay (sesuaikan jika beda)
relay = Pin(15, Pin.OUT)

print("=== TEST RELAY START ===")

while True:
    print("Relay ON")
    relay.value(1)   # Aktifkan relay
    sleep(2)

    print("Relay OFF")
    relay.value(0)   # Matikan relay
    sleep(2)
