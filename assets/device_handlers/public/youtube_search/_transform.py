#!/usr/bin/python3

from subprocess import Popen, PIPE, STDOUT
import sys
import math
import json
import os

if len(sys.argv) <= 1:
	print(json.dumps({'success': False, 'data': 'Insufficient amount of arguments given.'}))
	exit()

try:
	vid_id  = sys.argv[1]
	vid_url = f'https://youtube.com/watch?v={vid_id}'

	download_cmd     = ['youtube-dl', '-g', vid_url]
	ytp              = Popen(download_cmd, stdout=PIPE, stderr=PIPE)
	(yt_res, yt_err) = ytp.communicate()

	if yt_res:
		yt_res        = yt_res.decode('utf-8')
		video_url     = yt_res.split('\n')[0]
		audio_url     = yt_res.split('\n')[-2]
		thumbnail_url = "https://i.ytimg.com/vi/{}/hq720.jpg".format(vid_id)

		print(json.dumps({'success': True, 'data': {'audio_src': audio_url, 'thumbnail_src': thumbnail_url, 'video_src': video_url}}))
		exit()
	else:
		print(json.dumps({'success': False, 'data': f'An error occured: {yt_err}.'}))
		exit()
except Exception as e:
	print(json.dumps({'success': False, 'data': e}))
	exit()
