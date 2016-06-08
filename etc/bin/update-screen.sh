#!/bin/bash

PRINT=/etc/oled/printsmol.py

FIRST=1

SEQ=0
while true ; do
  OVERHEAT=`head -n1 /sys/power/axp_pmu/pmu/overheat`
  AC_IN=`head -n1 /sys/power/axp_pmu/ac/connected`
  CHARGING=`head -n1 /sys/power/axp_pmu/battery/charging`
  BAT_PCT=`head -n1 /sys/power/axp_pmu/battery/capacity`
  
  if [ $FIRST == 1 ] ; then
    PREV_AC_IN=$AC_IN
    PREV_CHARGING=$CHARGING
    FIRST=0  
  else
    if [ $AC_IN == "1" ] ; then
      sleep 1
    else
      sleep 30
    fi
  fi
  
  if [ $SEQ == 2 ] ; then
    SEQ=0
  else
    SEQ=$(( $SEQ + 1 ))
  fi
  
  if [ -f /tmp/no-screen-updates ] ; then
    continue
  fi
  
  
  if [ $BAT_PCT -gt 94 ] ; then
    BAT_PCT=100
  fi
  
  if [ $OVERHEAT == "1" ] ; then
    TEMP=$(( `head -n1 /sys/power/axp_pmu/pmu/temp` / 1000 ))
    $PRINT "WARNING" "Too Hot!!" "$TEMP C"
    sleep 5
  fi
  
  if [ $AC_IN != $PREV_AC_IN ] || [ $CHARGING != $PREV_CHARGING ]; then
    # AC status changed
    if [ $AC_IN == "1" ] ; then
      STAT1="On AC Power"
      if [ $CHARGING == "1" ] ; then
        STAT2="Charging..."
      else
        STAT2="Charged"
      fi
    else
      STAT1="On Battery"
      STAT2="$BAT_PCT % Battery"
    fi
    $PRINT "Power" "$STAT1" "$STAT2"
    sleep 5
  fi
  
  # If nothing flagged, just update default status info
  # Should get about 18 chars on line 1, 12 on lines 2 & 3
  
  if [ $AC_IN == "1" ] ; then
    if [ $CHARGING == "1" ] ; then
      CH="-CH"
    else
      CH="   "
    fi
    STAT0="AC${CH} ["
  else
    STAT0="Batt ["
  fi
  
  BARS=$(( $BAT_PCT / 10))
  SPACE=$(( 10 - $BARS ))
  while [ $BARS -gt 0 ] ; do
    STAT0="${STAT0}-"
    BARS=$(( $BARS - 1))
  done
  
  while [ $SPACE -gt 0 ] ; do
    STAT0="${STAT0} "
    SPACE=$(( $SPACE - 1))
  done
  
  STAT0="$STAT0]"
  
  # Spinner so we know we're still alive...
  if [ $SEQ == 0 ] ; then
    STAT0="${STAT0} ."
  else
    STAT0="${STAT0} "
  fi
  
  IPW=`ifconfig wlan1 | grep 'inet addr' | awk '{print $2}' | cut -f2 -d:`
  IPE=`ifconfig eth0 | grep 'inet addr' | awk '{print $2}' | cut -f2 -d:`
  
  if [ -z $IPW ] ; then
    STAT1="(None):w"
  else
    STAT1="$IPW:w"
  fi
  
  if [ -z $IPE ] ; then
    if [ -z $IPW ] ; then
      STAT2="(None):e"
    else
      STAT2=`iwconfig wlan1 | perl -nle '/ESSID:\"([^\"]+)\"/ &&  print $1;'`
    fi
  else
    STAT2="$IPE:e"
  fi
  
  $PRINT "$STAT0" "$STAT1" "$STAT2"
  
  # Save the values that trigger flags for next time.
  PREV_AC_IN=$AC_IN
  PREV_CHARGING=$CHARGING
done
