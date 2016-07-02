#!/bin/bash

# All exit 0 on this since we don't want to die if we don't have the RTC plugged in.

rm -f /dev/rtc0
/sbin/modprobe rtc-ds1307 || true
echo ds1307 0x68 > /sys/class/i2c-adapter/i2c-1/new_device || true
if [ -c /dev/rtc1 ] ; then
	rm -f /dev/rtc
	ln -s /dev/rtc1 /dev/rtc
fi

dmesg | grep rtc

/sbin/hwclock --hctosys || true

/sbin/hwclock
/bin/date
