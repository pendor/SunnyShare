#!/bin/bash

set -e

cd ~

if [ ! -d CHIP-linux ] ; then
  git clone https://github.com/NextThingCo/CHIP-linux.git
  cd CHIP-linux
  git checkout -b debian/4.3.0-ntc-4 origin/debian/4.3.0-ntc-4
else
  cd CHIP-linux
  git pull
fi

if [ ! -f .kernel-suffix ] ; then
  echo "No .kernel-suffix found.  Creating a random one."
  SUFFIX=`cat /dev/urandom | tr -dc 'a-z0-9' | fold -w 6 | head -n 1`
  echo $SUFFIX > .kernel-suffix
else
  SUFFIX=`head -n1 < .kernel-suffix`
  echo "Found existing build version suffix: $SUFFIX"
fi
  

if [ ! -f .config ] ; then
  echo "Copying initial kernel config"
  cp /vagrant_data/config .config
  sed -i -e "s/REPLACE_KVER_SUFFIX/${SUFFIX}/" .config
else
  echo "Keeping existing kernel .config"
fi

rm -rf ~/install
mkdir -p ~/install/lib ~/install/boot

make ARCH=arm CROSS_COMPILE=/usr/bin/arm-linux-gnueabihf- menuconfig

make ARCH=arm CROSS_COMPILE=/usr/bin/arm-linux-gnueabihf-

make ARCH=arm CROSS_COMPILE=/usr/bin/arm-linux-gnueabihf- INSTALL_MOD_PATH=~/install modules_install


cp arch/arm/boot/zImage ~/install/boot/vmlinuz-4.3.0-$SUFFIX
cp .config ~/install/boot/config-4.3.0-$SUFFIX
cp System.map ~/install/boot/System.map-4.3.0-$SUFFIX

echo " "
echo " "
echo "Building module for RTL8723BS..."
echo " "
echo " "

cd ~
if [ ! -d RTL8723BS ] ; then
  git clone https://github.com/NextThingCo/RTL8723BS.git
  cd RTL8723BS
  git checkout -b debian origin/debian
  for i in debian/patches/0*; do  echo $i; patch -p 1 <$i ; done
else
  cd RTL8723BS
  echo "RTL8723BS already exists.  Not 'git pull'ing since we've patched it."
fi

make CONFIG_PLATFORM_ARM_SUNxI=y ARCH=arm CROSS_COMPILE=/usr/bin/arm-linux-gnueabihf- \
  -C ~/CHIP-linux/ M=$PWD CONFIG_RTL8723BS=m  INSTALL_MOD_PATH=~/install/lib
make CONFIG_PLATFORM_ARM_SUNxI=y ARCH=arm CROSS_COMPILE=/usr/bin/arm-linux-gnueabihf- \
  -C ~/CHIP-linux/ M=$PWD CONFIG_RTL8723BS=m  INSTALL_MOD_PATH=~/install/lib modules_install


echo " "
echo " "
echo "Building module for rtl8812au..."
echo " "
echo " "

cd ~
if [ ! -d rtl8812au ] ; then
  git clone https://github.com/gnab/rtl8812au.git
  cd rtl8812au
else
  cd rtl8812au
  git pull
fi

sed -i -e 's/CONFIG_PLATFORM_I386_PC = y/CONFIG_PLATFORM_I386_PC = n/' Makefile
sed -i -e 's/CONFIG_PLATFORM_ARM_SUNxI = n/CONFIG_PLATFORM_ARM_SUNxI = y/' Makefile

make ARCH=arm CROSS_COMPILE=/usr/bin/arm-linux-gnueabihf- -C ../CHIP-linux/ INSTALL_MOD_PATH=~/install/lib
make ARCH=arm CROSS_COMPILE=/usr/bin/arm-linux-gnueabihf- -C ../CHIP-linux/ INSTALL_MOD_PATH=~/install/lib modules_install


echo " "
echo " "
echo "Creating tarball..."
echo " "
echo " "
cd ~
tar -cjvf kernel-4.3.0$SUFFIX.tbz -C ~/install .

