#!/usr/bin/python3

import OmnikExportNew
import sys
import json

if __name__ == "__main__":
	if(len(sys.argv) > 3):
		OmnikExportNew.OmnikExport().output_json(sys.argv[1], sys.argv[2], sys.argv[3])
	else:
		json.dumps({'success': False})
		exit();
