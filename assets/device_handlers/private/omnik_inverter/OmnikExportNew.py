#!/usr/bin/python3
"""OmnikExport program.

Get data from an omniksol inverter with 602xxxxx - 606xxxx ans save the data in
a database or push to pvoutput.org.
"""
from __future__ import division
import socket  # Needed for talking to inverter
import sys
import logging
import logging.config
import configparser
import os
import InverterMsg  # Import the Msg handler
import json
import codecs


class OmnikExport(object):
	"""
	Get data from Omniksol inverter and store the data in a configured output
	format/location.

	"""

	def run(self, ip, port, wifi_serial):
		# Connect to inverter

		for res in socket.getaddrinfo(ip, port, socket.AF_INET, socket.SOCK_STREAM):
			family, socktype, proto, canonname, sockadress = res
			try:
				inverter_socket = socket.socket(family, socktype, proto)
				inverter_socket.settimeout(10)
				inverter_socket.connect(sockadress)
			except socket.error as msg:
				json_output = {'success': False}
				json_dumps(json_output)
				sys.exit(1)

		inverter_socket.sendall(OmnikExport.generate_string(int(wifi_serial)))
		data = inverter_socket.recv(1024)
		inverter_socket.close()

		msg = InverterMsg.InverterMsg(data)
		return msg

	def output_json(self, ip, port, wifi_serial):
		msg = OmnikExport().run(ip, port, wifi_serial)

		json_output = {
			'success': True,
			'id': codecs.decode(msg.id),
			'generic': {
				'yield_total': msg.e_total,
				'yield_today': msg.e_today,
				'hours_total': msg.h_total,
				'temperature': msg.temperature
			},
			'pv': {
				'1': {
					'voltage': round(msg.v_pv(1), 3),
					'current': round(msg.i_pv(1), 3),
					'power':   round(msg.v_pv(1)*msg.i_pv(1)/1000, 3)
				},
				'2': {
					'voltage': round(msg.v_pv(2), 3),
					'current': round(msg.i_pv(2), 3),
					'power':   round(msg.v_pv(2)*msg.i_pv(2)/1000, 3)
				},
				'3': {
					'voltage': round(msg.v_pv(3), 3),
					'current': round(msg.i_pv(3), 3),
					'power':   round(msg.v_pv(3)*msg.i_pv(3)/1000, 3)
				}
			},
			'total': {
				'voltage': round(msg.v_pv(1) + msg.v_pv(2) + msg.v_pv(3), 3),
				'current': round(msg.i_pv(1) + msg.i_pv(2) + msg.i_pv(3), 3),
				'power':   round(((msg.v_pv(1) * msg.i_pv(1)) + (msg.v_pv(2) * msg.i_pv(2)) + (msg.v_pv(3) * msg.i_pv(3)))/1000, 3)
			}
		}

		print(json.dumps(json_output));

	@staticmethod
	def __expand_path(path):
		"""
		Expand relative path to absolute path.

		Args:
			path: file path

		Returns: absolute path to file

		"""
		if os.path.isabs(path):
			return path
		else:
			return os.path.dirname(os.path.abspath(__file__)) + "/" + path

	@staticmethod
	def generate_string(serial_no):
		"""Create request string for inverter.

		The request string is build from several parts. The first part is a
		fixed 4 char string; the second part is the reversed hex notation of
		the s/n twice; then again a fixed string of two chars; a checksum of
		the double s/n with an offset; and finally a fixed ending char.

		Args:
			serial_no (int): Serial number of the inverter

		Returns:
			str: Information request string for inverter
		"""

		response = '\x68\x02\x40\x30'

		double_hex = hex(serial_no)[2:] * 2
		hex_list = [codecs.decode(double_hex[i:i + 2], 'hex').decode('latin-1') for i in
					reversed(range(0, len(double_hex), 2))]

		cs_count = 115 + sum([ord(c) for c in hex_list])
		checksum = codecs.decode(bytes.fromhex(hex(cs_count)[-2:]), 'latin-1')
		response += ''.join(hex_list) + '\x01\x00' + checksum + '\x16'
		response = codecs.encode(response, 'latin-1')
		return response