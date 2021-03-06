#!/bin/bash

if [ -d /sys/power/axp_pmu ] || [ -e /sys/bus/i2c/drivers/axp20x/0-0034 ] ; then
  # Enable all voltage / current monitors if not already.
 	i2cset -y -f 0 0x34 0x82 0xff
  
  # Set longer delays on power button
 	i2cset -y -f 0 0x34 0x36 0xff
    
  # Make sure the PEK registers start out at 0
  i2cset -y -f 0 0x34 0x4a 0x03
    
  # 1200mA max charge current
  # i2cset -y -f 0 0x34 0x33 0xc9
   
  # 1800mA max charge current
  i2cset -y -f 0 0x34 0x33 0xcf
  
  # Might keep us from energizing VUSB when shutdown?
  i2cset -f -y 0 0x34 0x8b 0x9
  
  # Enable overheat shutdown for AXP209
  i2cset -f -y -m 0x4 0 0x34 0x8f 0x4
fi

# No blinky light...
echo "none" > /sys/class/leds/bananapro\:green\:usr/trigger
