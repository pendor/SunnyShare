#!/bin/bash

if [ -d /sys/power/axp_pmu ] || [ -e /sys/bus/i2c/drivers/axp20x/0-0034 ] ; then

  # Enable all voltage / current monitors if not already.
  if [ `i2cget -y -f 0 0x34 0x82` != "0xff" ] ; then
  	i2cset -y -f 0 0x34 0x82 0xff
  fi
  
  # Set longer delays on power button
  if [ `i2cget -y -f 0 0x34 0x36` != "0xff" ] ; then
  	i2cset -y -f 0 0x34 0x36 0xff
    
    # Make sure the PEK registers start out at 0
    i2cset -y -f 0 0x34 0x4a 0x03
    
    # 1200mA max charge current
    # i2cset -y -f 0 0x34 0x33 0xc9
    
    # 1800mA max charge current
    i2cset -y -f 0 0x34 0x33 0xcf
  fi
  
fi

echo "none" > /sys/class/leds/bananapro\:green\:usr/trigger
