#!/bin/bash

function getBit() {
	local val=$1
	local bit=$2
	
	local bval=$((1 << $bit))
	echo $(( ($val & $bval) / $bval ))
}

logger "Shutdown requested at `date`.  Waiting for timeout"
touch /tmp/no-screen-updates
python /etc/oled/print.py "Shutdown" "Hold Button" "To Power Off"

LOOP=3
while [ $LOOP -gt 0 ] ; do  
  LOOP=$((LOOP - 1))
  REG=`i2cget -y -f 0 0x34 0x4a`
  if [ 1 == `getBit $REG 0` ] ; then
    logger "Long-press on power.  Shutting down..."
    python /etc/oled/print.py "Shutdown" "In progress..." " "
    sleep 2
    /etc/oled/oledoff.sh
    rm /tmp/no-screen-updates
    shutdown -h now
    exit 0
  fi
  sleep 1
done

# Reset the IRQ registers
i2cset -y -f 0 0x34 0x4a 0x03

logger "Didn't get long-press.  Not shutting down."
python /etc/oled/print.py "Shutdown" "Canceled" " "
sleep 2
rm -f /tmp/no-screen-updates

( systemctl restart drop-local-wifi.service & )
