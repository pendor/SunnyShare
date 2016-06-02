#!/bin/bash

set -e

if [ -z $1 ] ; then
  echo "Usage: $0 <ssh_host>"
  exit 1
fi

if [ "z$UID" != "z0" ] ; then
	echo "Copying script to box..."
	scp $0 root@$1:/tmp/prereq.sh
	ssh root@$1 'chmod +x /tmp/prereq.sh'
	
	echo "Running package install..."
	ssh root@$1 '/tmp/prereq.sh install'
	
	echo "Running Deploy..."
	./deploy.sh $1
	
	echo "Enabling services..."
	ssh root@$1 '/tmp/prereq.sh services'
	
	echo "Rebooting..."
	ssh root@$1 '/tmp/prereq.sh reboot'
	
	exit 0
fi

echo "Script running on box :: Phase $1 ..."

# Ubuntu:
# PKGS="joe lighttpd php-cli php-cgi ifplugd dkms git"

# Raspbian:
# PKGS="joe lighttpd firmware-realtek dkms git raspberrypi-kernel-headers php-file php-file-iterator php-http-upload php5 php5-cgi php5-cli php5-curl php5-fpm php5-json php5-memcached lighttpd hostapd forked-daapd dnsmasq minidlna wpasupplicant memcached php-compat php5-mcrypt i2c-tools"

# CHIP:
# PKGS="joe lighttpd firmware-realtek dkms git php-file php-file-iterator php5 php5-cgi php5-cli php5-curl php5-fpm php5-json php5-memcached lighttpd hostapd forked-daapd dnsmasq minidlna wpasupplicant memcached php5-mcrypt i2c-tools"


# Armbian Banana-Pi
PKGS="joe ntpdate watchdog lighttpd dkms git php-file php-file-iterator php5 php5-cgi php5-cli php5-curl php5-fpm php5-json php5-memcached lighttpd hostapd forked-daapd dnsmasq minidlna wpasupplicant memcached php5-mcrypt i2c-tools"

if [ "z$1" == "zinstall" ] ; then
	echo "Installing packages..."
	
	ln -sf /usr/share/zoneinfo/America/New_York /etc/localtime 
	
	apt-get update
	
	DEBIAN_FRONTEND=noninteractive apt-get --force-yes -y \
		-o Dpkg::Options::="--force-confdef" \
		-o Dpkg::Options::="--force-confold" \
			dist-upgrade
	
	DEBIAN_FRONTEND=noninteractive apt-get --force-yes -y \
		-o Dpkg::Options::="--force-confdef" \
		-o Dpkg::Options::="--force-confold" \
		install $PKGS

	DEBIAN_FRONTEND=noninteractive apt-get --force-yes -y \
		-o Dpkg::Options::="--force-confdef" \
		-o Dpkg::Options::="--force-confold" \
			remove fake-hwclock
	
	DEBIAN_FRONTEND=noninteractive apt-get --force-yes -y \
		-o Dpkg::Options::="--force-confdef" \
		-o Dpkg::Options::="--force-confold" \
			purge fake-hwclock
	
	
	DEBIAN_FRONTEND=noninteractive apt-get --force-yes -y \
		-o Dpkg::Options::="--force-confdef" \
		-o Dpkg::Options::="--force-confold" \
			autoremove

	echo "Cleaning up permissions..."
	chsh -s /bin/bash www-data
	WWWHOME=`grep www-data /etc/passwd | cut -d: -f6`
	mkdir -p $WWWHOME/.ssh
	cp /root/.ssh/authorized_keys $WWWHOME/.ssh
	chown -R www-data $WWWHOME
	chmod 700 $WWWHOME/.ssh
	chmod 600 $WWWHOME/.ssh/authorized_keys
	
	echo "Building 8812au module..."
	
	cd /usr/src
	if [ ! -d rtl8812AU_8821AU_linux-1.0 ] ; then
		git clone https://github.com/abperiasamy/rtl8812AU_8821AU_linux rtl8812AU_8821AU_linux-1.0
	else
		cd rtl8812AU_8821AU_linux-1.0
		git pull
		cd ..
	fi

	sed -i -e 's/CONFIG_POWER_SAVING = y/CONFIG_POWER_SAVING = n/' rtl8812AU_8821AU_linux-1.0/Makefile
	sed -i -e 's/CONFIG_PLATFORM_I386_PC = y/CONFIG_PLATFORM_I386_PC = n/' rtl8812AU_8821AU_linux-1.0/Makefile
	sed -i -e 's/CONFIG_PLATFORM_ARM_RPI = n/CONFIG_PLATFORM_ARM_RPI = y/' rtl8812AU_8821AU_linux-1.0/Makefile

	if ! `dkms status | grep -q rtl8812AU_8821AU_linux` ; then
		dkms add -m rtl8812AU_8821AU_linux -v 1.0
	fi
	dkms build -m rtl8812AU_8821AU_linux -v 1.0
	dkms install -m rtl8812AU_8821AU_linux -v 1.0

	if ! `grep 8812au /etc/modules` ; then
		echo "88128au" >> /etc/modules
	fi
	
elif [ "z$1" == "zservices" ] ; then
	echo "Activating services..."
	SVCS="minidlna lighttpd dnsmasq hostapd memcached forked-daapd php5-fpm setup-data"
	for f in $SVSC ; do
		echo "Enabling ${f}..."
		systemctl enable $f
	done
	
	OLD_SVCS="watchdog"
	for f in $OLD_SVCS ; do
		echo "Enabling ${f}..."
		update-rc.d $f defaults
	done
	
elif [ "z$1" == "zreboot" ] ; then
	echo "Rebooting box..."
	rm -f $0
	reboot
else
	echo "Unknown phase."
	exit 1
fi

