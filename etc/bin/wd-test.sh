#!/bin/bash

if [ `cut -d. -f1 < /proc/uptime` -lt 600 ] ; then 
  echo "System not up long enough to watchdog yet."
  exit 0
fi

if [ ! -b /dev/sda ] ; then
	exit 1
fi
