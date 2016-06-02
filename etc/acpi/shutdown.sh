#!/bin/bash

logger "Shutdown requested at `date`.  Waiting for timeout"

LOOP=5
while [ $LOOP -gt 0 ] ; do
  LOOP=$((LOOP - 1))
  REG=`i2cget -y -f 0 0x34 0x4a`
  if [ $REG == "0x03" ] || [ $REG == "0x01" ]; then
    logger "Long-press on power.  Shutting down..."
    shutdown -h now
    exit 0
  fi
  sleep 1
done

# Reset the IRQ registers
i2cset -y -f 0 0x34 0x4a 0x03

logger "Didn't get long-press.  Not shutting down."
