import RPi.GPIO as GPIO
import configparser
import csv
import datetime
import time
import logging

# Set up GPIO
GPIO.setmode(GPIO.BCM)
input_pin = 22
GPIO.setup(input_pin, GPIO.IN, pull_up_down=GPIO.PUD_DOWN)

# Initialize variables
previous_state = GPIO.LOW
count = 0

# Setup logging
log_filename = "/home/pi/Desktop/count/logfile.log"
logging.basicConfig(filename=log_filename, level=logging.INFO,
                    format='%(asctime)s - %(message)s')

logging.info("Program started.")

try:
    while True:
        current_state = GPIO.input(input_pin)
        
        if previous_state == GPIO.LOW and current_state == GPIO.HIGH: #Relay OFF -> ON
            count = 1
            d = datetime.datetime.now().strftime("%Y_%m_%d_%H%M%S")
            dcsv = datetime.datetime.now().strftime("%Y/%m/%d %H:%M:%S")
            previous_state = current_state
            print(count)

        if previous_state == GPIO.HIGH and current_state == GPIO.HIGH: #Relay ON -> ON
            count += 1
            previous_state = current_state
            print(count)

        if count <= 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW: #Relay ON -> OFF
            count = 0
            previous_state = current_state
            logging.info(count)
            print("Not Output")
        
        if count > 3 and previous_state == GPIO.HIGH and current_state == GPIO.LOW:
            config_ini = configparser.ConfigParser()
            config_ini.read("/home/pi/Desktop/count/settingfile.ini", encoding="utf-8")
            LineName = config_ini.get("Target", "Line", fallback="Unknown")
            csv_filename = f"{d}_{LineName}.csv"
            with open(f"/home/pi/Desktop/count/csv/{csv_filename}", "w") as f:
                writer = csv.writer(f)
                writer.writerow([dcsv, LineName])
            logging.info(count)
            previous_state = GPIO.LOW
            count = 0
        
        time.sleep(1)

except KeyboardInterrupt:
    logging.info("Program stopped by user.")
    print("Stopped by user")

except Exception as e:
    logging.error(f"Error occurred: {str(e)}")

finally:
    GPIO.cleanup()
    logging.info("GPIO cleanup done.")
