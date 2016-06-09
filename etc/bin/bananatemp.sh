#!/bin/bash

axp_dir=/sys/power/axp_pmu
storage=/dev/sda1

# Settings based on https://github.com/zador-blood-stained/axp209-sysfs-interface

if [ ! -d $axp_dir ] ; then
	# Use chiptemp.sh instead.
	exit 0
fi

function getBit() {
	local val=$1
	local bit=$2
	
	local bval=$((1 << $bit))
	echo $(( ($val & $bval) / $bval ))
}

CCREG=`i2cget -y -f 0 0x34 0x33`

# Charge enabled
CCB7=`getBit $CCREG 7`

# Voltage
CCB6=`getBit $CCREG 6`
CCB5=`getBit $CCREG 5`

# 0 = 10%, 1 = 15% tolerance on charge cutoff
CCB4=`getBit $CCREG 4`

# Charge current
CCB3=`getBit $CCREG 3`
CCB2=`getBit $CCREG 2`
CCB1=`getBit $CCREG 1`
CCB0=`getBit $CCREG 0`

CCAMPS=$(( 300 + (((CCB3 << 3) + (CCB2 << 2) + (CCB1 << 1) + CCB0) * 100) ))


if [ $CCB6 == 1 ] ; then
  if [ $CCB5 == 1 ] ; then
    CCV=4.36
  else
    CCV=4.2
  fi
else
  if [ $CCB5 == 1 ] ; then
    CCV=4.15
  else
    CCV=4.1
  fi
fi

if [ $CCB7 == 1 ] ; then
  CCEN="Charge enabled"
else
  CCEN="Charge disabled"
fi

if [ $CCB4 == 0 ] ; then
  CCTOL="±10%"
else
  CCTOL="±15%"
fi

status_battery_connected=$(cat $axp_dir/battery/connected)
if [[ "$status_battery_connected" == "1" ]]; then
  status_battery_charging=$(cat $axp_dir/charger/charging)
  status_ac_connect=$(cat $axp_dir/ac/connected)
  battery_percent=$(cat $axp_dir/battery/capacity)
  battery_voltage=$(awk '{printf("%.2f", $1 / 1000000)}' < /sys/power/axp_pmu/battery/voltage)
  battery_out_ma=$(awk '{printf("%.2f", $1 / 1000)}' < /sys/power/axp_pmu/battery/amperage)
  battery_in_ma=$(awk '{printf("%.2f", $1 / 1000)}' < /sys/power/axp_pmu/charger/amperage)
        
  if [ `cat /sys/power/axp_pmu/charger/low_power` == 1 ] ; then
    batt_under=" -- WARNING: Insufficient charging current available"
  else
    batt_under=""
  fi
        
  # dispay charging / percentage
	if [[ "$status_ac_connect" == "1" ]]  && [ "1" == $status_battery_charging ] ; then
		status_battery_text="Charging ${battery_percent}% :: $battery_voltage V @ $battery_in_ma mA $batt_under"
	elif [[ "$status_ac_connect" == "1" ]] && [ "0" == $status_battery_charging ] ; then
		status_battery_text="Charged     :: $battery_voltage V @ $battery_out_ma mA $batt_under"
	else
		status_battery_text="Discharging ${battery_percent}% :: $battery_voltage V @ $battery_out_ma mA $batt_under"
	fi
else
  status_battery_text="DISCONNECTED"
fi

load=$(cat /proc/loadavg | awk '{print $1}')
memory_usage=$(free | awk '/Mem/ {printf("%.0f",(($2-($4+$6+$7))/$2) * 100)}') 
memory_total=$(free -m |  awk '/Mem/ {print $(2)}') 
users=$(users | wc -w)
swap_usage=$(free -m | awk '/Swap/ { printf("%3.0f", $3/$2*100) }' | sed 's/ //g')
swap_usage=${swap_usage//[!0-9]/} # to remove alfanumeric if swap not used
swap_total=$(free -m |  awk '/Swap/ {print $(2)}')
root_usage=$(df -h / | awk '/\// {print $(NF-1)}' | sed 's/%//g')
root_total=$(df -h / | awk '/\// {print $(NF-4)}')
board_temp=$(awk '{printf("%d",$1/1000)}' </sys/devices/virtual/thermal/thermal_zone0/temp)

if [ -e "$storage" ]; then
	storage_usage=$(df -h $storage | grep $storage | awk '/\// {print $(NF-1)}' | sed 's/%//g')
	storage_total=$(df -h $storage | grep $storage | awk '/\// {print $(NF-4)}')
	[[ "$storage" == */sd* ]] && hdd_temp=$(hddtemp -u C -nq $storage)
fi


echo "System load  : $load"

echo " "
echo "Memory usage : $memory_usage % of $memory_total Mb"
echo "Swap usage   : $swap_usage % of $swap_total Mb"
echo "Usage of /   : $root_usage % of $root_total"
echo "        data : $storage_usage % of $storage_total"

echo " "
echo "CPU temp     : $board_temp °C"
echo "HDD temp     : $hdd_temp °C"
echo "PMU temp     :" `awk '{printf("%d", $1 / 1000)}' < /sys/power/axp_pmu/pmu/temp` "°C"

echo "-----------------------------------------"
echo "5v rail  : " `awk '{printf("%.2f", $1 / 1000000)}' < /sys/power/axp_pmu/pmu/voltage` "V"
if [ "1" == `cat $axp_dir/ac/connected` ] ; then
	echo "AC Power : Available   ::" `awk '{printf("%.2f", $1 / 1000000)}' < /sys/power/axp_pmu/ac/voltage` "V @" `awk '{printf("%.2f", $1 / 1000)}' < /sys/power/axp_pmu/ac/amperage` "mA"
else
	echo "AC Power : Not available" 
fi

if [ "1" == `cat $axp_dir/battery/connected` ] ; then
	echo "Battery  : $status_battery_text"
  echo "  Charge : $CCV V $CCTOL @ $CCAMPS mA ($CCEN)"
else
	echo "Battery  : Not available" 
fi






