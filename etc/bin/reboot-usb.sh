#!/bin/bash
# Power cycle USB

PIN=132
GPIO=/sys/class/gpio

if grep -q /mnt/data /proc/mounts ; then 
	if [ ! "$1" == "-f" ] ; then
		echo "/mnt/data appears to be MOUNTED."
		echo "Won't cut the USB power without -f flag."
		echo "Remember: The data you corrupt may be your own..."
		exit 1
	else
		echo "/mnt/data is mounted.  Trying to umount."
		umount /mnt/data || umount -o ro,remount /mnt/data
	fi
fi

if [ ! -d $GPIO/gpio$PIN ] ; then
	echo $PIN > $GPIO/export
fi

if [ `cat $GPIO/gpio$PIN/direction` != "out" ] ; then
	echo out > $GPIO/gpio$PIN/direction
fi

if [ `cat $GPIO/gpio$PIN/value` == "1" ] ; then
	echo 0 > $GPIO/gpio$PIN/value
	sleep 3
fi

echo 1 > $GPIO/gpio$PIN/value

# Set over-heat shutdown on the PMC:
if [ `i2cget -y -f 0 0x34 0x8f` != "0x23" ] ; then
	i2cset -y -f 0 0x34 0x8f 0x23
fi