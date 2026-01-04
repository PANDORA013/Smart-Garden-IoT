from machine import I2C, Pin

i2c = I2C(0, sda=Pin(0), scl=Pin(1), freq=400000)

devices = i2c.scan()

if devices:
    print("I2C devices found:")
    for d in devices:
        print(hex(d))
else:
    print("No I2C devices found")
