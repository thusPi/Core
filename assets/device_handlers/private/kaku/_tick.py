#!/usr/bin/python3

import sys
import os
import serial
import json
import time
import subprocess

abspath = os.path.abspath(__file__)
dname = os.path.dirname(abspath)
os.chdir(dname)

if(len(sys.argv) > 2):
    port    = '/dev/'+sys.argv[1]
    baud    = sys.argv[2]

    ser = serial.Serial(port, baud)
    try:

        ser.close()
        ser.open()

        end_time = time.time()+65;

        while time.time() < end_time:
            if (ser.inWaiting() > 0):
                line_raw = ser.readline().decode('utf-8').strip()
                try:
                    line = json.loads(line_raw)
                except:
                    continue

                print(line);

                ser.flushInput()
            
                if all (k in line for k in ('address', 'unit', 'state', 'pulselength')):
                    subprocess.Popen(['php', 'receive.php', str(line['address']), str(line['unit']), str(line['state']), str(line['pulselength']), '&'])            
            time.sleep(0.05) 

    except Exception as err:
        pass
else:
    print('Usage: python3 _tick.py <port> <baud>')