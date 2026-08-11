import RPi.GPIO as GPIO
import csv
import datetime
import time
import logging
import requests

# Konfigurasi Server Ubuntu LAMPP & Identitas Mesin
SERVER_URL = "http://192.168.180.181/Line-Monitor/api_receive.php"
MACHINE_ID = "A1"

# Set up GPIO
GPIO.setmode(GPIO.BCM)

# Pin Configuration (Menggunakan Input Relay pada GPIO 22)
input_pin = 22
GPIO.setup(input_pin, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)

# Initialize variables
previous_state = GPIO.LOW

# Setup logging
log_filename = "/home/pi/Desktop/count/logfile.log"
logging.basicConfig(filename=log_filename, level=logging.INFO, format='%(asctime)s - %(message)s')

try:
    while True:
        current_state = GPIO.input(input_pin)
        
        # Sinyal Relay terputus (Transisi HIGH -> LOW): Ada Data Masuk / Produksi Selesai
        if previous_state == GPIO.HIGH and current_state == GPIO.LOW:
            d = datetime.datetime.now().strftime("%Y_%m_%d_%H%M%S")
            dcsv = datetime.datetime.now().strftime("%Y/%m/%d %H:%M:%S")

            # 1. Simpan CSV Lokal di Raspberry Pi
            csv_filename = f"{d}_{MACHINE_ID}.csv"
            try:
                with open(f"/home/pi/Desktop/count/csv/{csv_filename}", "w") as f:
                    writer = csv.writer(f)
                    writer.writerow([dcsv, MACHINE_ID])
            except Exception as e:
                logging.error(f"Gagal simpan CSV: {str(e)}")

            # 2. Kirim Data Sinyal Masuk via HTTP POST ke Server (count = 1)
            try:
                payload = {
                    "machine_id": MACHINE_ID,
                }
                response = requests.post(SERVER_URL, json=payload, timeout=3)
            except Exception as e:
                logging.error(f"Gagal mengirim data {MACHINE_ID} ke server: {str(e)}")

            time.sleep(0.2) # Debounce delay

        # Simpan status pin saat ini untuk perbandingan loop berikutnya
        previous_state = current_state
        time.sleep(0.05) # Sleep singkat agar CPU Raspberry Pi tidak 100%

except KeyboardInterrupt:
    logging.info("Program stopped by user.")
    print("\nStopped by user")

except Exception as e:
    logging.error(f"Error occurred: {str(e)}")

finally:
    GPIO.cleanup()
    logging.info("GPIO cleanup done.")