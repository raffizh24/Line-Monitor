import RPi.GPIO as GPIO
import datetime
import time
import logging
import requests

# Konfigurasi Server Ubuntu LAMPP & Identitas Mesin
SERVER_URL = "http://192.168.180.181/Line-Monitor/api_receive.php"
MACHINE_ID = "B1"

# Set up GPIO
GPIO.setmode(GPIO.BCM)

# Pin Configuration
power_pin = 17   # GPIO 17 sebagai Pengeluar Tegangan 3.3V (Output)
sensor_pin = 27  # GPIO 27 sebagai Penerima Sinyal (Input)

GPIO.setup(power_pin, GPIO.OUT)
GPIO.setup(sensor_pin, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)

# Aktifkan GPIO 17 agar terus mengeluarkan 3.3V
GPIO.output(power_pin, GPIO.HIGH)

# Initialize variables
previous_state = GPIO.LOW
count = 0

# Setup logging
log_filename = "/home/pi/Desktop/count/logfile.log"
logging.basicConfig(filename=log_filename, level=logging.INFO, format='%(asctime)s - %(message)s')

try:
    while True:
        current_state = GPIO.input(sensor_pin)
        
        # Logika Penghitung berdasarkan masukan di GPIO 27
        if previous_state == GPIO.LOW and current_state == GPIO.HIGH: # Sinyal baru masuk
            count = 1
            previous_state = current_state

        if previous_state == GPIO.HIGH and current_state == GPIO.HIGH: # Sinyal masih aktif
            count += 1
            previous_state = current_state

        if count <= 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW: # Sinyal terputus terlalu cepat
            count = 0
            previous_state = current_state
        
        if count > 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW: # Sinyal terputus setelah > 3 detik (Valid)
            
            # Kirim Data HTTP POST ke Server Ubuntu (LAMPP)
            try:
                payload = {
                    "machine_id": MACHINE_ID,
                }
                response = requests.post(SERVER_URL, json=payload, timeout=3)
                logging.info(f"Data {MACHINE_ID} terkirim. Status Code: {response.status_code}")
            except Exception as e:
                logging.error(f"Gagal mengirim data {MACHINE_ID} ke server: {str(e)}")
            
            previous_state = GPIO.LOW
            count = 0
        
        time.sleep(1)

except KeyboardInterrupt:
    logging.info("Program dihentikan oleh user.")

except Exception as e:
    logging.error(f"Error terjadi: {str(e)}")

finally:
    GPIO.cleanup()
    logging.info("GPIO cleanup selesai.")