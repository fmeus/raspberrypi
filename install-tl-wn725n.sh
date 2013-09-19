#!/bin/bash
#
# Commands needed to install drivers for the TP-Link Wireless N USB Adapter (Model TL-WN725N, Ver 2.0)
#

wget https://dl.dropboxusercontent.com/u/80256631/8188eu-20130209.tar.gz
tar -zxvf 8188eu-20130209.tar.gz
sudo install -p -m 644 8188eu.ko /lib/modules/3.6.11+/kernel/drivers/net/wireless
sudo depmod -a
sudo modprobe 8188eu

#
# Contents of /etc/network/interfaces to auto load wlan0 and connect to WPA secured wireless network
#
# auto lo
# iface lo inet loopback
# allow-hotplug wlan0
# auto wlan0
# iface wlan0 inet dhcp
#         wpa-ssid "your-ssid"
#         wpa-psk "your-password"
# iface default inet dhcp


#
# http://www.raspberrypi.org/phpBB3/viewtopic.php?t=55779&p=422649
#
# by MrEngman » Mon Sep 16, 2013 5:38 pm
# wewa wrote:
# I've also tried this but it also produces an error.
#
# 	user@raspberrypi / $ sudo insmod /lib/modules/3.6.11+/kernel/drivers/net/wireless/8188eu.ko
# 	Error: could not insert module /lib/modules/3.6.11+/kernel/drivers/net/wireless/8188eu.ko: Invalid module format
#
# I have loaded an SD card with 2013-07-26-wheezy-raspbian and installed the same driver you used and it loads and the wifi works OK. Have you updated you image using apt-get update/upgrade or rpi-update? You shouldn't get that error unless your kernel revision has changed.
#
# I have three different driver revisions. Which one you should use depends on the revision of raspbian you have installed. This you can find using command uname -a
#
# After running sudo rpi-update my own RPi shows
#
# 	Linux raspberrypi 3.6.11+ #541 PREEMPT Sat Sep 7 19:46:21 BST 2013 armv6l GNU/Linux
#
# for 3.6.11+ #538 and #541 use 8188eu-20130830.tar.gz
# for 3.6.11+ #524, #528 or #532 use 8188eu-20130815.tar.gz
# for 3.6.11+ #371 up to #520 use 8188eu-20130209.tar.gz
#
# Download and install the driver using the commands
#
# 	wget https://dl.dropboxusercontent.com/u/80256631/8188eu-2013xxyy.tar.gz  <--set data code for driver version
# 	tar -zxvf 8188eu-2013xxyy.tar.gz                                          <--set data code for driver version
# 	sudo install -p -m 644 8188eu.ko /lib/modules/3.6.11+/kernel/drivers/net/wireless
# 	sudo insmod /lib/modules/3.6.11+/kernel/drivers/net/wireless/8188eu.ko
# 	sudo depmod -a