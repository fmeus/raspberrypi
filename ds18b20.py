#!/usr/bin/env python

import os
import glob
import time
import RPi.GPIO as GPIO
import urllib2 as url
import subprocess
 
os.system( 'modprobe w1-gpio' )
os.system( 'modprobe w1-therm' )
 
base_dir = '/sys/bus/w1/devices/'
device_folder = glob.glob( base_dir + '28*' )[0]
device_file = device_folder + '/w1_slave'
log_host = 'http://www.littlegemsoftware.com/raspberrypi/'

GPIO.setmode( GPIO.BCM )
GPIO.setwarnings( False )

def logData( id, value):
    try:
        addr = log_host + 'sensor-data.php?action=log_data&id={0}&value={1}'.format( id, value )
        response = url.urlopen( addr )
        html = response.read()
        return
    except:
        return

def ledMode( PiPin, mode ):
    GPIO.setup( PiPin, GPIO.OUT )
    GPIO.output( PiPin, mode )
    return

def read_temp_raw():
    f = open(device_file, 'r')
    lines = f.readlines()
    f.close()
    return lines
 
def read_dht22( PiPin ):
    output = subprocess.check_output(["./Adafruit_DHT", "2302", str(PiPin)])
    matches = re.search("Temp =\s+([0-9.]+)", output)
    if ( matches ):
        logData( 2, float(matches.group(1)) )
    matches = re.search("Hum =\s+([0-9.]+)", output)
    if ( matches ):
        logData( 3, float(matches.group(1)) )
    print output
    return

def read_temp():
    lines = read_temp_raw()
    while lines[0].strip()[-3:] != 'YES':
        time.sleep(0.2)
        lines = read_temp_raw()
    equals_pos = lines[1].find('t=')
    if equals_pos != -1:
        temp_string = lines[1][equals_pos+2:]
        temp_c = float(temp_string) / 1000.0
        temp_f = temp_c * 9.0 / 5.0 + 32.0
        return temp_c, temp_f

ledMode( 14, GPIO.LOW )
ledMode( 15, GPIO.LOW )
ledMode( 18, GPIO.LOW )

while True:
    temp_c, temp_f = read_temp()
    ledMode( 14, GPIO.HIGH if temp_c < 27 else GPIO.LOW )
    ledMode( 15, GPIO.HIGH if temp_c >= 27 and temp_c < 29 else GPIO.LOW )
    ledMode( 18, GPIO.HIGH if temp_c >= 29 else GPIO.LOW )
    ts = time.strftime('%Y-%m-%d %H:%M:%S', time.localtime()) 
    print '{0} - Temperature = {1:.2f} C ({2:.2f} F)'.format( ts, temp_c, temp_f )
    logData( 1, temp_c )
    read_dht22(22)
    time.sleep(15)
