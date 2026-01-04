from machine import I2C, Pin
from pico_i2c_lcd import I2cLcd

class LCDDisplay:
    def __init__(self, addr=0x27, rows=2, cols=16):
        self.i2c = I2C(0, sda=Pin(0), scl=Pin(1))
        self.lcd = I2cLcd(self.i2c, addr, rows, cols)

    def show(self, line1, line2=""):
        self.lcd.clear()
        self.lcd.move_to(0, 0)
        self.lcd.putstr(line1)
        if line2:
            self.lcd.move_to(0, 1)
            self.lcd.putstr(line2)
from machine import I2C, Pin
from pico_i2c_lcd import I2cLcd

class LCDDisplay:
    def __init__(self, addr=0x27, rows=2, cols=16):
        self.i2c = I2C(0, sda=Pin(0), scl=Pin(1))
        self.lcd = I2cLcd(self.i2c, addr, rows, cols)

    def show(self, line1, line2=""):
        self.lcd.clear()
        self.lcd.move_to(0, 0)
        self.lcd.putstr(line1)
        if line2:
            self.lcd.move_to(0, 1)
            self.lcd.putstr(line2)
