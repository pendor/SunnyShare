#!/bin/bash

logger "Shutdown requested at `date`.  Waiting for timeout"

#echo "timer" > /sys/class/leds/bananapro\:green\:usr/trigger 
#echo "100" > /sys/class/leds/bananapro\:green\:usr/delay_on
#echo "100" > /sys/class/leds/bananapro\:green\:usr/delay_off

LOOP=2
while [ $LOOP -gt 0 ] ; do
  python /etc/oled/print.py "Shutdown" "Hold Btn" $LOOP
  LOOP=$((LOOP - 1))
  REG=`i2cget -y -f 0 0x34 0x4a`
  if [ $REG == "0x03" ] || [ $REG == "0x01" ]; then
    logger "Long-press on power.  Shutting down..."
    python /etc/oled/print.py "Shutdown" "In progress..." " "
    sleep 2
    python /etc/oled/oledoff.py
    shutdown -h now
    exit 0
  fi
  sleep 1
done

# Reset the IRQ registers
i2cset -y -f 0 0x34 0x4a 0x03

logger "Didn't get long-press.  Not shutting down."
python /etc/oled/print.py "Shutdown" "Canceled" " "
#echo "none" > /sys/class/leds/bananapro\:green\:usr/trigger 
sleep 3
python /etc/oled/oledoff.py
