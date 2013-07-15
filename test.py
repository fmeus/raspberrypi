#!/usr/bin/env python

import urllib2 as url

response = url.urlopen( 'http://www.google.com/' )
html = response.read()

print( html )
