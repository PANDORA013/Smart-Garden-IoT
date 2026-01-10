# Testing & Validation Tools

This directory contains tools to test and validate your Smart Garden IoT setup.

## Available Tools

### 1. API Endpoint Test (`test-api.php`)

Tests the Laravel server API endpoint by simulating data from a Pico W device.

**Usage:**
```bash
php test-api.php
```

**What it does:**
- Sends a POST request to `http://127.0.0.1:8000/api/monitoring/insert`
- Simulates sensor data (temperature, soil moisture, etc.)
- Displays server response

**Expected Output:**
```
✅ Success!
HTTP Code: 201
Response Body: {...}
```

### 2. Configuration Validator (`validate-config.py`)

Validates your MicroPython configuration file before uploading to Pico W.

**Usage:**
```bash
python3 validate-config.py
```

**What it validates:**
- Checks if `micropython/config.py` exists
- Verifies all required variables are present
- Detects placeholder values that need to be changed
- Validates format of URLs and other fields

**Expected Output:**
```
✅ Configuration looks good!
```

### 3. Hardware Test Scripts

PowerShell scripts for testing hardware components:

- `test-hardware.ps1` - General hardware testing
- `test-new-sensor-detection.ps1` - Sensor detection testing

## Testing Workflow

### Before uploading to Pico W:

1. **Test Server:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   php test-api.php  # In another terminal
   ```

2. **Validate Configuration:**
   ```bash
   # After creating micropython/config.py
   python3 validate-config.py
   ```

3. **Upload to Pico W:**
   - If validation passes, upload config files
   - Monitor serial output for connection status

### After uploading to Pico W:

1. **Check Serial Monitor:**
   - Should see: `✅ WiFi Connected!`
   - Should see: `✅ Server Response Code: 201`

2. **Verify Dashboard:**
   - Open: http://localhost:8000
   - Device should appear in list
   - Data should update every 10 seconds

## Troubleshooting

### test-api.php fails:
- Ensure Laravel server is running
- Check server URL is correct
- Verify database is configured

### validate-config.py shows errors:
- Follow the error messages to fix config.py
- Common issues:
  - Empty values
  - Placeholder values not changed
  - Missing variables

### Pico W won't connect:
- Run validation script first
- Check WiFi credentials
- Ensure 2.4GHz network
- Verify server IP is reachable

## Documentation Links

- **CONFIGURATION_GUIDE.md** - Complete setup guide
- **PICO_CONFIGURATION_CHECKLIST.md** - Step-by-step checklist
- **README.md** - Project overview

## Adding Your Own Tests

To add custom tests:

1. Create test script in project root
2. Name with `test-` prefix
3. Document usage in this file
4. Ensure it's compatible with CI/CD if used

## CI/CD Integration

Currently, testing is manual. For automated testing:

- Laravel tests: `php artisan test`
- PHPUnit tests in `tests/` directory
- Add GitHub Actions workflows in `.github/workflows/`

---

**Last Updated:** January 10, 2026  
**Project:** Smart Garden IoT System
