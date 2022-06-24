#!/usr/bin/env python3

import sys
import os
import serial
import json
import time

# Exit if insufficient amount of arguments are given
if(len(sys.argv) <= 6):
	print(json.dumps({'success': False, 'data': 'Insufficient amount of arguments.'}))
	quit()

state        = 1 if sys.argv[1] == 'on' else 0
unit         = str(sys.argv[2]).zfill(4)[:4]
address      = str(sys.argv[3]).zfill(8)[:8]
pulse_length = str(sys.argv[4]).zfill(4)[:4]
port         = '/dev/'+sys.argv[5]
baud         = sys.argv[6]

# Digits 0-4:   Command
# Digits 5-12:  Address
# Digits 14-17: Unit
# Digit  19:    State
# Digits 21-24: Pulse length
cmd = f'kaku {address} {unit} {state} {pulse_length}'

try:
	ser = serial.Serial(port, baud, timeout=0.1)

	# Wait just a little
	time.sleep(0.1)

	# Serial port is being kept open by the _tick process.
	ser.write(bytes(cmd, encoding='utf-8'))

	# Wait for a response from the Arduino (5 seconds max.)
	timeout = time.time() + 5
	while time.time() < timeout:
		if(ser.inWaiting() > 0):
			print(json.dumps({'success': True}))
			quit()

	print(json.dumps({'success': False}))
	quit()
except Exception as err:
	print(json.dumps({'success': False, 'data': str(err)}))