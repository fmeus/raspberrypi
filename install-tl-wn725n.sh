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