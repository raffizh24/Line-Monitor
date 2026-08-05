import RPi.GPIO as GPIO

import configparser

import csv

import datetime

import time

import logging



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

logging.basicConfig(filename=log_filename, level=logging.INFO,

                    format='%(asctime)s - %(message)s')



logging.info("Program started. GPIO 17 Power ON (3.3V).")



try:

    while True:

        current_state = GPIO.input(sensor_pin)

        

        # Logika Penghitung (Count) berdasarkan masukan di GPIO 27

        if previous_state == GPIO.LOW and current_state == GPIO.HIGH: # Sinyal diterima pada Pin 27

            count = 1

            d = datetime.datetime.now().strftime("%Y_%m_%d_%H%M%S")

            dcsv = datetime.datetime.now().strftime("%Y/%m/%d %H:%M:%S")

            previous_state = current_state

            print(f"Count: {count}")



        if previous_state == GPIO.HIGH and current_state == GPIO.HIGH: # Sinyal masih aktif di Pin 27

            count += 1

            previous_state = current_state

            print(f"Count: {count}")



        if count <= 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW: # Sinyal terputus (terlalu singkat)

            count = 0

            previous_state = current_state

            logging.info("Ignored short pulse (count <= 3)")

            print("Not Output")

        

        if count > 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW: # Sinyal terputus setelah > 3 detik

            config_ini = configparser.ConfigParser()

            config_ini.read("/home/pi/Desktop/count/settingfile.ini", encoding="utf-8")

            LineName = config_ini.get("Target", "Line", fallback="Unknown")

            csv_filename = f"{d}_{LineName}.csv"

            

            with open(f"/home/pi/Desktop/count/csv/{csv_filename}", "w") as f:

                writer = csv.writer(f)

                writer.writerow([dcsv, LineName])

                

            logging.info(f"Data saved. Final count: {count}")

            previous_state = GPIO.LOW

            count = 0

            print("CSV Saved")

        

        time.sleep(1)



except KeyboardInterrupt:

    logging.info("Program stopped by user.")

    print("Stopped by user")



except Exception as e:

    logging.error(f"Error occurred: {str(e)}")



finally:

    GPIO.cleanup()

    logging.info("GPIO cleanup done.")