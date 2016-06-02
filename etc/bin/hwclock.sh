#!/bin/bash

# All exit 0 on this since we don't want to die if we don't have the RTC plugged in.

/sbin/modprobe rtc-ds1307 || exit 0
echo ds1307 0x68 > /sys/class/i2c-adapter/i2c-1/new_device || exit 0
/sbin/hwclock --hctosys || exit 0
