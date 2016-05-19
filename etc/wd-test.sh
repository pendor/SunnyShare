#!/bin/bash

ifconfig wlan1 || exit 1

if [ ! -b /dev/sda ] ; then
	exit 1
fi
