#!/bin/bash

PRINT=/etc/oled/printsmol.py

FIRST=1

SEQ=0
while true ; do
  # Not sure what the diff is between these, but bananatemp 
  # uses charger/charging & seems to be okay.
  #CHARGING=`head -n1 /sys/power/axp_pmu/battery/charging`
  CHARGING=`head -n1 /sys/power/axp_pmu/charger/charging`
  BAT_PCT=`head -n1 /sys/power/axp_pmu/battery/capacity`
  OVERHEAT=`head -n1 /sys/power/axp_pmu/pmu/overheat`
  AC_IN=`head -n1 /sys/power/axp_pmu/ac/connected`
  
  if [ $FIRST == 1 ] ; then
    PREV_AC_IN=$AC_IN
    PREV_CHARGING=$CHARGING
    FIRST=0  
  else
    if [ $AC_IN == "1" ] || [ $SEQ -gt 0 ]; then
      sleep 3
    else
      # If we're on battery, don't update the screen for 30 seconds,
      # BUT check to see if we're on A/C every three seconds.  Wake up
      # and update if we get switched so we'll update quickly on-plug.
      C=10
      while [ $C -gt 0 ] ; do
        sleep 3
        C=$(( $C - 1 ))
        
        NEW_AC_IN=`head -n1 /sys/power/axp_pmu/ac/connected`
        if [ $NEW_AC_IN == "1" ] || \
          [ $NEW_AC_IN != $PREV_AC_IN ] || \
          [ `head -n1 /sys/power/axp_pmu/charger/charging` != $CHARGING ] ; then
          AC_IN=$NEW_AC_IN
          CHARGING=`head -n1 /sys/power/axp_pmu/charger/charging`
          BAT_PCT=`head -n1 /sys/power/axp_pmu/battery/capacity`
          break
        fi
      done
    fi
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
    sleep 3
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
  
  if [ $SEQ -ge 8 ] ; then
    SEQ=0
  else
    SEQ=$(( $SEQ + 1 ))
  fi
  
  # Spinner so we know we're still alive...
  if [ $(( $SEQ % 2 )) == 0 ] ; then
    STAT0="${STAT0} ."
  else
    STAT0="${STAT0} "
  fi
  
  if [ $SEQ -ge 0 ] && [ $SEQ -le 3 ] ; then
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
  elif [ $SEQ -eq 4 ] ; then
    board_temp=$(awk '{printf("%d",$1/1000)}' </sys/devices/virtual/thermal/thermal_zone0/temp)
    pmu_temp=`awk '{printf("%d", $1 / 1000)}' < /sys/power/axp_pmu/pmu/temp`
    
    STAT1="PMU: $pmu_temp C"
    STAT2="CPU: $board_temp C"
  elif [ $SEQ -eq 5 ] ; then
    battery_voltage=$(awk '{printf("%.2f", $1 / 1000000)}' < /sys/power/axp_pmu/battery/voltage)
    battery_out_ma=$(awk '{printf("%.0f", $1 / 1000)}' < /sys/power/axp_pmu/battery/amperage)
    battery_in_ma=$(awk '{printf("%.0f", $1 / 1000)}' < /sys/power/axp_pmu/charger/amperage)
    
    if [ $CHARGING == 1 ] ; then
      ma="+$battery_in_ma"
    else
      ma="-$battery_out_ma"
    fi
    
    STAT1="Batt: $BAT_PCT %"
    STAT2="${battery_voltage}V ${ma}mA"
  elif [ $SEQ -eq 6 ] ; then
    ma=`awk '{printf("%.0f", $1 / 1000)}' < /sys/power/axp_pmu/ac/amperage`
    v=`awk '{printf("%.2f", $1 / 1000000)}' < /sys/power/axp_pmu/ac/voltage`
    STAT1="AC: ${v}V"
    STAT2="  @ ${ma}mA"
  elif [ $SEQ -eq 7 ] ; then
    root_usage=$(df -h / | awk '/\// {print $(NF-1)}' | sed 's/%//g')
    root_total=$(df -h / | awk '/\// {print $(NF-4)}')
  	STAT1="root: $root_usage %"
    STAT2=" of $root_total"
  elif [ $SEQ -eq 8 ] ; then
    storage=/mnt/data
  	storage_usage=$(df -h $storage | grep $storage | awk '/\// {print $(NF-1)}' | sed 's/%//g')
  	storage_total=$(df -h $storage | grep $storage | awk '/\// {print $(NF-4)}')
    STAT1="data: $storage_usage %"
    STAT2=" of $storage_total"
  fi
  
  $PRINT "$STAT0" "$STAT1" "$STAT2"
  sleep 1
  
  # Save the values that trigger flags for next time.
  PREV_AC_IN=$AC_IN
  PREV_CHARGING=$CHARGING
done
