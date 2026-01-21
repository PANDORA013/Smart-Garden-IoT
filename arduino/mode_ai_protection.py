"""
Mode AI (Fuzzy Logic) Implementation untuk Pico W
Dengan proteksi tambahan untuk keamanan pompa

Status: ✅ Production Ready
Version: 1.0
Date: 21-01-2026
"""

import time


class ModeAI:
    """
    Implementasi Mode AI dengan Fuzzy Logic
    Mencegah pompa hidup terus-menerus dengan watchdog timer
    """
    
    def __init__(self, sensor_min=4095, sensor_max=1500, 
                 threshold_on=35, threshold_off=65):
        """
        Initialize Mode AI
        
        Args:
            sensor_min: ADC value saat sensor kering (udara) - default 4095
            sensor_max: ADC value saat sensor basah (air) - default 1500
            threshold_on: Threshold untuk pompa ON (%) - default 35%
            threshold_off: Threshold untuk pompa OFF (%) - default 65%
        """
        self.sensor_min = sensor_min
        self.sensor_max = sensor_max
        self.threshold_on = threshold_on
        self.threshold_off = threshold_off
        
        # Safety parameters
        self.pump_on_time = 0              # Track pompa ON duration (seconds)
        self.max_pump_duration = 300       # Proteksi: Max 5 menit (seconds)
        self.check_interval = 10           # Cek setiap 10 detik
        self.last_check_time = 0           # Timestamp last check
        
        # Status flags
        self.is_active = False
        self.pump_was_on = False           # Track previous pump state
        
        print("[MODE_AI] 🤖 Initialized with proteksi tambahan")
        print(f"  - Sensor range: {sensor_min} (kering) - {sensor_max} (basah)")
        print(f"  - Threshold: ON @ {threshold_on}%, OFF @ {threshold_off}%")
        print(f"  - Watchdog: Max pump duration = {self.max_pump_duration}s")
    
    def adc_to_percentage(self, raw_adc):
        """
        Convert ADC value to soil moisture percentage (0-100%)
        
        Formula:
            percentage = (sensor_min - raw_adc) / (sensor_min - sensor_max) * 100
        
        Args:
            raw_adc: Raw ADC value dari sensor
            
        Returns:
            Soil moisture percentage (0-100%)
        """
        # Clamp raw_adc ke valid range
        raw_adc = max(self.sensor_max, min(self.sensor_min, raw_adc))
        
        # Calculate percentage
        # sensor_min = 0% (kering maksimal)
        # sensor_max = 100% (basah maksimal)
        if self.sensor_min == self.sensor_max:
            return 50  # Safety fallback
        
        percentage = ((self.sensor_min - raw_adc) * 100) // (self.sensor_min - self.sensor_max)
        
        # Clamp hasil ke 0-100%
        return max(0, min(100, percentage))
    
    def run(self, current_adc, pump_relay, current_time=None):
        """
        Main loop untuk Mode AI
        Eksekusi logika Fuzzy dengan proteksi keamanan
        
        Args:
            current_adc: Raw ADC value dari soil sensor
            pump_relay: Relay object dengan method on() dan off()
            current_time: Current timestamp (auto-detect jika None)
            
        Returns:
            True jika execution berhasil, False jika error
        """
        
        if current_time is None:
            current_time = time.time()
        
        # ===== PROTEKSI 1: Validasi Sensor =====
        if current_adc <= 0:
            print("[MODE_AI] ❌ ERROR: Sensor offline (ADC=0), pompa OFF")
            pump_relay.off()
            self.pump_on_time = 0
            return False
        
        # ===== PROTEKSI 2: Check Timeout Loop =====
        if current_time - self.last_check_time < self.check_interval:
            return True  # Skip cycle, belum waktunya
        
        self.last_check_time = current_time
        
        # ===== Konversi ADC ke Persentase =====
        soil_moisture = self.adc_to_percentage(current_adc)
        
        # ===== LOGIKA FUZZY AI =====
        pump_should_be_on = False
        
        if soil_moisture < self.threshold_on:
            # Tanah KERING → Pompa ON
            pump_should_be_on = True
            log_msg = f"💧 DRY: Pompa ON | Moisture: {soil_moisture}% (< {self.threshold_on}%)"
            
        elif soil_moisture > self.threshold_off:
            # Tanah BASAH → Pompa OFF
            pump_should_be_on = False
            log_msg = f"🌱 WET: Pompa OFF | Moisture: {soil_moisture}% (> {self.threshold_off}%)"
        
        else:
            # Tanah NORMAL (antara threshold_on dan threshold_off)
            # Pertahankan kondisi sebelumnya (hysteresis)
            pump_should_be_on = self.pump_was_on
            status = "ON" if pump_should_be_on else "OFF"
            log_msg = f"⏸️ NORMAL: Pompa {status} | Moisture: {soil_moisture}% (hysteresis)"
        
        # ===== EKSEKUSI POMPA =====
        if pump_should_be_on:
            pump_relay.on()
            self.pump_on_time += self.check_interval
            action = "💨 ON "
        else:
            pump_relay.off()
            self.pump_on_time = 0
            action = "🔌 OFF"
        
        self.pump_was_on = pump_should_be_on
        
        # ===== PROTEKSI 3: Watchdog Timer =====
        if self.pump_on_time > self.max_pump_duration:
            print(f"[MODE_AI] ⚠️ WATCHDOG TRIGGERED: Pompa ON > {self.max_pump_duration}s, force OFF!")
            pump_relay.off()
            self.pump_on_time = 0
            self.pump_was_on = False
            return True
        
        # ===== LOG OUTPUT =====
        print(f"[MODE_AI] {action} {log_msg} [on_time: {self.pump_on_time}s]")
        
        return True
    
    def stop(self, pump_relay):
        """
        Graceful shutdown saat exit Mode AI
        Pastikan pompa OFF dan state clean
        
        Args:
            pump_relay: Relay object
        """
        print("[MODE_AI] 🛑 Stopping Mode AI - pompa OFF")
        pump_relay.off()
        self.pump_on_time = 0
        self.pump_was_on = False
        self.is_active = False
    
    def get_status(self):
        """Get current status untuk monitoring/logging"""
        return {
            'active': self.is_active,
            'pump_on_time': self.pump_on_time,
            'threshold_on': self.threshold_on,
            'threshold_off': self.threshold_off,
            'max_pump_duration': self.max_pump_duration
        }
    
    def update_config(self, sensor_min=None, sensor_max=None, 
                     threshold_on=None, threshold_off=None):
        """
        Update configuration (jika diperlukan recalibration)
        
        Args:
            sensor_min: New sensor_min value
            sensor_max: New sensor_max value
            threshold_on: New threshold_on value
            threshold_off: New threshold_off value
        """
        if sensor_min is not None:
            self.sensor_min = sensor_min
        if sensor_max is not None:
            self.sensor_max = sensor_max
        if threshold_on is not None:
            self.threshold_on = threshold_on
        if threshold_off is not None:
            self.threshold_off = threshold_off
        
        print(f"[MODE_AI] ✅ Config updated - ON:{self.threshold_on}%, OFF:{self.threshold_off}%")


# ===== HELPER FUNCTION =====
def create_mode_ai_from_device(device_data):
    """
    Factory function untuk create ModeAI instance dari device data
    
    Args:
        device_data: Dict dengan keys {sensor_min, sensor_max, threshold_on, threshold_off}
        
    Returns:
        ModeAI instance
    """
    return ModeAI(
        sensor_min=device_data.get('sensor_min', 4095),
        sensor_max=device_data.get('sensor_max', 1500),
        threshold_on=device_data.get('threshold_on', 35),
        threshold_off=device_data.get('threshold_off', 65)
    )


# ===== EXAMPLE USAGE =====
if __name__ == "__main__":
    """
    Contoh penggunaan Mode AI class
    """
    
    # Mock relay class untuk testing
    class MockRelay:
        def __init__(self):
            self.is_on = False
        
        def on(self):
            self.is_on = True
            print("  [RELAY] ON")
        
        def off(self):
            self.is_on = False
            print("  [RELAY] OFF")
    
    # Initialize
    relay = MockRelay()
    mode_ai = ModeAI(sensor_min=4095, sensor_max=1500, 
                     threshold_on=35, threshold_off=65)
    mode_ai.is_active = True
    
    # Simulate sensor readings
    test_readings = [
        (3500, "Kering - Should ON"),        # 30%
        (3200, "Kering - Should ON"),        # 35% threshold
        (2500, "Normal - Should stay ON"),   # 50%
        (1700, "Basah - Should OFF"),        # 65% threshold
        (1500, "Basah - Should OFF"),        # 100%
        (3500, "Back kering"),               # 30%
    ]
    
    print("\n========== Mode AI Test ==========")
    for adc_val, description in test_readings:
        print(f"\n[TEST] ADC: {adc_val} ({description})")
        mode_ai.run(adc_val, relay, time.time())
    
    print("\n========== Test Complete ==========")
    mode_ai.stop(relay)
