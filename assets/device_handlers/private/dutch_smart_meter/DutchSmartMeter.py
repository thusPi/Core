import serial
import sys
import time
import datetime
import json

class DutchSmartMeter(object):
    def __init__(self, serial_port, dsmr_version):
        self.serial_port  = serial_port
        self.dsmr_version = dsmr_version
        self.msg          = []

    def request_data(self):
        # Open serial port depending on DSMR / ESMR version
        try:
            if(self.dsmr_version == 'DSMR2.0'):
                ser = serial.Serial(port=self.serial_port, timeout=20, xonxoff=0, rtscts=0,
                    baudrate = 9600,
                    bytesize=serial.SEVENBITS,
                    parity=serial.PARITY_EVEN,
                    stopbits=serial.STOPBITS_ONE
                )
            elif(self.dsmr_version == 'DSMR4.2'):
                ser = serial.Serial(port=self.serial_port, timeout=20, xonxoff=0, rtscts=0,
                    baudrate = 115200,
                    bytesize=serial.SEVENBITS,
                    parity=serial.PARITY_EVEN,
                    stopbits=serial.STOPBITS_ONE
                )
            elif(self.dsmr_version == 'ESMR5.0'):
                ser = serial.Serial(port=self.serial_port, timeout=20, xonxoff=0, rtscts=0,
                    baudrate = 115200,
                    bytesize=serial.EIGHTBITS,
                    parity=serial.PARITY_NONE,
                    stopbits=serial.STOPBITS_ONE
                )
            else:
                print(json.dumps({'success': False, 'message': f'Invalid DSMR version: {self.dsmr_version}. Supported are DSMR2.0, DSMR4.2 and ESMR5.0.'}))
                return False
        except Exception as e:
            e_str = str(e)
            print(json.dumps({'success': False, 'message': f'{e_str}.'}))
            return False

        # Flush serial port
        ser.flushOutput()
        ser.flushInput()
        ser.flush()

        timeout = time.time() + 65

        # Read for one minute max
        while time.time() < timeout:
            if(ser.in_waiting > 0):
                line = ser.readline().decode('utf-8').strip()

                # Save line
                self.msg.append(line)

                if(len(line) == 1 and line[0] == '!'):
                    # Stop reading if footer was sent
                    break
            time.sleep(0.1)

        if(not self.validate_data()):
            return False

        return True

    def output_json(self):

        if(not self.request_data()):
            return False
        data = {'success': True, 'electricity': {'consumed': {}, 'sent_back': {}}, 'gas': {}}
        
        for line in self.msg:
            if line[0:9] == '1-0:1.8.1':
                # Electricity delivered to client (normal tariff) in kWh
                data['electricity']['consumed']['normal_tariff'] = float(self.metering_from_line(line))
            elif line[0:9] == '1-0:1.8.2':
                # Electricity delivered to client (normal tariff) in kWh
                data['electricity']['consumed']['low_tariff'] = float(self.metering_from_line(line))
            elif line[0:9] == '1-0:2.8.1':
                # Electricity delivered by client (normal tariff) in kWh
                data['electricity']['sent_back']['normal_tariff'] = float(self.metering_from_line(line))
            elif line[0:9] == '1-0:2.8.2':
                # Electricity delivered by client (normal tariff) in kWh
                data['electricity']['sent_back']['low_tariff'] = float(self.metering_from_line(line))
            elif line[0:11] == '0-0:96.14.0':
                # Tariff used at moment of metering
                tariff_currently = int(float(self.metering_from_line(line, end=')')))
                if(tariff_currently == 1):
                    data['electricity']['tariff_currently'] = 'low_tariff'
                else:
                    data['electricity']['tariff_currently'] = 'normal_tariff'
            elif line[0:9] == '1-0:1.7.0':
                # Actual electricity power in 1 Watt resolution
                data['electricity']['consumed']['actual'] = float(self.metering_from_line(line))
            elif line[0:9] == '1-0:2.7.0':
                # Actual electricity power in 1 Watt resolution
                data['electricity']['sent_back']['actual'] = float(self.metering_from_line(line))
            elif line[0:10] == '0-1:24.3.0':
                # Time of latest gas metering. 22013011 for 01/30/2022 at 11AM
                measured_at = str(self.metering_from_line(line, end=')'))[0:8]
                measured_at = int(datetime.datetime.strptime(measured_at,'%y%m%d%H').timestamp())
                data['gas']['measured_at'] = measured_at
            elif line[0:1] == '(':
                # Gas metering
                data['gas']['consumed'] = float(self.metering_from_line(line, end=')'))

        print(json.dumps(data));
    
    def metering_from_line(self, line, start = '(', end = '*'):
        metering = (line.split(start))[1].split(end)[0]

        return metering

    def validate_data(self):
        # Check if header is valid
        if(self.msg[0][0] != '/'):
            return False

        if(self.msg[0][5] != '\\'):
            return False

        # Check if footer is valid
        if(self.msg[-1][0] != '!'):
            return False

        return True