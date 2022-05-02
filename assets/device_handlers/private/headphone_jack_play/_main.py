#!/usr/bin/python3

import subprocess
import json
import base64
import sys
import time
import os

if(len(sys.argv) <= 3):
	print(json.dumps({'success': False, 'data': 'Invalid amount of arguments given.'}))
	exit()

tunnel_file = sys.argv[1]
value = sys.argv[2]
options = json.loads(base64.b64decode(sys.argv[3]))

if(not 'audio_src' in options):
	print(json.dumps({'success': False, 'data': 'Option audio_src missing.'}))
	exit()

# Stop all running mplayer processes
subprocess.call(['killall',  'mplayer'], stdout=subprocess.DEVNULL, stderr=subprocess.STDOUT)

# Start new mplayer process
print(json.dumps({'success' : 'true'}))
subprocess.Popen(['mplayer', '-loop', '0', '-slave', '-really-quiet', options['audio_src'], '>', '/dev/null', '2>&1', '&'])