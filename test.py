#!/user/bin/env python

import os
import glob
import time
import subprocess

def testProc( PiPin ):
    output = subprocess.check_output(["./Adafruit_DHT", "2302", str(PiPin)])
    print output
    return

testProc(22)

# print subprocess.check_output(["./Adafruit_DHT", "2302", "22"])
