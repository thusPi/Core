#!/usr/bin/python3

import sys
import os
import serial
import time
import subprocess

abspath = os.path.abspath(__file__)
dname = os.path.dirname(abspath)
os.chdir(dname)

if(len(sys.argv) > 4):
    port    = '/dev/' + sys.argv[1]
    baud    = sys.argv[2]
    msg_on  = sys.argv[3]
    msg_off = sys.argv[4]

    ser = serial.Serial(port, baud)
    try:
        ser.close()
        ser.open()

        end_time = time.time() + 65

        while time.time() < end_time:
            if (ser.inWaiting() > 0):
                line = ser.readline().decode('utf-8').strip()

                ser.flushInput()

                if(msg_on in line):
                    subprocess.Popen(['php', 'receive.php', 'on', msg_on, sys.argv[1], '&'])
                elif(msg_off in line):
                    subprocess.Popen(['php', 'receive.php', 'off', msg_off, sys.argv[1], '&'])

            time.sleep(0.05) 

    except Exception as err:
        print(err)
        pass

    finally:
        ser.close()

else:
    print('Usage: python3 _tick.py <port> <baud> <msg_on> <msg_off>')