#!/usr/bin/python3

import DutchSmartMeter
import sys

if __name__ == '__main__':
    if(len(sys.argv) > 2):
        serial_port  = '/dev/' + sys.argv[1]
        dsmr_version = sys.argv[2]
        DutchSmartMeter.DutchSmartMeter(serial_port, dsmr_version).output_json()
    else:
        print('Expected serial_port and dsmr_version in arguments.')