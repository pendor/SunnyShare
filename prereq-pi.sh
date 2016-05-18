#!/bin/bash

if [ -z $1 ] ; then
  echo "Usage: $0 [user@]<ssh_host>"
  exit 1
fi

# Ubuntu
# PKGS="joe lighttpd php-cli php-cgi ifplugd dkms git"

# Raspbian
PKGS="joe lighttpd firmware-realtek dkms git raspberrypi-kernel-headers php-file php-file-iterator php-http-upload php5 php5-cgi php5-cli php5-curl php5-fpm php5-json php5-memcached lighttpd hostapd forked-daapd dnsmasq minidlna wpasupplicant memcached php-compat php5-mcrypt i2c-tools"

ssh "$1" 'apt-get update && apt-get install $PKGS'
