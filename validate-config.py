#!/usr/bin/env python3
"""
Configuration Validator for Smart Garden IoT
Validates config.py for MicroPython implementation
"""

import sys
import os

def validate_config():
    """Validate the config.py file"""
    
    print("=" * 60)
    print("🔍 Smart Garden IoT - Configuration Validator")
    print("=" * 60)
    
    # Check if config.py exists
    config_path = "config.py"
    if not os.path.exists(config_path):
        print("❌ ERROR: config.py not found!")
        print("   Please copy config.example.py to config.py")
        return False
    
    print("✅ config.py file found")
    
    # Read config file
    try:
        with open(config_path, 'r') as f:
            content = f.read()
    except Exception as e:
        print(f"❌ ERROR: Could not read config.py: {e}")
        return False
    
    errors = []
    warnings = []
    
    # Check for required variables
    required_vars = {
        'WIFI_SSID': 'WiFi network name',
        'WIFI_PASSWORD': 'WiFi password',
        'SERVER_URL': 'Server URL',
        'DEVICE_ID': 'Device identifier'
    }
    
    for var, description in required_vars.items():
        if var not in content:
            errors.append(f"Missing required variable: {var} ({description})")
        elif f'{var} = "YOUR_' in content or f'{var} = \'YOUR_' in content:
            warnings.append(f"{var} still contains placeholder value")
    
    # Check WiFi SSID
    if 'WIFI_SSID' in content:
        if 'WIFI_SSID = ""' in content or "WIFI_SSID = ''" in content:
            errors.append("WIFI_SSID is empty")
    
    # Check WiFi PASSWORD
    if 'WIFI_PASSWORD' in content:
        if 'WIFI_PASSWORD = ""' in content or "WIFI_PASSWORD = ''" in content:
            errors.append("WIFI_PASSWORD is empty")
    
    # Check SERVER_URL format
    if 'SERVER_URL' in content:
        if 'SERVER_URL = ""' in content or "SERVER_URL = ''" in content:
            errors.append("SERVER_URL is empty")
        elif '192.168.1.100' in content:
            warnings.append("SERVER_URL contains example IP (192.168.1.100) - update with your actual IP")
        elif not ('http://' in content or 'https://' in content):
            warnings.append("SERVER_URL should start with http:// or https://")
    
    # Check DEVICE_ID
    if 'DEVICE_ID' in content:
        if 'DEVICE_ID = ""' in content or "DEVICE_ID = ''" in content:
            errors.append("DEVICE_ID is empty")
    
    # Print results
    print("\n" + "=" * 60)
    print("📊 Validation Results:")
    print("=" * 60)
    
    if errors:
        print("\n❌ ERRORS found:")
        for error in errors:
            print(f"   • {error}")
    
    if warnings:
        print("\n⚠️  WARNINGS:")
        for warning in warnings:
            print(f"   • {warning}")
    
    if not errors and not warnings:
        print("\n✅ Configuration looks good!")
        print("   Your config.py appears to be properly configured.")
    elif not errors:
        print("\n✅ No critical errors found.")
        print("   Please review warnings above.")
    else:
        print("\n❌ Configuration has errors that must be fixed.")
        return False
    
    print("\n" + "=" * 60)
    print("📝 Next Steps:")
    print("=" * 60)
    if errors or warnings:
        print("1. Edit config.py with your actual values")
        print("2. Run this validator again to verify")
        print("3. Upload config.py and main.py to Pico W")
    else:
        print("1. Upload config.py to Pico W")
        print("2. Upload main.py to Pico W")
        print("3. Run main.py and check Serial Monitor")
    
    return not errors

if __name__ == "__main__":
    # Change to micropython directory if not already there
    script_dir = os.path.dirname(os.path.abspath(__file__))
    micropython_dir = os.path.join(script_dir, "micropython")
    
    if os.path.exists(micropython_dir):
        os.chdir(micropython_dir)
        print(f"📁 Changed to directory: {micropython_dir}\n")
    
    success = validate_config()
    sys.exit(0 if success else 1)
