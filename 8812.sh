#!/bin/bash

set -e

cd /usr/src
git clone https://github.com/abperiasamy/rtl8812AU_8821AU_linux rtl8812AU_8821AU_linux-1.0

sed -i -e 's/CONFIG_POWER_SAVING = y/CONFIG_POWER_SAVING = n/' rtl8812AU_8821AU_linux-1.0/Makefile
sed -i -e 's/CONFIG_PLATFORM_I386_PC = y/CONFIG_PLATFORM_I386_PC = n/' rtl8812AU_8821AU_linux-1.0/Makefile
sed -i -e 's/CONFIG_PLATFORM_ARM_RPI = n/CONFIG_PLATFORM_ARM_RPI = y/' rtl8812AU_8821AU_linux-1.0/Makefile

dkms add -m rtl8812AU_8821AU_linux -v 1.0
dkms build -m rtl8812AU_8821AU_linux -v 1.0
dkms install -m rtl8812AU_8821AU_linux -v 1.0

if ! `grep 8812au /etc/modules` ; then
	echo "88128au" >> /etc/modules
fi

