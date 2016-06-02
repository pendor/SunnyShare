#!/bin/bash

if [ -d /sys/power/axp_pmu ] ; then
	# Use bananatemp.sh instead.
	exit 0
fi

if [ ! -e /sys/bus/i2c/drivers/axp20x/0-0034 ] ; then
	echo "Hardware info not available on this platform."
	exit 0
fi

# Enable all voltage / current monitors if not already.
if [ `i2cget -y -f 0 0x34 0x82` != "0xff" ] ; then
	i2cset -y -f 0 0x34 0x82 0xff
fi

# From: http://linux-sunxi.org/AXP209
# Channel						msb, lsb	0x0=		Step/bit	0xfff=
# ACIN Voltage 					56h, 57h 	0 mV 		1.7 mV 		6.9615 V
# VBUS voltage 					5Ah, 5Bh 	0 mV 		1.7 mV 		6.9615 V
# APS (IPSOUT) Voltage 			7Eh, 7Fh 	0 mV 		1.4 mV 		5.733 V
# Battery Voltage 				78h, 79h 	0 mV 		1.1 mV 		4.5045 V

# ACIN Current 					58h, 59h 	0 mA 		0.625 mA 	2.5594 A
# VBUS Current 					5Ch, 5Dh 	0 mA 		0.375 mA 	1.5356 A
# Battery Discharge Current 	7Ah, 7Bh 	0 mA 		0.5 mA 		4.095 A
# Battery Charge Current 		7Ch, 7Dh 	0 mA 		0.5 mA 		4.095 A

# Internal Temperature 			5Eh, 5Fh 	-144.7 C 	0.1 C 		264.8 C
# Temperature Sensor Voltage 	62h, 63h 	0 mV 		0.8 mV 		3.276 V

function readVoltage() {
	local reglsb=$1
	local regmsb=$2
	local step=$3
	
	local lsb=`i2cget -y -f 0 0x34 $reglsb`
	local msb=`i2cget -y -f 0 0x34 $regmsb`
	local bin=$(( $(($msb << 4)) | $(($(($lsb & 0xF0)) >> 4)) ))
	
	echo " ($bin*$step) " | bc
}

function readCurrent() {
	local reglsb=$1
	local regmsb=$2
	local step=$3
	
	local lsb=`i2cget -y -f 0 0x34 $reglsb`
	local msb=`i2cget -y -f 0 0x34 $regmsb`
	local bin=$(( $(($msb << 4)) | $(($(($lsb & 0xF0)) >> 4)) ))
	
	echo " ($bin*$step) " | bc
}

function getBit() {
	local val=$1
	local bit=$2
	
	local bval=$((1 << $bit))
	echo $(( ($val & $bval) / $bval ))
}

# From: https://bbs.nextthing.co/t/how-to-check-the-temperature-of-chip/1738/10
lsb=$(i2cget -y -f 0 0x34 0x5f)
msb=$(i2cget -y -f 0 0x34 0x5e)
bin=$(( $(($msb << 4)) | $(($lsb & 0x0F))))
cel=`echo $bin | awk '{printf("%.2f", ($1/10) - 144.7)}'`
fah=`echo $cel | awk '{printf("%.2f", ($1 * 1.8) + 32)}'`

# Based on:
# https://gist.github.com/Jooshboy/bc1e5a2c2b58f6c9fab4
# http://learn.linksprite.com/pcduino/pcduino-hardware-basic-and-improve/axp209-battery-info-with-a-script/


# REG 00H: Power input status
# 7 	Indicates ACIN presence :: 0: ACIN does not exist; 1: ACIN present
# 6 	Instructions the ACIN whether available
# 5 	VBUS is present indication :: 0: VBUS does not exist; 1: VBUS exist
# 4 	Indicate the VBUS whether available
# 3 	The directions VBUS access before use is greater than VHOLD
# 2 	Indicates that the battery current direction :: 0: battery discharge; 1: The battery is charged
# 1 	Indicate whether ACIN and VBUS input is shorted on the PCB
# 0 	The instructions start ACIN source is or VBUS ::
#				0: Start source non-ACIN / VBUS is;: Start source ACIN / VBUS 

POWER_STATUS=`i2cget -y -f 0 0x34 0x00`

ACIN_PRESENT=`getBit $POWER_STATUS 7`
#ACIN_AVAIL=`getBit $POWER_STATUS 6`
USB_PRESENT=`getBit $POWER_STATUS 5`
#USB_AVAIL=`getBit $POWER_STATUS 4`
BAT_STATUS=`getBit $POWER_STATUS 2`


# REG 01H: Power operating mode and charge status indication
# 7 	Indicating AXP209 whether over-temperature :: 0: not too warm; 1: overtemperature
# 6 	Charging indicator :: 0: not charging or charging has been completed; 1: Charging
# 5 	The battery state of existence indicates :: 
#			0: no battery connected to AXP209; 
#			1: the battery has been connected to the AXP209
# 3 	Indicates whether the battery into the active mode ::
#			0: not to enter the the battery activation patterns; 
#			1: has entered the battery activation mode
# 2 	Indicate the charging current is less than the desired current
# 			0: The actual charge current is equal to the desired current; 
#			1: the actual charge current is less than the desired current

################################
#read Power OPERATING MODE register @01h
POWER_OP_MODE=`i2cget -y -f 0 0x34 0x01`
OVERTEMP=`getBit $POWER_OP_MODE 7`
CHARGING=`getBit $POWER_OP_MODE 6`
BATTPRES=`getBit $POWER_OP_MODE 5`
MOREPOWER=`getBit $POWER_OP_MODE 2`

ACIN_VOLT=`readVoltage 0x57 0x56 1.7`
USB_VOLT=`readVoltage 0x5b 0x5a 1.7`
RUN_VOLT=`readVoltage 0x7f 0x7e 1.4`
BATT_VOLT=`readVoltage 0x78 0x78 1.1`

ACIN_CUR=`readCurrent 0x59 0x58 0.625`
USB_CUR=`readCurrent 0x5d 0x5c 0.375`

if [ $OVERTEMP == 1 ] ; then
	TEMP="Too Hot"
else
	TEMP="Ok"
fi

if [ $BAT_STATUS == 0 ] ; then
	BATDIR="Discharging"
	BATCUR=`readCurrent 0x7b 0x7a 0.5`
else
	BATDIR="Charging"
	BATCUR=`readCurrent 0x7d 0x7c 0.5`
fi

echo "Ext Power : Status: $ACIN_PRESENT :: $ACIN_VOLT mv @ $ACIN_CUR mA"
echo "USB Power : Status: $USB_PRESENT :: $USB_VOLT mv @ $USB_CUR mA"
echo "Battery   : $BATDIR @ $BATCUR mA"
echo "PMU Temp  : $cel°C / $fah°F ($TEMP)"
