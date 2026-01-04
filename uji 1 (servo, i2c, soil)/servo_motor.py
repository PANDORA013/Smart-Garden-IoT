from machine import PWM, Pin

class ServoMotor:
    def __init__(self, pin_pwm, freq=50):
        self.servo = PWM(Pin(pin_pwm))
        self.servo.freq(freq)
        self.min_duty = 1802
        self.max_duty = 7865

    def open(self):
        self.servo.duty_u16(self.max_duty)

    def close(self):
        self.servo.duty_u16(self.min_duty)
