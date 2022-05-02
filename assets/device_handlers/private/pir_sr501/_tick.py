from gpiozero import MotionSensor
import sys
import time


if(len(sys.argv) > 1):
    try:
        pir = MotionSensor(sys.argv[1])

        end_time = time.time()+65;

        while time.time() < end_time:
            pir.wait_for_motion()
            print('MOTION!')
            pir.wait_for_no_motion()
            print('NO MOTION!')
    except KeyboardInterrupt:
        pass
else:
    print('Expected GPIO pin. Exiting.');