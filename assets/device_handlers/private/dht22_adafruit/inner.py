#!/usr/bin/python3
import time
import sys
import json
import Adafruit_DHT

DHT_SENSOR = Adafruit_DHT.DHT22
if(len(sys.argv) > 1):
	DHT_PIN = sys.argv[1]

	while True:
		humidity, temperature_celsius = Adafruit_DHT.read(DHT_SENSOR, DHT_PIN)

		print(humidity, temperature_celsius);
		
		if humidity is not None and temperature_celsius is not None:
			temperature_kelvin = temperature_celsius + 273.15
			print(json.dumps({'success': True, 'temperature': temperature_kelvin, 'humidity': humidity}))
			quit()

		print('Retrying...');

		time.sleep(2)
else:
	print(json.dumps({'success': False, 'data': 'Option gpio_pin not given.'}))
	quit()