#!/usr/bin/env python3

#    _                          ____  _ 
#   | |__   ___  _ __ ___   ___|  _ \(_)
#   | '_ \ / _ \| '_ ` _ \ / _ \ |_) | |
#   | | | | (_) | | | | | |  __/  __/| |
#   |_| |_|\___/|_| |_| |_|\___|_|   |_|
#
#   Welcome in the installation file for thusPi. Feel free to look around before installation.
#

installation_folder = '/var/www/html/thusPi/'
                                    
import os
import time

parent_dirs = os.path.realpath(__file__).split('/')
del parent_dirs[-3:]
installation_folder = '/'.join(parent_dirs)

def printSuccess(msg = ''):
	print('\033[92mSUCCESS\033[0m:', msg)

def printFail(msg = ''):
	print('\033[91mERROR\033[0m:', msg)

def printInfo(msg = ''):
	print('\033[94mINFO\033[0m:', msg)

def printDialog(message):
	answers_agree = ["y", "yes"]

	while True:
		answer = input('\033[94mINFO\033[0m: ' + message + ' [Y/n]: ').lower()
		if answer in answers_agree:
			return True
		else:
			return False

def programExists(name):
	from shutil import which

	return which(name) is not None



# ---------------------------------------------------------
# Print the thusPi logo and message
# ---------------------------------------------------------

print('')
print('\033[94m')
print('                     _                          ____  _ ')
print('                    | |__   ___  _ __ ___   ___|  _ \\(_)')
print('                    | \'_ \\ / _ \\| \'_ ` _ \\ / _ \\ |_) | |')
print('                    | | | | (_) | | | | | |  __/  __/| |')
print('                    |_| |_|\\___/|_| |_| |_|\\___|_|   |_|')
print('\033[0m')
print('')

if(os.geteuid() != 0):
	printFail('The installer is not being run with root privileges. Exiting.')
	quit()

input('Welcome to the thusPi installer. Press Enter to begin the installation process.')
time.sleep(1)



# ---------------------------------------------------------
# Prepare for thusPi installation
# ---------------------------------------------------------

try:
	actions = {
		'dependencies': {
			'packages': ['python3', 'python3-pip', 'apache2', 'php7.4', 'php7.4-curl', 'bash'],
			'commands': ['sudo python3 -m pip install pyserial RPi.GPIO', 'sudo adduser www-data gpio', 'sudo adduser www-data dialout']
		},
		'YouTube': {
			'packages': ['youtube-dl']
		},
		'audio': {
			'packages': ['mplayer', 'alsa-utils'],
			'commands': ['sudo adduser www-data audio']
		},
		'Adafruit': {
			'packages': ['libgpiod2'],
		}
	}

	for key in actions:
		if(not 'packages' in actions[key]):
			actions[key]['packages'] = []

		if(not 'commands' in actions[key]):
			actions[key]['commands'] = []

		packages_count = len(actions[key]['packages'])
		commands_count = len(actions[key]['commands'])

		if(packages_count > 0 or commands_count > 0):
			print('\n')
			if(key == 'dependencies'):
				printInfo(f'The following \033[1m{packages_count}\033[0m packages and \033[1m{commands_count}\033[0m commands are required for thusPi to function correctly:')
			else:
				printInfo(f'The following \033[1m{packages_count}\033[0m packages and \033[1m{commands_count}\033[0m commands are required to add support for \033[1m{key}\033[0m:')

			print('')
			if(packages_count > 0):
				for package in actions[key]['packages']:
					print(f'        - {package}')
				print('')

			if(commands_count > 0):
				for command in actions[key]['commands']:
					print(f'        - {command}')
				print('')

			if(packages_count > 0 and commands_count > 0):
				dialog_msg = f'Do you want to install these \033[1m{packages_count}\033[0m packages and run these \033[1m{commands_count}\033[0m commands?'
			elif(packages_count > 0):
				dialog_msg = f'Do you want to install these \033[1m{packages_count}\033[0m packages?'
			elif(commands_count > 0):
				dialog_msg = f'Do you want to run these \033[1m{commands_count}\033[0m commands?'
			
			if(printDialog(dialog_msg)):
				packages_str = ' '.join(actions[key]['packages'])
				printInfo(f'Installing packages: {packages_str}')
				os.system(f'sudo apt -y install {packages_str}')
				for key in actions[key]['commands']:
					code = os.system(key)
					try:
						if(code == 0 or code == 200):
							printSuccess(f'Succesfully ran command \033[1m{key}\033[0m.')
						else:
							raise BaseException(f'returned {code}')
					except BaseException as err:
						printFail(f'Failed to run command \033[1m{key}\033[0m: {err}.')
			elif(key == 'dependencies'): # Throw error if dependencies are denied
				raise BaseException('installation of dependencies was denied')
			else:
				printInfo(f'Not installing packages for \033[1m{key}\033[0m.')

			time.sleep(1)
except BaseException as err:
	printFail(f'Failed to install packages: {err}. Exiting.')
	quit()



# ---------------------------------------------------------
# Allow apache2 to run command vcgencmd without sudo
# to measure temperature.
# ---------------------------------------------------------
print('\n')

try:
	if(printDialog('Do you want apache2 to be able to measure your Pi\'s temperature using the vcgencmd command?')):
		visudo_entries = [
			'www-data ALL=NOPASSWD:/usr/bin/vcgencmd',
		]

		visudo_content = os.popen('sudo cat /etc/sudoers').read()

		for visudo_entry in visudo_entries:
			if(not visudo_entry in visudo_content):
				printInfo(f'Adding line {visudo_entry} to visudo...')
				visudo_response = os.popen(f'echo \'{visudo_entry}\' | sudo EDITOR=\'tee -a\' visudo').read().rstrip('\n')
				if(visudo_response != visudo_entry):
					raise BaseException(visudo_response)
				printSuccess('Succesfully modified visudo.')
			else:
				printInfo(f'\033[1m{visudo_entry}\033[0m was already set in sudoers file, skipping...')
	else:
		raise BaseException('denied')
except BaseException as err:
	printFail(f'Failed: {err}.')

time.sleep(1)



# ---------------------------------------------------------
# Enable cURL extension for PHP.
# ---------------------------------------------------------
print('\n')

ini_location_response = os.popen('php -i | grep \'Loaded Configuration File\'').read().rstrip('\n')

cli_ini_path      = ini_location_response[ini_location_response.find('/'):] # Extract ini path from command output
apache2_ini_path  = cli_ini_path.replace('/cli/', '/apache2/')

# Write to Apache2 ini file
try:
	with open(apache2_ini_path) as file:
		ini_content = file.read()
		if(';extension=curl' in ini_content):
			if(printDialog('The cURL extension for PHP (apache2) is disabled. Do you want to enable it?')):
				ini_content = ini_content.replace(';extension=curl', 'extension=curl') # Remove semicolon to enable

				with open(apache2_ini_path, 'w') as file:
					file.write(ini_content)

				printSuccess('Succesfully enabled cURL extension for PHP (apache2).')
			else:
				raise baseException('denied. Some crucial features might not work.')
		else:
			printSuccess('cURL extension for PHP (apache2) was already enabled.')
except BaseException as err:
	printFail(f'Failed to enable cURL extension for PHP (apache2): {err}.')

time.sleep(1)



# ---------------------------------------------------------
# Finish apache
# ---------------------------------------------------------
if(printDialog('Apache needs to be restarted for the changes to take effect. Would you like do that right now?')):
	os.system('sudo service apache2 restart')
	printSuccess('Apache was restarted.')



# ---------------------------------------------------------
# Download thusPi files from Github
# ---------------------------------------------------------
print('\n')

respository_url = 'https://github.com/TjallingF/thusPi/'

try:
	if(not programExists('git')): # Ask user to install Git if it isn't installed yet
		if(printDialog('Git is mandatory to clone the thusPi respository. Do you want to install it right now?')):
			os.system('sudo -y install git')
		else:
			raise BaseException('installation of git deniedwas ')

	if(printDialog(f'Do you want to clone the thusPi respository from {respository_url} into {installation_folder}?')):
		clone_response = os.system(f'sudo git clone {respository_url} {installation_folder}')
		if(clone_response == 32768):
			raise BaseException(f'destination path {installation_folder} already exists and is not an empty directory')
		elif(len(os.listdir(installation_folder)) != 0):
			printSuccess(f'thusPi succesfully cloned into {installation_folder}')
		else:
			raise BaseException('something went wrong')
	else:
		raise BaseException('installation of respository was denied')
except BaseException as err:
	printFail(f'Cloning of thusPi respository: {err}. Exiting.')
	quit()

print('Congratulations with your copy of thusPi.')