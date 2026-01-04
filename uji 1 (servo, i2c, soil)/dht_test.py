from machine import Pin
import dht
from time import sleep

# DHT11 di GP15
sensor = dht.DHT11(Pin(15))

print("=== TEST DHT11 ===")

while True:
    try:
        sensor.measure()
        temp = sensor.temperature()
        hum = sensor.humidity()

        print("Temperature: {} °C".format(temp))
        print("Humidity   : {} %".format(hum))
        print("-----------------------")

    except OSError as e:
        print("Read error:", e)

    sleep(2)
