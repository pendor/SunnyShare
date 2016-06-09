#!/bin/bash

PREV_AC_IN=0
PREV_CHARGING=0

PRINT=/etc/oled/printsmol.py
NOUPDATES=/tmp/no-screen-updates

while true ; do
  # Not sure what the diff is between these, but bananatemp 
  # uses charger/charging & seems to be okay.
  #CHARGING=`head -n1 /sys/power/axp_pmu/battery/charging`
  CHARGING=`head -n1 /sys/power/axp_pmu/charger/charging`
  AC_IN=`head -n1 /sys/power/axp_pmu/ac/connected` 
  
  OVERHEAT=`head -n1 /sys/power/axp_pmu/pmu/overheat`
  if [ $OVERHEAT == "1" ] ; then
    TEMP=$(( `head -n1 /sys/power/axp_pmu/pmu/temp` / 1000 ))
    
    touch $NOUPDATES
    $PRINT "WARNING" "Too Hot!!" "$TEMP C"
    sleep 5
    rm -f $NOUPDATES
  fi
  
  if [ $AC_IN != $PREV_AC_IN ] || [ $CHARGING != $PREV_CHARGING ] ; then
    # AC status changed
    if [ $AC_IN == "1" ] ; then
      STAT1="On AC Power"
      if [ $CHARGING == "1" ] ; then
        STAT2="Charging..."
      else
        STAT2="Charged"
      fi
    else
      BAT_PCT=`head -n1 /sys/power/axp_pmu/battery/capacity`
      if [ $BAT_PCT -gt 94 ] ; then
        BAT_PCT=100
      fi
      STAT1="On Battery"
      STAT2="  $BAT_PCT %"
    fi
    
    touch $NOUPDATES
    $PRINT "Power" "$STAT1" "$STAT2"
    sleep 3
    rm -f $NOUPDATES
  fi
  
  # Save the values that trigger flags for next time.
  PREV_AC_IN=$AC_IN
  PREV_CHARGING=$CHARGING
  
  sleep 3
done
