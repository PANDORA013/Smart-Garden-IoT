from machine import ADC, Pin

class SoilMoisture:
    def __init__(self, pin_adc):
        self.adc = ADC(Pin(pin_adc))

    def read_raw(self):
        return self.adc.read_u16()

    def read_percent(self):
        raw = self.read_raw()
        percent = 100 - ((raw / 65535) * 100)
        return percent
