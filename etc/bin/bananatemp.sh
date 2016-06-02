#!/bin/bash

axp_dir=/sys/power/axp_pmu
storage=/dev/sda1

# Settings based on https://github.com/zador-blood-stained/axp209-sysfs-interface

if [ ! -d $axp_dir ] ; then
	# Use chiptemp.sh instead.
	exit 0
fi

status_battery_connected=$(cat $axp_dir/battery/connected)
if [[ "$status_battery_connected" == "1" ]]; then
        status_battery_charging=$(cat $axp_dir/charger/charging)
        status_ac_connect=$(cat $axp_dir/ac/connected)
        battery_percent=$(cat $axp_dir/battery/capacity)
        # dispay charging / percentage
	if [[ "$status_ac_connect" == "1" ]]  && [ "1" == $status_battery_charging ] ; then
		status_battery_text="Charging ${battery_percent}%"
	elif [[ "$status_ac_connect" == "1" ]] && [ "0" == $status_battery_charging ] ; then
		status_battery_text="Charged"
	else
		status_battery_text="Discharging ${battery_percent}%"
	fi
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
	echo "AC Power : Available ::" `awk '{printf("%.2f", $1 / 1000000)}' < /sys/power/axp_pmu/ac/voltage` " V @" `awk '{printf("%.2f", $1 / 1000)}' < /sys/power/axp_pmu/ac/amperage` "mA"
else
	echo "AC Power : Not available" 
fi

if [ "1" == `cat $axp_dir/battery/connected` ] ; then
	echo "Battery  : $status_battery_text ::" `awk '{printf("%.2f", $1 / 1000000)}' < /sys/power/axp_pmu/battery/voltage` " V @" `awk '{printf("%.2f", $1 / 1000)}' < /sys/power/axp_pmu/battery/amperage` "mA"
else
	echo "Battery  : Not available" 
fi






