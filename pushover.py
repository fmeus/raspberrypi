import httplib
import urllib
import os

APP_TOKEN = os.environ['PUSHOVER_APP_TOKEN']
USER_KEY = os.environ['PUSHOVER_USER_KEY']

def send_notification(msg):
	conn = httplib.HTTPSConnection("api.pushover.net:443")
	conn.request("POST", "/1/messages.json",
	  urllib.urlencode({
	    "token": APP_TOKEN,
	    "user": USER_KEY,
	    "title": "RPi Environmental Data",
	    "message": msg,
	    "url": "http://littlegemsoftware.com:314/chart-sensor.html",
	    "url_title": "RPi Sensor Data"
	  }), { "Content-type": "application/x-www-form-urlencoded" })
	conn.getresponse()