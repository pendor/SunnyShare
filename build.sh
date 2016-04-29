#!/bin/bash

# SSH alias or user@ip for box:
BOX=sunny

# Router type
PROFILE=TLWR842

# Dir within the VM for ImageBuilder.  Changes based on trunk/release version.
BUILD_DIR='~/OpenWrt-ImageBuilder-ar71xx-generic.Linux-x86_64'
# BUILD_DIR='~/OpenWrt-ImageBuilder-15.05.1-ar71xx-generic.Linux-x86_64'

# Output dir inside the VM
FW_DIR="${BUILD_DIR}/bin"

# Name of the firmware we check for output.  Changes based on profile.  Wildcard okay.
OUT='openwrt-*-wr842n-v2-squashfs-factory.bin'

# Where we'll drop the finished firmware on the host system
LOCAL_FW=./firmware


INSTALL="false"
if [ "$1" == "--install" ] ; then
  INSTALL="true"
  shift
fi

PKG_FILE="$1"

if [ -z $PKG_FILE ] ; then
  PKG_FILE=pb.pkgs
  echo "Using default package file: $PKG_FILE.  Pass another on the command line to override."
fi

rm -rf $LOCAL_FW
mkdir -p $LOCAL_FW

vagrant up

PKGS=`cat $PKG_FILE | grep -v '#' | tr '\r\n' ' '`

find copy-to -name .DS_Store -delete
vagrant ssh -c "\
  cd ${BUILD_DIR} && \
  rm -rf files && \
  cp -r /openwrt_files/* . && \
  ( patch -p0 -N --dry-run -i Makefile.patch && patch -p0 -N -i  Makefile.patch ) ;  \
  make image PROFILE=${PROFILE} PACKAGES=\"${PKGS}\" FILES=files && \
  cp -r bin/* /openwrt_firmware \
  "

if [ -f $LOCAL_FW/ar71xx/$OUT ] ; then
  echo " "
  echo " "
  echo "Found $LOCAL_FW/ar71xx/$OUT.  Looks like build is good."
  echo " "
  echo " "
  if [ "$INSTALL" == "true" ] ; then
    echo "Sending firmware to the condemned..."
    scp $LOCAL_FW/ar71xx/$OUT $BOX:/tmp/owrt-pbox.bin

    echo "Bricking router now..."
    ssh $BOX 'sysupgrade -n -v /tmp/owrt-pbox.bin'

    echo "You now have a brick.  Perhaps it's rebooting?"
  fi
else
  echo "No firmware found.  Build failed?"
fi
