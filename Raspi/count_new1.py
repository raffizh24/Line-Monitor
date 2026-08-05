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
count = 0

# Setup logging
log_filename = "/home/pi/Desktop/count/logfile.log"
logging.basicConfig(filename=log_filename, level=logging.INFO,
                    format='%(asctime)s - %(message)s')

logging.info(f"Program started for Machine {MACHINE_ID} (Pin 22).")

try:
    while True:
        current_state = GPIO.input(input_pin)
        
        # Relay OFF -> ON
        if previous_state == GPIO.LOW and current_state == GPIO.HIGH:
            count = 1
            d = datetime.datetime.now().strftime("%Y_%m_%d_%H%M%S")
            dcsv = datetime.datetime.now().strftime("%Y/%m/%d %H:%M:%S")
            previous_state = current_state
            print(f"[{MACHINE_ID}] Count: {count}")

        # Relay ON -> ON
        if previous_state == GPIO.HIGH and current_state == GPIO.HIGH:
            count += 1
            previous_state = current_state
            print(f"[{MACHINE_ID}] Count: {count}")

        # Relay ON -> OFF (Sinyal terputus <= 3 detik / diabaikan)
        if count <= 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW:
            count = 0
            previous_state = current_state
            logging.info("Ignored short pulse (count <= 3)")
            print(f"[{MACHINE_ID}] Not Output")
        
        # Relay ON -> OFF (Sinyal terputus > 3 detik / Output Valid)
        if count > 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW:
            
            # 1. Simpan CSV Lokal di Raspberry Pi A1
            csv_filename = f"{d}_{MACHINE_ID}.csv"
            try:
                with open(f"/home/pi/Desktop/count/csv/{csv_filename}", "w") as f:
                    writer = csv.writer(f)
                    writer.writerow([dcsv, MACHINE_ID])
                print(f"[{MACHINE_ID}] CSV Saved: {csv_filename}")
            except Exception as e:
                logging.error(f"Gagal simpan CSV: {str(e)}")

            # 2. Kirim Data via HTTP POST ke Server Ubuntu (LAMPP)
            try:
                payload = {
                    "machine_id": MACHINE_ID,
                    "count": count
                }
                response = requests.post(SERVER_URL, json=payload, timeout=3)
                logging.info(f"Data {MACHINE_ID} dikirim ke server. Status Code: {response.status_code}")
                print(f"[{MACHINE_ID}] Data terkirim ke Server ({response.status_code})")
            except Exception as e:
                logging.error(f"Gagal mengirim data {MACHINE_ID} ke server: {str(e)}")
                print(f"[{MACHINE_ID}] Gagal koneksi ke server: {str(e)}")

            logging.info(f"Data saved. Final count: {count}")
            previous_state = GPIO.LOW
            count = 0
            print(f"[{MACHINE_ID}] Process Finished")
        
        time.sleep(1)

except KeyboardInterrupt:
    logging.info("Program stopped by user.")
    print("Stopped by user")

except Exception as e:
    logging.error(f"Error occurred: {str(e)}")

finally:
    GPIO.cleanup()
    logging.info("GPIO cleanup done.")