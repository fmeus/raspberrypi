#!/usr/bin/env python

import httplib
import urllib
import os

APP_TOKEN = os.environ['PUSHOVER_APP_TOKEN']
USER_KEY = os.environ['PUSHOVER_USER_KEY']

def send_notification(title,msg,url,url_title):
	conn = httplib.HTTPSConnection("api.pushover.net:443")
	conn.request("POST", "/1/messages.json",
	  urllib.urlencode({
	    "token": APP_TOKEN,
	    "user": USER_KEY,
	    "title": title,
	    "message": msg,
	    "url": url,
	    "url_title": url_title
	  }), { "Content-type": "application/x-www-form-urlencoded" })
	conn.getresponse()
